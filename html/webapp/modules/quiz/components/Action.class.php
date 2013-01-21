<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テストデータ登録コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Components_Action
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
	function Quiz_Components_Action()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * 小テストデータを登録する
	 *
     * @return	boolean	true or false
	 * @access	public
	 */
	function setQuiz()
	{
		$quizType = intval($this->_request->getParameter("quiz_type"));
		if ($quizType == QUIZ_TYPE_SEQUENCE_VALUE) {
			$this->_request->setParameter("correct_flag", _ON);
		}
		if ($quizType == QUIZ_TYPE_SEQUENCE_VALUE &&
				$this->_request->getParameter("quiz_type_random") == _ON) {
			$quizType = QUIZ_TYPE_RANDOM_VALUE;
		}
		$this->_request->setParameter("quiz_type", $quizType);

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

		$params = array(
			"image_authentication" => intval($this->_request->getParameter("image_authentication")),
			"total_flag" => intval($this->_request->getParameter("total_flag")),
			"mail_send" => intval($this->_request->getParameter("mail_send")),
			"mail_subject" => $this->_request->getParameter("mail_subject"),
			"mail_body" => $this->_request->getParameter("mail_body")
		);

        $quiz = $this->_request->getParameter("quiz");
        if (empty($quiz) ||
        		$quiz["status"] == QUIZ_STATUS_INACTIVE_VALUE) {
			$params["quiz_name"] = $this->_request->getParameter("quiz_name");
			$params["icon_name"] = $this->_request->getParameter("icon_name");
        	$params["quiz_type"] = $this->_request->getParameter("quiz_type");
			$params["nonmember_flag"] = intval($this->_request->getParameter("nonmember_flag"));
			$params["repeat_flag"] = intval($this->_request->getParameter("repeat_flag"));
			$params["correct_flag"] = intval($this->_request->getParameter("correct_flag"));
        }

        if ($quiz["status"] != QUIZ_STATUS_END_VALUE) {
			$params["period"] = $this->_request->getParameter("period");
		}

        $quizID = $this->_request->getParameter("quiz_id");
		if (empty($quizID)) {
			$params["room_id"] = $this->_request->getParameter("room_id");
			$params["status"] = QUIZ_STATUS_INACTIVE_VALUE;
			$result = $this->_db->insertExecute("quiz", $params, true, "quiz_id");
		} else {
			$params["quiz_id"] = $quizID;
			$result = $this->_db->updateExecute("quiz", $params, "quiz_id", true);
		}
		if (!$result) {
			return false;
		}

        if (!empty($quizID)) {
        	return true;
        }

		$quizID = $result;
		$this->_request->setParameter("quiz_id", $quizID);
		if ($this->_request->getParameter("old_use") != _ON) {
			return true;
		}

		$params = array($this->_request->getParameter("old_quiz_id"));
		$sql = "SELECT question_id, question_sequence, question_value, question_type, ".
						"allotment, correct, require_flag, description ".
				"FROM {quiz_question} ".
				"WHERE quiz_id = ? ".
				"ORDER BY question_id";
		$questions = $this->_db->execute($sql, $params);
		if ($questions === false) {
			$this->_db->addError();
			return false;
		}

		$sql = "SELECT question_id, choice_sequence, choice_value, graph ".
				"FROM {quiz_choice} ".
				"WHERE quiz_id = ? ".
				"ORDER BY question_id, choice_sequence";
		$choices = $this->_db->execute($sql, $params);
		if ($choices === false) {
			$this->_db->addError();
			return false;
		}

    	foreach ($questions as $question) {
    		$oldQuestionID = $question["question_id"];
    		unset($question["question_id"]);
    		$question["quiz_id"] = $quizID;

    		$questionID = $this->_db->insertExecute("quiz_question", $question, true, "question_id");
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
	    		$choice["quiz_id"] = $quizID;

		        if (!$this->_db->insertExecute("quiz_choice", $choice, true, "choice_id")) {
					return false;
				}

				unset($choices[$index]);
				$index++;
			}
		}

		if (!$this->updatePerfectScore()) {
			return false;
		}

		return true;
	}

	/**
	 * 小テストデータを変更する
	 *
	 * @param	array	$params	変更する小テストデータ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function updateQuiz($params = array())
	{
        if (!$this->_db->updateExecute("quiz", $params, "quiz_id", true)) {
			return false;
		}
		return true;
	}

	/**
	 * 小テスト得点、小テスト解答者数データを加算する
	 *
	 * @param	string	$summaryScore	加算する点数
 	 * @param	boolean	$countFlag		true:解答者数を加算する、false:解答者数を加算しない
     * @return boolean	true or false
	 * @access	public
	 */
	function incrementQuizAnswer($summaryScore, $countFlag)
	{
		$params = array(
			$summaryScore,
			$this->_request->getParameter("quiz_id")
		);

    	$sql = "UPDATE {quiz} SET ".
					"quiz_score = quiz_score + ? ";
		if ($countFlag) {
			$sql .= ",answer_count = answer_count + 1 ";
		}
		$sql .= "WHERE quiz_id = ?";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 満点データを更新する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updatePerfectScore()
	{
		$quizID = $this->_request->getParameter("quiz_id");
		$params = array($quizID);
    	$sql = "SELECT SUM(allotment) ".
    			"FROM {quiz_question} ".
    			"WHERE quiz_id = ?";
		$result = $this->_db->execute($sql, $params, 1, null, false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$perfectScore = $result[0][0];

		$params = array(
			"quiz_id" => $quizID,
			"perfect_score" => $perfectScore
		);
        if (!$this->updateQuiz($params)) {
			return false;
		}

		return true;
	}

	/**
	 * 小テスト得点、小テスト解答者データを変更する
	 *
 	 * @param	boolean	$countFlag		true:解答者数を更新する、false:解答者数を更新しない
     * @return boolean	true or false
	 * @access	public
	 */
	function updateQuizAnswer($countFlag)
	{
		$params = array("quiz_id" => $this->_request->getParameter("quiz_id"));
		$sql = "SELECT SUM(summary_score), COUNT(summary_id) ".
					"FROM {quiz_summary} ".
					"WHERE quiz_id = ?";
		$result = $this->_db->execute($sql, $params, 1, null, false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		list($quizScore, $answerCount) = $result[0];

		$params["quiz_score"] = $quizScore;
		if ($countFlag) {
			$params["answer_count"] = $answerCount;
		}

		if (!$this->updateQuiz($params)) {
			return false;
		}

		return true;
	}

	/**
	 * 小テストデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteQuiz()
	{
		$params = array("quiz_id" => $this->_request->getParameter("quiz_id"));

    	if (!$this->_db->deleteExecute("quiz_block", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("quiz_answer", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("quiz_summary", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("quiz_choice", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("quiz_question", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("quiz", $params)) {
    		return false;
    	}

		$container =& DIContainerFactory::getContainer();
		$whatsnewAction =& $container->getComponent("whatsnewAction");
		$whatsnewAction->delete($this->_request->getParameter("quiz_id"));

		return true;
	}

	/**
	 * 小テスト用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$blockID = $this->_request->getParameter("block_id");

		$params = array($blockID);
		$sql = "SELECT block_id ".
				"FROM {quiz_block} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $blockID,
			"quiz_id" => $this->_request->getParameter("quiz_id")
		);
		if (count($blockIDs) > 0) {
			$result = $this->_db->updateExecute("quiz_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("quiz_block", $params, true);
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
		$quiz = $this->_request->getParameter("quiz");

		if ($actionName == "quiz_action_edit_quiz_current" &&
				$quiz["status"] != QUIZ_STATUS_INACTIVE_VALUE) {
			return true;
		}

		$quizView =& $container->getComponent("quizView");
		$currentQuizID = $quizView->getCurrentQuizID();
		if ($currentQuizID === false) {
			return false;
		}
		$quizID = $this->_request->getParameter("quiz_id");
		/*
		if ($actionName == "quiz_action_edit_quiz_status" &&
				($quizID != $currentQuizID ||
				$this->_request->getParameter("status") != QUIZ_STATUS_ACTIVE_VALUE)) {
			return true;
		}
		*/

		$whatsnewAction =& $container->getComponent("whatsnewAction");
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

    	$quiz_name = mb_substr($quiz["quiz_name"], 0, QUIZ_WHATSNEW_TITLE, INTERNAL_CODE);
    	if ($quiz_name != $quiz["quiz_name"]) {
    		$quiz_name .= _SEARCH_MORE;
    	}
		$whatsnew = array(
			"unique_id" => $quizID,
			"title" => sprintf($smartyAssign->getLang("quiz_whatsnew"), $quiz_name),
			"description" => "",
			"action_name" => "quiz_view_main_whatsnew",
			"parameters" => "quiz_id=". $quizID
		);
		$result = $whatsnewAction->insert($whatsnew);
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * 問題データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setQuestion()
	{
    	$params = array("description" => $this->_request->getParameter("description"));

    	$questionType = intval($this->_request->getParameter("question_type"));
        $quiz = $this->_request->getParameter("quiz");
        if ($quiz["status"] == QUIZ_STATUS_INACTIVE_VALUE) {
	    	$params["question_value"] = $this->_request->getParameter("question_value");
	    	$params["question_type"] = $questionType;
	    	$params["allotment"] = intval($this->_request->getParameter("allotment"));
	    	$params["require_flag"] = intval($this->_request->getParameter("require_flag"));

			if ($questionType != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
				$params["correct"] = $this->_request->getParameter("correct");
			}
		}

		$questionID = $this->_request->getParameter("question_id");
		if (empty($questionID)
				&& $quiz["status"] != QUIZ_STATUS_INACTIVE_VALUE) {
			return false;
		}
		if (empty($questionID)) {
			$params["quiz_id"] = $this->_request->getParameter("quiz_id");
			$params["question_sequence"] = $this->_request->getParameter("question_sequence");
			$result = $this->_db->insertExecute("quiz_question", $params, true, "question_id");
		} else {
			$params["question_id"] = $questionID;
			$result = $this->_db->updateExecute("quiz_question", $params, "question_id", true);
		}
		if (!$result) {
			return false;
		}

		if (empty($questionID)) {
			$questionID = $result;
		}

		if ($quiz["status"] == QUIZ_STATUS_INACTIVE_VALUE &&
				!$this->updatePerfectScore()) {
			return false;
		}

		$requestChoiceIDs = $this->_request->getParameter("choice_id");
		if ($quiz["status"] == QUIZ_STATUS_INACTIVE_VALUE) {
			$params = array($questionID);
			$sql = "SELECT choice_id ".
						"FROM {quiz_choice} ".
						"WHERE question_id = ?";

			$choiceIDs = $this->_db->execute($sql, $params, null, null, false, array($this, "_makeChoiceIDs"));
			if ($choiceIDs === false) {
	        	$this->_db->addError();
				return false;
			}

	        if ($questionType == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
				$deleteChoiceIDs = $choiceIDs;
			} else {
				$deleteChoiceIDs = array_diff($choiceIDs, $requestChoiceIDs);
			}
			if (!empty($deleteChoiceIDs)) {
				$sql = "DELETE FROM {quiz_choice} ".
						"WHERE choice_id IN (". implode(",", $deleteChoiceIDs). ")";
				$result = $this->_db->execute($sql);
				if ($result === false) {
					$this->_db->addError();
					return false;
				}
			}
		}

		if ($questionType == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
			return true;
		}

		$quizID = $this->_request->getParameter("quiz_id");
		$choiceValues = $this->_request->getParameter("choice_value");
		$graphs = $this->_request->getParameter("graph");
		$choiceSequence = 0;
		$container =& DIContainerFactory::getContainer();
		$quizView =& $container->getComponent("quizView");
		foreach (array_keys($requestChoiceIDs) as $index) {
			if (empty($requestChoiceIDs[$index])
					&& $quiz["status"] != QUIZ_STATUS_INACTIVE_VALUE) {
				return false;
			}

			$choiceSequence++;

			$params = array(
				"graph" => isset($graphs[$index]) ? $graphs[$index] : ""
			);
			if ($quiz["status"] == QUIZ_STATUS_INACTIVE_VALUE) {
				$params["choice_sequence"] = $choiceSequence;
				if ($questionType == QUIZ_QUESTION_TYPE_WORD_VALUE) {
					$choiceValues[$index] = $quizView->getSynonym($choiceValues[$index]);
				}
				$params["choice_value"] = $choiceValues[$index];
			}

			if (empty($requestChoiceIDs[$index])) {
				$params["quiz_id"] = $quizID;
				$params["question_id"] = $questionID;

				$result = $this->_db->insertExecute("quiz_choice", $params, true, "choice_id");
			} else {
				$params["choice_id"] = $requestChoiceIDs[$index];
				$result = $this->_db->updateExecute("quiz_choice", $params, "choice_id", true);
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
	 * 問題番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateQuestionSequence()
	{
		$dragSequence = $this->_request->getParameter("drag_sequence");
		$dropSequence = $this->_request->getParameter("drop_sequence");

		$params = array(
			$this->_request->getParameter("quiz_id"),
			$dragSequence,
			$dropSequence
		);

        if ($dragSequence > $dropSequence) {
        	$sql = "UPDATE {quiz_question} ".
					"SET question_sequence = question_sequence + 1 ".
					"WHERE quiz_id = ? ".
					"AND question_sequence < ? ".
					"AND question_sequence > ?";
        } else {
        	$sql = "UPDATE {quiz_question} ".
					"SET question_sequence = question_sequence - 1 ".
					"WHERE quiz_id = ? ".
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

    	$sql = "UPDATE {quiz_question} ".
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
	 * 問題データを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteQuestion()
	{
		$quiz = $this->_request->getParameter("quiz");
		if ($quiz["status"] != QUIZ_STATUS_INACTIVE_VALUE) {
			return false;
		}

		$params = array("question_id" => $this->_request->getParameter("question_id"));
		$sql = "SELECT DISTINCT summary_id ".
				"FROM {quiz_answer} ".
				"WHERE question_id = ?";
		$summaryIDs = $this->_db->execute($sql, $params, 1, null, false);
		if ($summaryIDs === false) {
			$this->_db->addError();
			return false;
		}

     	if (!$this->_db->deleteExecute("quiz_answer", $params)) {
    		return false;
    	}

    	foreach ($summaryIDs as $summaryID) {
    		$result = $this->updateSummary($summaryID);
	     	if ($result === false) {
	    		return false;
	    	}
    	}

     	if (!$this->_db->deleteExecute("quiz_choice", $params)) {
    		return false;
    	}

		$sql = "SELECT question_sequence ".
				"FROM {quiz_question} ".
				"WHERE question_id = ?";
		$sequences = $this->_db->execute($sql, $params, 1, null, false);
		if ($sequences === false) {
			$this->_db->addError();
			return false;
		}
		$sequence = $sequences[0][0];

    	if (!$this->_db->deleteExecute("quiz_question", $params)) {
    		return false;
    	}

		if ($quiz["status"] == QUIZ_STATUS_INACTIVE_VALUE &&
			!$this->updatePerfectScore()) {
			return false;
		}

		$params = array("quiz_id" => $this->_request->getParameter("quiz_id"));
		$sequenceParam = array("question_sequence" => $sequence);
		if (!$this->_db->seqExecute("quiz_question", $params, $sequenceParam)) {
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

    	$sql = "UPDATE {quiz_choice} SET ".
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

        $quizID = $this->_request->getParameter("quiz_id");

        $params = array(
        	$quizID,
        	$session->getParameter("_user_id")
        );
		$sql = "SELECT COUNT(summary_id) ".
				"FROM {quiz_summary} ".
				"WHERE quiz_id = ? ".
				"AND insert_user_id = ?";
		$counts = $this->_db->execute($sql, $params, 1, 0, false);
		if ($counts === false) {
			$this->_db->addError();
			return $counts;
		}
		$answerNumber = $counts[0][0] + 1;

		$params = array(
			"quiz_id" => $quizID,
	        "answer_flag" => QUIZ_ANSWER_NONE_VALUE,
	        "answer_number" => $answerNumber,
	        "summary_score" => 0,
	        "answer_time" => timezone_date()
		);
        $summaryID = $this->_db->insertExecute("quiz_summary", $params, true, "summary_id");
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
					"FROM {quiz_answer} ".
					"WHERE summary_id = ? ".
					"ORDER BY answer_id DESC";
		$insertTimes = $this->_db->execute($sql, $params, 1, null, false);
		if ($insertTimes === false) {
			$this->_db->addError();
			return $insertTimes;
		}
		$updateParams["answer_time"] = $insertTimes[0][0];

		$sql = "SELECT MIN(quiz_id), COUNT(answer_id), MIN(answer_flag), SUM(score) ".
					"FROM {quiz_answer} ".
					"WHERE summary_id = ?";
		$answers = $this->_db->execute($sql, $params, 1, null, false);
		if ($answers === false) {
			$this->_db->addError();
			return $answers;
		}
		list($quizID, $answerCount, $answerFlag, $updateParams["summary_score"]) = $answers[0];

    	$params = array("quiz_id" => $quizID);
    	$questionCount = $this->_db->countExecute("quiz_question", $params);

		$updateParams["answer_flag"] = QUIZ_ANSWER_NOT_MARK_VALUE;
		if ($answerCount < $questionCount ) {
			$updateParams["answer_flag"] = QUIZ_ANSWER_NONE_VALUE;
		} elseif ($answerFlag > QUIZ_ANSWER_SCORED_VALUE) {
			$updateParams["answer_flag"] = QUIZ_ANSWER_SCORED_VALUE;
		}

        if (!$this->_db->updateExecute("quiz_summary", $updateParams, "summary_id", true)) {
			return false;
		}

		return true;
	}

	/**
	 * 解答データを登録する
	 *
	 * @param	array	$params	登録する解答データ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function insertAnswer($params = array())
	{
        if (!$this->_db->insertExecute("quiz_answer", $params, true, "answer_id")) {
			return false;
		}

		return true;
	}

	/**
	 * 解答データを変更する
	 *
	 * @param	array	$params	登録する解答データ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function updateAnswer($params = array())
	{
        if (!$this->_db->updateExecute("quiz_answer", $params, "answer_id", true)) {
			return false;
		}

		return true;
	}
}
?>
