<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケートデータ登録コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Components_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Questionnaire_Components_Action()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * アンケートデータを登録する
	 *
	 * @return	boolean	true or false
	 * @access	public
	 */
	function setQuestionnaire()
	{
		$questionnaireType = intval($this->_request->getParameter("questionnaire_type"));
		if ($questionnaireType == QUESTIONNAIRE_TYPE_SEQUENCE_VALUE &&
				$this->_request->getParameter("questionnaire_type_random") == _ON) {
			$questionnaireType = QUESTIONNAIRE_TYPE_RANDOM_VALUE;
		}
		$this->_request->setParameter("questionnaire_type", $questionnaireType);

		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$page = $session->getParameter('_main_room_page');
		if ($session->getParameter("_space_type") != _SPACE_TYPE_PUBLIC
			&& ($page['private_flag'] == _OFF
				|| $page['default_entry_flag'] == _OFF)) {
			$this->_request->setParameter("nonmember_flag", _OFF);
		} else if ($this->_request->getParameter("nonmember_flag") == _ON){
			$this->_request->setParameter("repeat_flag", _ON);
		}

		if (!function_exists("gd_info")
			|| ($session->getParameter("_space_type") != _SPACE_TYPE_PUBLIC
				&& ($page['private_flag'] == _OFF
				|| $page['default_entry_flag'] == _OFF))) {
			$this->_request->setParameter("image_authentication", _OFF);
		}

       	if ($this->_request->getParameter("anonymity_flag") == _ON){
       		$this->_request->setParameter("mail_send", _OFF);
       	}

		$params = array(
			"image_authentication" => intval($this->_request->getParameter("image_authentication")),
			'keypass_use_flag' => intval($this->_request->getParameter('keypass_use_flag')),
			'keypass_phrase' => $this->_request->getParameter('keypass_phrase'),
			"total_flag" => intval($this->_request->getParameter("total_flag")),
			"answer_show_flag" => intval($this->_request->getParameter("answer_show_flag")),
			"mail_send" => intval($this->_request->getParameter("mail_send")),
			"mail_subject" => $this->_request->getParameter("mail_subject"),
			"mail_body" => $this->_request->getParameter("mail_body")
		);

        $questionnaire = $this->_request->getParameter("questionnaire");
        if (empty($questionnaire) ||
        		$questionnaire["status"] == QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
			$params["questionnaire_name"] = $this->_request->getParameter("questionnaire_name");
			$params["icon_name"] = $this->_request->getParameter("icon_name");
        	$params["questionnaire_type"] = $this->_request->getParameter("questionnaire_type");
			$params["nonmember_flag"] = intval($this->_request->getParameter("nonmember_flag"));
			$params["anonymity_flag"] = intval($this->_request->getParameter("anonymity_flag"));
			$params["repeat_flag"] = intval($this->_request->getParameter("repeat_flag"));
        }

        if ($questionnaire["status"] != QUESTIONNAIRE_STATUS_END_VALUE) {
			$params["period"] = $this->_request->getParameter("period");
		}

        $questionnaireID = $this->_request->getParameter("questionnaire_id");
		if (empty($questionnaireID)) {
			$params["room_id"] = $this->_request->getParameter("room_id");
			$params["status"] = QUESTIONNAIRE_STATUS_INACTIVE_VALUE;
			$result = $this->_db->insertExecute("questionnaire", $params, true, "questionnaire_id");
		} else {
			$params["questionnaire_id"] = $questionnaireID;
			$result = $this->_db->updateExecute("questionnaire", $params, "questionnaire_id", true);
		}
		if (!$result) {
			return false;
		}

        if (!empty($questionnaireID)) {
        	return true;
        }

		$questionnaireID = $result;
		$this->_request->setParameter("questionnaire_id", $questionnaireID);
		if ($this->_request->getParameter("old_use") != _ON) {
			return true;
		}

		$params = array($this->_request->getParameter("old_questionnaire_id"));
		$sql = "SELECT question_id, question_sequence, question_value, question_type, ".
						"require_flag, description ".
				"FROM {questionnaire_question} ".
				"WHERE questionnaire_id = ? ".
				"ORDER BY question_id";
		$questions = $this->_db->execute($sql, $params);
		if ($questions === false) {
			$this->_db->addError();
			return false;
		}

		$sql = "SELECT question_id, choice_sequence, choice_value, graph ".
				"FROM {questionnaire_choice} ".
				"WHERE questionnaire_id = ? ".
				"ORDER BY question_id, choice_sequence";
		$choices = $this->_db->execute($sql, $params);
		if ($choices === false) {
			$this->_db->addError();
			return false;
		}

    	foreach ($questions as $question) {
    		$oldQuestionID = $question["question_id"];
    		unset($question["question_id"]);
    		$question["questionnaire_id"] = $questionnaireID;

    		$questionID = $this->_db->insertExecute("questionnaire_question", $question, true, "question_id");
	        if (!$questionID) {
				return false;
			}

			reset($choices);
			$index = key($choices);
			foreach ($choices as $choice) {
				if ($choice["question_id"] != $oldQuestionID) {
					break;
				}

				$choice["question_id"] = $questionID;
	    		$choice["questionnaire_id"] = $questionnaireID;

		        if (!$this->_db->insertExecute("questionnaire_choice", $choice, true, "choice_id")) {
					return false;
				}

				unset($choices[$index]);
				$index++;
			}
		}

		return true;
	}

	/**
	 * アンケートデータを変更する
	 *
	 * @param	array	$params	変更するアンケートデータ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function updateQuestionnaire($params = array())
	{
		if (!$this->_db->updateExecute("questionnaire", $params, "questionnaire_id", true)) {
			return false;
		}

		return true;
	}

	/**
	 * アンケート回答者数データを加算する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function incrementQuestionnaireAnswer()
	{
		$params = array($this->_request->getParameter("questionnaire_id"));

    	$sql = "UPDATE {questionnaire} SET ".
					"answer_count = answer_count + 1 ".
				"WHERE questionnaire_id = ?";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * アンケート回答者データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateQuestionnaireAnswer($countFlag)
	{
		$params = array("questionnaire_id" => $this->_request->getParameter("questionnaire_id"));
    	$params["answer_count"] = $this->_db->countExecute("questionnaire_summary", $params);
		if (!$this->updateQuestionnaire($params)) {
			return false;
		}

		return true;
	}

	/**
	 * アンケートデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteQuestionnaire()
	{
		$params = array("questionnaire_id" => $this->_request->getParameter("questionnaire_id"));

    	if (!$this->_db->deleteExecute("questionnaire_block", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("questionnaire_answer", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("questionnaire_summary", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("questionnaire_choice", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("questionnaire_question", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("questionnaire", $params)) {
    		return false;
    	}

		$container =& DIContainerFactory::getContainer();
		$whatsnewAction =& $container->getComponent("whatsnewAction");
		$whatsnewAction->delete($this->_request->getParameter("questionnaire_id"));

		return true;
	}

	/**
	 * アンケート用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$blockID = $this->_request->getParameter("block_id");

		$params = array($blockID);
		$sql = "SELECT block_id ".
				"FROM {questionnaire_block} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $blockID,
			"questionnaire_id" => $this->_request->getParameter("questionnaire_id")
		);
		if (count($blockIDs) > 0) {
			$result = $this->_db->updateExecute("questionnaire_block", $params, "block_id", true);
		} else {
	    	$result = $this->_db->insertExecute("questionnaire_block", $params, true);
        }
		if (!$result) {
			return false;
		}

		if (!$this->setWhatsnew()) {
			return false;
		}

		return true;
	}

	/**
	 * 新着データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setWhatsnew()
	{
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$questionnaire = $this->_request->getParameter("questionnaire");
		if ($actionName == "questionnaire_action_edit_questionnaire_current"
				&& $questionnaire["status"] != QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
			return true;
		}

		$questionnaireView =& $container->getComponent("questionnaireView");
		$currentQuestionnaireID = $questionnaireView->getCurrentQuestionnaireID();
		if ($currentQuestionnaireID === false) {
			return false;
		}
		$questionnaireID = $this->_request->getParameter("questionnaire_id");
		/*
		if ($actionName == "questionnaire_action_edit_questionnaire_status" &&
				($questionnaireID != $currentQuestionnaireID ||
				$this->_request->getParameter("status") != QUESTIONNAIRE_STATUS_ACTIVE_VALUE)) {
			return true;
		}
		*/

		$whatsnewAction =& $container->getComponent("whatsnewAction");
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

    	$questionnaire_name = mb_substr($questionnaire["questionnaire_name"], 0, QUESTIONNAIRE_WHATSNEW_TITLE, INTERNAL_CODE);
    	if ($questionnaire_name != $questionnaire["questionnaire_name"]) {
    		$questionnaire_name .= _SEARCH_MORE;
    	}
		$whatsnew = array(
			"unique_id" => $questionnaireID,
			"title" => sprintf($smartyAssign->getLang("questionnaire_whatsnew"), $questionnaire_name),
			"description" => "",
			"action_name" => "questionnaire_view_main_whatsnew",
			"parameters" => "questionnaire_id=". $questionnaireID
		);
		$result = $whatsnewAction->insert($whatsnew);
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * 質問データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setQuestion()
	{
    	$params = array("description" => $this->_request->getParameter("description"));

		$questionType = intval($this->_request->getParameter("question_type"));
        $questionnaire = $this->_request->getParameter("questionnaire");
        if ($questionnaire["status"] == QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
	    	$params["question_value"] = $this->_request->getParameter("question_value");
	    	$params["question_type"] = $questionType;
	    	$params["require_flag"] = intval($this->_request->getParameter("require_flag"));
        }

		$questionID = $this->_request->getParameter("question_id");
		if (empty($questionID)
				&& $questionnaire["status"] != QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
			return false;
		}
		if (empty($questionID)) {
			$params["questionnaire_id"] = $this->_request->getParameter("questionnaire_id");
			$params["question_sequence"] = $this->_request->getParameter("question_sequence");
			$result = $this->_db->insertExecute("questionnaire_question", $params, true, "question_id");
		} else {
			$params["question_id"] = $questionID;
			$result = $this->_db->updateExecute("questionnaire_question", $params, "question_id", true);
		}
		if (!$result) {
			return false;
		}

		if (empty($questionID)) {
			$questionID = $result;
		}

		$requestChoiceIDs = $this->_request->getParameter("choice_id");
		if ($questionnaire["status"] == QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
			$params = array($questionID);
			$sql = "SELECT choice_id ".
						"FROM {questionnaire_choice} ".
						"WHERE question_id = ?";

			$choiceIDs = $this->_db->execute($sql, $params, null, null, false, array($this, "_makeChoiceIDs"));
			if ($choiceIDs === false) {
	        	$this->_db->addError();
				return false;
			}

	        if ($questionType == QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
				$deleteChoiceIDs = $choiceIDs;
			} else {
				$requestChoiceIDs = $this->_request->getParameter("choice_id");
				$deleteChoiceIDs = array_diff($choiceIDs, $requestChoiceIDs);
			}
			if (!empty($deleteChoiceIDs)) {
				$sql = "DELETE FROM {questionnaire_choice} ".
						"WHERE choice_id IN (". implode(",", $deleteChoiceIDs). ")";
				$result = $this->_db->execute($sql);
				if ($result === false) {
					$this->_db->addError();
					return false;
				}
			}
		}

		if ($questionType == QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
			return true;
		}

		$questionnaireID = $this->_request->getParameter("questionnaire_id");
		$choiceValues = $this->_request->getParameter("choice_value");
		$graphs = $this->_request->getParameter("graph");
		$choiceSequence = 0;
		foreach (array_keys($requestChoiceIDs) as $index) {
			if (empty($requestChoiceIDs[$index])
					&& $questionnaire["status"] != QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
				return false;
			}

			$choiceSequence++;

			$params = array("graph" => $graphs[$index]);
			if ($questionnaire["status"] == QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
				$params["choice_sequence"] = $choiceSequence;
				$params["choice_value"] = $choiceValues[$index];
			}

			if (empty($requestChoiceIDs[$index])) {
				$params["questionnaire_id"] = $questionnaireID;
				$params["question_id"] = $questionID;

				$result = $this->_db->insertExecute("questionnaire_choice", $params, true, "choice_id");
			} else {
				$params["choice_id"] = $requestChoiceIDs[$index];
				$result = $this->_db->updateExecute("questionnaire_choice", $params, "choice_id", true);
			}
			if (!$result) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 選択肢ID配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	選択肢ID配列
	 * @access	private
	 */
	function &_makeChoiceIDs(&$recordSet)
	{
		$choiceIDs = array();
		while ($row = $recordSet->fetchRow()) {
			$choiceIDs[] = $row[0];
		}

		return $choiceIDs;
	}

	/**
	 * 質問番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateQuestionSequence()
	{
		$dragSequence = $this->_request->getParameter("drag_sequence");
		$dropSequence = $this->_request->getParameter("drop_sequence");

		$params = array(
			$this->_request->getParameter("questionnaire_id"),
			$dragSequence,
			$dropSequence
		);

        if ($dragSequence > $dropSequence) {
        	$sql = "UPDATE {questionnaire_question} ".
					"SET question_sequence = question_sequence + 1 ".
					"WHERE questionnaire_id = ? ".
					"AND question_sequence < ? ".
					"AND question_sequence > ?";
        } else {
        	$sql = "UPDATE {questionnaire_question} ".
					"SET question_sequence = question_sequence - 1 ".
					"WHERE questionnaire_id = ? ".
					"AND question_sequence > ? ".
					"AND question_sequence <= ?";
        }

		$result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		if ($dragSequence > $dropSequence) {
			$dropSequence++;
		}
		$params = array(
			$dropSequence,
			$this->_request->getParameter("drag_question_id")
		);

    	$sql = "UPDATE {questionnaire_question} ".
				"SET question_sequence = ? ".
				"WHERE question_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 質問データを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteQuestion()
	{
		$questionnaire = $this->_request->getParameter("questionnaire");
		if ($questionnaire["status"] != QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
			return false;
		}

		$params = array("question_id" => $this->_request->getParameter("question_id"));
		$sql = "SELECT DISTINCT summary_id ".
				"FROM {questionnaire_answer} ".
				"WHERE question_id = ?";
		$summaryIDs = $this->_db->execute($sql, $params, 1, null, false);
		if ($summaryIDs === false) {
			$this->_db->addError();
			return false;
		}

     	if (!$this->_db->deleteExecute("questionnaire_answer", $params)) {
    		return false;
    	}

    	foreach ($summaryIDs as $summaryID) {
	     	if (!$this->updateSummary($summaryID)) {
	    		return false;
	    	}
    	}

     	if (!$this->_db->deleteExecute("questionnaire_choice", $params)) {
    		return false;
    	}

		$sql = "SELECT question_sequence ".
				"FROM {questionnaire_question} ".
				"WHERE question_id = ?";
		$sequences = $this->_db->execute($sql, $params, 1, null, false);
		if ($sequences === false) {
			$this->_db->addError();
			return false;
		}
		$sequence = $sequences[0][0];

    	if (!$this->_db->deleteExecute("questionnaire_question", $params)) {
    		return false;
    	}

		$params = array("questionnaire_id" => $this->_request->getParameter("questionnaire_id"));
		$sequenceParam = array("question_sequence" => $sequence);
		if (!$this->_db->seqExecute("questionnaire_question", $params, $sequenceParam)) {
			return false;
		}

    	return true;
	}

	/**
	 * 選択数を変更する
	 *
	 * @param	array	$answerChoiceIDs	変更する選択肢ID配列
     * @return boolean	true or false
	 * @access	public
	 */
	function incrementChoice($answerChoiceIDs)
	{
		if (empty($answerChoiceIDs)) {
			return true;
		}

    	$sql = "UPDATE {questionnaire_choice} SET ".
				"choice_count = choice_count + 1 ".
				"WHERE choice_id IN (". implode(",", $answerChoiceIDs). ")";
		$result = $this->_db->execute($sql);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 集計データを登録する
	 *
     * @return string	登録した集計ID
	 * @access	public
	 */
	function insertSummary()
	{
        $container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");

        $questionnaireID = $this->_request->getParameter("questionnaire_id");

        $params = array(
        	$questionnaireID,
        	$session->getParameter("_user_id")
        );
		$sql = "SELECT COUNT(summary_id) ".
				"FROM {questionnaire_summary} ".
				"WHERE questionnaire_id = ? ".
				"AND insert_user_id = ?";
		$counts = $this->_db->execute($sql, $params, 1, 0, false);
		if ($counts === false) {
			$this->_db->addError();
			return false;
		}
		$answerNumber = $counts[0][0] + 1;

		$params = array(
			"questionnaire_id" => $questionnaireID,
	        "answer_flag" => QUESTIONNAIRE_ANSWER_NONE_VALUE,
	        "answer_number" => $answerNumber,
	        "answer_time" => timezone_date()
		);
        $summaryID = $this->_db->insertExecute("questionnaire_summary", $params, true, "summary_id");
        if ($summaryID === false) {
			return false;
		}

		return $summaryID;
	}

	/**
	 * 集計データを変更する
	 *
	 * @param	string	$summaryID	集計ID
     * @return boolean	true or false
	 * @access	public
	 */
	function updateSummary($summaryID)
	{
		$params = array($summaryID);
		$updateParams["summary_id"] = $summaryID;

		$sql = "SELECT insert_time ".
					"FROM {questionnaire_answer} ".
					"WHERE summary_id = ? ".
					"ORDER BY answer_id DESC";
		$insertTimes = $this->_db->execute($sql, $params, 1, null, false);
		if ($insertTimes === false) {
			$this->_db->addError();
			return false;
		}
		$updateParams["answer_time"] = $insertTimes[0][0];

		$sql = "SELECT MIN(questionnaire_id), COUNT(answer_id) ".
					"FROM {questionnaire_answer} ".
					"WHERE summary_id = ?";
		$answers = $this->_db->execute($sql, $params, 1, null, false);
		if ($answers === false) {
			$this->_db->addError();
			return false;
		}
		list($questionnaireID, $answerCount) = $answers[0];

    	$params = array("questionnaire_id" => $questionnaireID);
    	$questionCount = $this->_db->countExecute("questionnaire_question", $params);

		$updateParams["answer_flag"] = QUESTIONNAIRE_ANSWER_DONE_VALUE;
		if ($answerCount < $questionCount ) {
			$updateParams["answer_flag"] = QUESTIONNAIRE_ANSWER_NONE_VALUE;
		}

        if (!$this->_db->updateExecute("questionnaire_summary", $updateParams, "summary_id", true)) {
			return false;
		}

		return true;
	}

	/**
	 * 回答データを登録する
	 *
	 * @param	array	$params	登録する回答データ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function insertAnswer($params = array())
	{
        if (!$this->_db->insertExecute("questionnaire_answer", $params, true, "answer_id")) {
        	return false;
        }

		return true;
	}

	/**
	 * 回答データを変更する
	 *
	 * @param	array	$params	登録する回答データ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function updateAnswer($params = array())
	{
        if (!$this->_db->updateExecute("questionnaire_answer", $params, "answer_id", true)) {
			return false;
		}

		return true;
	}
}
?>