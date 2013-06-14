<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケートデータ取得コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Components_View
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
	function Questionnaire_Components_View()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * アンケートが配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock()
	{
		$params = array($this->_request->getParameter("questionnaire_id"));
		$sql = "SELECT Q.room_id, B.block_id ".
				"FROM {questionnaire} Q ".
				"INNER JOIN {questionnaire_block} B ".
				"ON Q.questionnaire_id = B.questionnaire_id ".
				"WHERE Q.questionnaire_id = ? ".
				"ORDER BY B.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}

	/**
	 * アンケートが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function questionnaireExists()
	{
		$params = array(
			$this->_request->getParameter("questionnaire_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT questionnaire_id ".
				"FROM {questionnaire} ".
				"WHERE questionnaire_id = ? ".
				"AND room_id = ?";
		$questionnaireIDs = $this->_db->execute($sql, $params);
		if ($questionnaireIDs === false) {
			$this->_db->addError();
			return $questionnaireIDs;
		}

		if (count($questionnaireIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDのアンケート件数を取得する
	 *
     * @return string	アンケート件数
	 * @access	public
	 */
	function getQuestionnaireCount()
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$count = $this->_db->countExecute("questionnaire", $params);

		return $count;
	}

	/**
	 * 現在配置されているアンケートのIDを取得する
	 *
     * @return string	配置されているアンケートID
	 * @access	public
	 */
	function &getCurrentQuestionnaireID()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT questionnaire_id ".
				"FROM {questionnaire_block} ".
				"WHERE block_id = ?";
		$questionnaireIDs = $this->_db->execute($sql, $params);
		if ($questionnaireIDs === false) {
			$this->_db->addError();
			return $questionnaireIDs;
		}

		return $questionnaireIDs[0]["questionnaire_id"];
	}


	/**
	 * アンケートの設定データを取得する
	 *
     * @return string	設定データ配列
	 * @access	public
	 */
	function &getConfig()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);

		return $config;
	}

	/**
	 * アンケート一覧データを取得する
	 *
     * @return array	アンケート一覧データ配列
	 * @access	public
	 */
	function &getQuestionnaires()
	{
		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "questionnaire_id";
		}
		$sortColumn = "Q.". $sortColumn;
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT Q.questionnaire_id, Q.questionnaire_name, Q.icon_name, Q.status, Q.nonmember_flag, Q.answer_count, ".
							"Q.insert_time, Q.insert_user_id, Q.insert_user_name, ".
							"COUNT(QS.question_id) AS question_count ".
				"FROM {questionnaire} Q ".
				"LEFT JOIN {questionnaire_question} QS ".
				"ON Q.questionnaire_id = QS.questionnaire_id ".
				"WHERE Q.room_id = ? ".
				"GROUP BY Q.questionnaire_id, Q.questionnaire_name, Q.icon_name, Q.status, ".
							"Q.insert_time, Q.insert_user_id, Q.insert_user_name ".
				$this->_db->getOrderSQL($orderParams);
		$questionnaires = $this->_db->execute($sql, $params, $limit, $offset);
		if ($questionnaires === false) {
			$this->_db->addError();
		}

		return $questionnaires;
	}

	/**
	 * アンケート用デフォルトデータを取得する
	 *
     * @return array	アンケート用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultQuestionnaire()
	{
		$config = $this->getConfig();
		if ($config === false) {
        	return $config;
        }

		$questionnaire = array(
			"old_use" => constant($config["old_use"]["conf_value"]),
			"status" => QUESTIONNAIRE_STATUS_INACTIVE_VALUE,
			"mail_send" => constant($config["mail_send"]["conf_value"]),
			"questionnaire_type" => constant($config["questionnaire_type"]["conf_value"]),
			"nonmember_flag" => constant($config["nonmember_flag"]["conf_value"]),
			"image_authentication" => constant($config["image_authentication"]["conf_value"]),
			"anonymity_flag" => constant($config["anonymity_flag"]["conf_value"]),
			"keypass_use_flag" => constant($config["keypass_use_flag"]["conf_value"]),
			"keypass_phrase" => $config["keypass_phrase"]["conf_value"],
			"repeat_flag" => constant($config["repeat_flag"]["conf_value"]),
			"total_flag" => constant($config["total_flag"]["conf_value"]),
			"answer_show_flag" => constant($config["answer_show_flag"]["conf_value"])
		);

		return $questionnaire;
	}

	/**
	 * アンケートデータを取得する
	 *
     * @return array	アンケートデータ配列
	 * @access	public
	 */
	function &getQuestionnaire()
	{
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$session =& $container->getComponent("Session");
		$edit = $session->getParameter("questionnaire_edit". $this->_request->getParameter("block_id"));

		$sql = "SELECT questionnaire_id, questionnaire_name, icon_name, status";
		$format = "";
		if ($actionName == "questionnaire_view_edit_questionnaire_entry") {
			$sql .= ", questionnaire_type, period, ".
						"nonmember_flag, image_authentication, anonymity_flag, keypass_use_flag, keypass_phrase, ".
                        "repeat_flag, total_flag, answer_show_flag, ".
						"answer_count, mail_send, mail_subject, mail_body ";
			$format = _INPUT_DATE_FORMAT;

		} elseif ($actionName == "questionnaire_view_edit_answer" ||
						$actionName == "questionnaire_view_edit_export") {
			$sql .= ", anonymity_flag, answer_count ";

		} else if ($actionName == "questionnaire_view_edit_reference") {
			$sql .= ", ". QUESTIONNAIRE_TYPE_LIST_VALUE. " AS questionnaire_type, period ";

		} elseif (strpos($actionName, "questionnaire_view_edit") === 0 ||
					strpos($actionName, "questionnaire_action_edit") === 0) {
			$sql .= " ";

		} elseif ($edit) {
			$sql .= ", questionnaire_type, period, ".
						"nonmember_flag, anonymity_flag, total_flag, answer_show_flag, ".
						"answer_count ";

		}

		$params = array($this->_request->getParameter("questionnaire_id"));
		$sql .= "FROM {questionnaire} ".
				"WHERE questionnaire_id = ?";

		$questionnaires = $this->_db->execute($sql, $params, 1, null, true, array($this, "_makeQuestionnaire"), $format);
		if ($questionnaires === false) {
			$this->_db->addError();
			return $questionnaires;
		}

		return $questionnaires[0];
	}

	/**
	 * ルームIDの既存アンケートデータを取得する
	 *
     * @return array	既存アンケートデータ配列
	 * @access	public
	 */
	function &getOldQuestionnaires()
	{
		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT questionnaire_id, questionnaire_name ".
				"FROM {questionnaire} Q ".
				"WHERE room_id = ? ".
				"ORDER BY questionnaire_id DESC";

		$questionnaires = $this->_db->execute($sql, $params);
		if ($questionnaires === false) {
			$this->_db->addError();
		}

		return $questionnaires;
	}

	/**
	 * 現在配置されているアンケートデータを取得する
	 *
     * @return array	配置されているアンケートデータ配列
	 * @access	public
	 */
	function &getCurrentQuestionnaire()
	{
		$params = array(
			$this->_request->getParameter("block_id"),
			QUESTIONNAIRE_STATUS_INACTIVE_VALUE
		);

		$sql = "SELECT Q.questionnaire_id, Q.questionnaire_name, Q.icon_name, Q.status, Q.questionnaire_type, Q.period, ".
						"Q.nonmember_flag, Q.image_authentication, Q.anonymity_flag, Q.keypass_use_flag, Q.keypass_phrase, Q.repeat_flag, Q.total_flag, Q.answer_show_flag, ".
						"Q.answer_count, Q.mail_send ".
				"FROM {questionnaire_block} B ".
				"INNER JOIN {questionnaire} Q ".
				"ON B.questionnaire_id = Q.questionnaire_id ".
				"WHERE B.block_id = ? ".
				"AND Q.status != ?";
		$questionnaires = $this->_db->execute($sql, $params, 1, null, true, array($this, "_makeQuestionnaire"));
		if ($questionnaires === false) {
			$this->_db->addError();
			return $questionnaires;
		}

		return $questionnaires[0];
	}

	/**
	 * アンケート配列を作成する
	 * 期限の日時をフォーマットする
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @access	private
	 */
	function &_makeQuestionnaire(&$recordSet, $format = _DATE_FORMAT)
	{
		$questionnaires = array();
		while ($row = $recordSet->fetchRow()) {
			if (empty($row["period"])) {
				$questionnaires[] = $row;
				continue;
			}

			$period = timezone_date_format($row["period"], null);
			if (substr($period, 8) == "000000") {
				$previousDay = -1;
				$format = str_replace("H", "24", $format);
				$timeFormat = str_replace("H", "24", _SHORT_TIME_FORMAT);
			} else {
				$previousDay = 0;
				$timeFormat = _SHORT_TIME_FORMAT;
			}

			$date = mktime(intval(substr($period, 8, 2)),
							intval(substr($period, 10, 2)),
							intval(substr($period, 12, 2)),
							intval(substr($period, 4, 2)),
							intval(substr($period, 6, 2)) + $previousDay,
							intval(substr($period, 0, 4)));
			$row["display_period_date"] = date($format, $date);
			$row["display_period_time"] = date($timeFormat, $date);

			$questionnaires[] = $row;
		}

		return $questionnaires;
	}

	/**
	 * 質問用デフォルトデータを取得する
	 *
     * @return array	質問用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultQuestion()
	{
		$config = $this->getConfig();
		if ($config === false) {
        	return $config;
        }

		$question = array(
			"questionnaire_id" => $this->_request->getParameter("questionnaire_id"),
			"question_type" => constant($config["question_type"]["conf_value"]),
			"require_flag" => constant($config["require_flag"]["conf_value"])
		);

		$choiceCount = $config["choice_count"]["conf_value"];
		$choiceLabel = $config["choice_label"]["conf_value"];
		$graphColor = $config["graph_color"]["conf_value"];
		$question["choices"] = $this->getDefaultChoiceLists($choiceCount, $choiceLabel, $graphColor);

		$params = array(
			"questionnaire_id" => $this->_request->getParameter("questionnaire_id")
		);
    	$question["question_sequence"] = $this->_db->countExecute("questionnaire_question", $params);
		if ($question["question_sequence"] === false) {
			return $question["question_sequence"];
		}
		$question["question_sequence"]++;

		return $question;
	}

	/**
	 * 質問データ取得用のSQL文を取得する
	 *
	 * @param	string	$whereColumn	WHERE句カラム名称
     * @return string	質問データ取得用のSQL文
	 * @access	public
	 */
	function &_getQuestionSQL($whereColumn)
	{
		$sql = "SELECT question_id, questionnaire_id, question_sequence, question_value";

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		switch($actionName) {
			case "questionnaire_view_edit_question_list":
				$sql .= " ";
				break;

			case "questionnaire_view_main_init":
			case "questionnaire_view_main_single":
			case "questionnaire_view_edit_reference":
				$sql .= ", question_type, require_flag ";
				break;

			default:
				$sql .= ", question_type, require_flag, description ";
		}

		$sql .= "FROM {questionnaire_question} ".
				"WHERE ". $whereColumn. " = ? ".
				"ORDER BY question_sequence";

		return $sql;
	}

	/**
	 * 質問データ取得用のSQL文を取得する
	 *
	 * @param	string	$whereColumn	WHERE句カラム名称
     * @return string	質問データ取得用のSQL文
	 * @access	public
	 */
	function &_getChoiceSQL($whereColumn)
	{
		$sql = "SELECT choice_id, question_id, choice_value, choice_count, graph ".
					"FROM {questionnaire_choice} ".
					"WHERE ". $whereColumn. " = ? ".
					"ORDER BY question_id, choice_sequence";

		return $sql;
	}

	/**
	 * 質問データを取得する
	 *
	 * @param	array	$questionID	質問ID
     * @return array	質問データ配列
	 * @access	public
	 */
	function &getQuestion($questionID = null)
	{
		if (empty($questionID)) {
			$questionID = $this->_request->getParameter("question_id");
		}

		$params = array($questionID);
		$sql = $this->_getQuestionSQL("question_id");
		$questions = $this->_db->execute($sql, $params);
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}
		$question = $questions[0];

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($question["question_type"] == QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE &&
				$actionName != "questionnaire_view_edit_question_entry") {
			return $question;
		}

		$choiceConfig = $this->getChoiceConfig();
		if ($choiceConfig === false) {
        	return $choiceConfig;
        }

		if ($question["question_type"] == QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
			$question["choices"] = $this->getDefaultChoiceLists($choiceConfig["choice_count"], $choiceConfig["choice_label"], $choiceConfig["graph_color"]);
		} else {
			$questions = array($questionID => $question);
			$sql = $this->_getChoiceSQL("question_id");
			$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeChoices"), array($questions, $choiceConfig["choice_label"]));
			if ($questions === false) {
				$this->_db->addError();
				return $questions;
			}
			$question["choices"] = $questions[$questionID]["choices"];
		}

		return $question;
	}

	/**
	 * アンケートIDの全質問データを取得する
	 *
     * @return array	質問データ配列
	 * @access	public
	 */
	function &getQuestions()
	{
		$params = array($this->_request->getParameter("questionnaire_id"));
		$sql = $this->_getQuestionSQL("questionnaire_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeQuestions"));
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "questionnaire_view_edit_question_list") {
			return $questions;
		}

		$sql = $this->_getChoiceSQL("questionnaire_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeChoices"), array($questions));
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}

		return $questions;
	}

	/**
	 * 選択肢配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$params[0]	質問データ配列
	 * @param	string	$params[1]	選択肢ラベル文字列
	 * @return array	質問データ配列
	 * @access	private
	 */
	function &_makeChoices(&$recordSet, &$params)
	{
		$questions = $params[0];
		$choiceLabel = "";
		if (!empty($params[1])) {
			$choiceLabel = $params[1];
		}
		while ($row = $recordSet->fetchRow()) {
			$choice = $this->getDefaultChoice($recordSet->CurrentRow() - 1, $choiceLabel, null);
			$row["label"] = $choice["label"];

			$questionID = $row["question_id"];

			if (empty($questions[$questionID]["choices"])) {
				$questions[$questionID]["choices"] = array();
			}

			$choiceID = $row["choice_id"];
			$questions[$questionID]["choices"][$choiceID] = $row;
		}

		return $questions;
	}

	/**
	 * 質問データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	質問データ配列
	 * @access	private
	 */
	function &_makeQuestions(&$recordSet)
	{
		$questions = array();
		while ($row = $recordSet->fetchRow()) {
			$questionID = $row["question_id"];
			$questions[$questionID] = $row;
		}

		return 	$questions;
	}

	/**
	 * 選択肢一覧用用デフォルトデータを取得する
	 *
	 * @param	string	$choiceCount	選択肢数
	 * @param	string	$graphColor	選択肢ラベル文字列
	 * @param	string	$graphColor	グラフ色文字列
     * @return array	選択肢一覧用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultChoiceLists($choiceCount, $choiceLabel, $graphColor)
	{
		$choiceLists = array();
		for ($i = 0; $i < $choiceCount; $i++) {
			$choiceLists[$i] = $this->getDefaultChoice($i, $choiceLabel, $graphColor);
		}

		return $choiceLists;
	}

	/**
	 * 選択肢数、選択肢グラフ色、選択肢ラベルのコンフィグデータを取得する
	 *
     * @return array	選択肢数、選択肢グラフ色、選択肢ラベルのコンフィグデータ配列
	 * @access	public
	 */
	function &getChoiceConfig()
	{
		$config = $this->getConfig();
		if (!$config) {
        	return $config;
        }

		$choiceConfig["choice_count"] = $config["choice_count"]["conf_value"];
		$choiceConfig["choice_label"] = $config["choice_label"]["conf_value"];
		$choiceConfig["graph_color"] = $config["graph_color"]["conf_value"];

		return $choiceConfig;
	}

	/**
	 * アンケートIDの全質問ID配列を取得する
	 *
     * @return array	質問ID配列
	 * @access	public
	 */
	function &getQuestionIDs()
	{
		$params = array($this->_request->getParameter("questionnaire_id"));
		$sql = "SELECT question_id ".
				"FROM {questionnaire_question} ".
				"WHERE questionnaire_id = ? ".
				"ORDER BY question_sequence";
		$questionIDs = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeQuestionIDs"));
		if ($questionIDs === false) {
			$this->_db->addError();
			return $questionIDs;
		}

		$questionnaire = $this->_request->getParameter("questionnaire");
		if ($questionnaire["questionnaire_type"] != QUESTIONNAIRE_TYPE_RANDOM_VALUE) {
			return $questionIDs;
		}

		$numbers = range(1, count($questionIDs));
		srand((float)microtime() * 1000000);
		shuffle($numbers);
		$tempDatas = array();
		foreach ($numbers as $number) {
		    $tempDatas[count($tempDatas) + 1] = $questionIDs[$number];
		}
		$questionIDs = $tempDatas;

		return $questionIDs;
	}

	/**
	 * 質問ID配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	質問データ配列
	 * @access	private
	 */
	function &_makeQuestionIDs(&$recordSet)
	{
		$questionsIDs = array();
		while ($row = $recordSet->fetchRow()) {
			$questionsIDs[$recordSet->CurrentRow()] = $row["question_id"];
		}

		return 	$questionsIDs;
	}

	/**
	 * 選択肢用デフォルトデータを取得する
	 *
	 * @param	string	$choiceSequence	選択肢シーケンス番号
	 * @param	array	$choiceLabel		選択肢ラベル配列
	 * @param	array	$graphColor		グラフ色配列
     * @return array	選択肢用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultChoice($choiceSequence, $choiceLabel, $graphColor)
	{
		$choice["choice_sequence"] = $choiceSequence;
		$choice["label"] = "";
		$choice["graph"] = "";

		if (!empty($choiceLabel)) {
			$choiceLabels = explode("|", $choiceLabel);
			$choice["label"] = $choiceLabels[$choiceSequence % count($choiceLabels)];
		}

		if (!empty($graphColor)) {
			$graphColors = explode("|", $graphColor);
			$choice["graph"] = $graphColors[$choiceSequence % count($graphColors)];
		}

		return $choice;
	}

	/**
	 * 質問番号データを取得する
	 *
     * @return array	質問番号データ配列
	 * @access	public
	 */
	function &getQuestionSequence()
	{
		$params = array(
			$this->_request->getParameter("drag_question_id"),
			$this->_request->getParameter("drop_question_id"),
			$this->_request->getParameter("questionnaire_id")
		);

		$sql = "SELECT question_id, question_sequence ".
				"FROM {questionnaire_question} ".
				"WHERE (question_id = ? ".
				"OR question_id = ?) ".
				"AND questionnaire_id = ?";
		$questions = $this->_db->execute($sql, $params);
		if ($questions === false ||
			count($questions) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$questions[0]["question_id"]] = $questions[0]["question_sequence"];
		$sequences[$questions[1]["question_id"]] = $questions[1]["question_sequence"];

		return $sequences;
	}

	/**
	 * 回答済みかどうか判断する
	 *
     * @return boolean	true:回答済み、false:未回答
	 * @access	public
	 */
	function isAnswered()
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$userID = $session->getParameter("_user_id");
		if (empty($userID)) {
			return false;
		}

		$params = array(
			$this->_request->getParameter("questionnaire_id"),
			$userID,
			QUESTIONNAIRE_ANSWER_NONE_VALUE
		);
		$sql = "SELECT summary_id ".
					"FROM {questionnaire_summary} ".
					"WHERE questionnaire_id = ? ".
					"AND insert_user_id = ? ".
					"AND answer_flag != ?";

		$summaryIDs = $this->_db->execute($sql, $params, 1);
		if ($summaryIDs === false) {
        	$this->_db->addError();
			return false;
		}

		if (count($summaryIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * 回答一覧件数を取得する
	 *
     * @return array	回答一覧件数
	 * @access	public
	 */
	function &getSummaryCount()
	{
		$chiefItemShow = $this->_request->getParameter("chiefItemShow");

		$chiefWhere = "";
		if (!$chiefItemShow) {
			$chiefWhere = "AND insert_user_id = ?";
		}

		$params = array($this->_request->getParameter("questionnaire_id"));
		if (!$chiefItemShow) {
			$container =& DIContainerFactory::getContainer();
			$session =& $container->getComponent("Session");
			$params[] = $session->getParameter("_user_id");
		}

		$sql = "SELECT COUNT(summary_id) ".
				"FROM {questionnaire_summary} ".
				"WHERE questionnaire_id = ? ".
				$chiefWhere;
		$counts = $this->_db->execute($sql, $params, 1, null, false);
		if ($counts === false) {
			$this->_db->addError();
			return $counts;
		}

		return $counts[0][0];
	}

	/**
	 * 回答一覧データを取得する
	 *
     * @return array	回答一覧データ配列
	 * @access	public
	 */
	function &getSummaries()
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$mobile_flag = $session->getParameter("_mobile_flag");

		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "summary_id";
		}
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = ($mobile_flag==_ON ? "DESC" : "ASC");
		}
		$orderParams[$sortColumn] = $sortDirection;

		$chiefItemShow = $this->_request->getParameter("chiefItemShow");

		$chiefSelect = "";
		$chiefWhere = "";
		if ($chiefItemShow) {
			$chiefSelect = ", insert_user_id, insert_user_name ";
		} else {
			$chiefWhere = "AND insert_user_id = ? ";
		}

		$params = array($this->_request->getParameter("questionnaire_id"));
		if (!$chiefItemShow) {
			$container =& DIContainerFactory::getContainer();
			$session =& $container->getComponent("Session");
			$params[] = $session->getParameter("_user_id");
		}

		$sql = "SELECT summary_id, answer_flag, answer_number, answer_time ".
						$chiefSelect.
				"FROM {questionnaire_summary} ".
				"WHERE questionnaire_id = ? ".
				$chiefWhere.
				$this->_db->getOrderSQL($orderParams);

		$summaries = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makeSummaries"));
		if ($summaries === false) {
			$this->_db->addError();
		}

		//非会員回答処理
		if ($chiefItemShow) {
			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			foreach (array_keys($summaries) as $summari_id) {
				if (empty($summaries[$summari_id]["insert_user_name"])) {
					 $summaries[$summari_id]["insert_user_name"] = $smartyAssign->getLang("questionnaire_nonmember_answer");
				}
			}
		}

		return $summaries;
	}

	/**
	 * 回答一覧データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
     * @return string	回答一覧データ配列
	 * @access	public
	 */
	function &_makeSummaries(&$recordSet)
	{
		$summaries = array();
		while ($row = $recordSet->fetchRow()) {
			$summaryID = $row["summary_id"];
			$summaries[$summaryID] = $row;
		}

		return $summaries;
	}

	/**
	 * 集計情報用データ配列を取得する
	 *
     * @return string	集計情報用データ配列
	 * @access	public
	 */
	function &getAnswerSummary()
	{
		$params = array(
			$this->_request->getParameter("summary_id"),
			$this->_request->getParameter("questionnaire_id")
		);
		$sql = "SELECT summary_id, questionnaire_id, answer_number, ".
						"answer_time, insert_user_id, insert_user_name ".
					"FROM {questionnaire_summary} ".
					"WHERE summary_id = ? ".
					"AND questionnaire_id = ?";
		$summaries = $this->_db->execute($sql, $params);
		if ($summaries === false) {
			$this->_db->addError();
		}
		if (empty($summaries)) {
			return $summaries;
		}
		$summary = $summaries[0];

		//非会員回答処理
		if (empty($summary["insert_user_name"])) {
			$container =& DIContainerFactory::getContainer();
			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			$summary["insert_user_name"] = $smartyAssign->getLang("questionnaire_nonmember_answer");
		}

		return $summary;
	}

	/**
	 * 非会員回答データを取得する
	 *
     * @return string	非会員回答データ配列
	 * @access	public
	 */
	function &getNonmemberAnswer()
	{
		$questions = $this->getQuestions();
		if (empty($questions)) {
			return $questions;
		}

		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$request =& $container->getComponent("Request");
		$blockID = $request->getParameter("block_id");
		$answers = $session->getParameter("questionnaire_nonmember_answers". $blockID);

		foreach (array_keys($answers) as $questionID) {
			$answerQuestions[$questionID] = $questions[$questionID];
			unset($questions[$questionID]);

			if ($answerQuestions[$questionID]["question_type"] != QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
				$answers[$questionID]["answer_value"] = $this->getAnswerValues($answers[$questionID]["answer_value"], $answerQuestions[$questionID]["choices"]);
			}

			$answerQuestions[$questionID]["answer"] = $answers[$questionID];
		}

		return $answerQuestions;
	}

	/**
	 * 回答データ取得用のSQL文を取得する
	 *
	 * @param	string	$whereColumn	WHERE句カラム名称
     * @return string	回答データ取得用のSQL文
	 * @access	public
	 */
	function &_getAnswerSQL($whereColumn)
	{
		$sql = "SELECT answer_id, question_id, summary_id, answer_value ".
				"FROM {questionnaire_answer} ".
				"WHERE ". $whereColumn. " = ? ".
				"ORDER BY summary_id, question_id";

		return $sql;
	}

	/**
	 * 回答データを取得する
	 *
     * @return string	回答データ配列
	 * @access	public
	 */
	function &getAnswer()
	{
		$questions = $this->getQuestions();
		if (empty($questions)) {
			return $questions;
		}

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent('ActionChain');
		$actionName = $actionChain->getCurActionName();
		if ($actionName == 'questionnaire_view_main_confirm') {
			$session =& $container->getComponent('Session');
			$sessionKey = 'questionnaire_confirm' . $this->_request->getParameter('block_id');
			$confirmDatas = $session->getParameter($sessionKey);
			
			foreach (array_keys($questions) as $questionId) {
				$answer['answer_value'] = $confirmDatas['answer_value'][$questionId];
				if ($questions[$questionId]['question_type'] != QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
					$answer['answer_value'] = $this->getAnswerValues($answer['answer_value'],
																		$questions[$questionId]['choices']);
				}
				$questions[$questionId]['answer'] = $answer;
			}
			return $questions;
		}
		
		$params = array($this->_request->getParameter("summary_id"));
		$sql = $this->_getAnswerSQL("summary_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeQuestionAnswers"), $questions);
		if ($questions === false) {
			$this->_db->addError();
		}

		return $questions;
	}

	/**
	 * 回答配列を元に質問配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$questions	質問データ配列
	 * @return array	質問データ配列
	 * @access	private
	 */
	function &_makeQuestionAnswers(&$recordSet, &$questions)
	{
		$answerQuestions = $questions;
		while ($row = $recordSet->fetchRow()) {
			$questionID = $row["question_id"];
			if ($answerQuestions[$questionID]["question_type"] != QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
				$row["answer_value"] = $this->getAnswerValues($row["answer_value"], $answerQuestions[$questionID]["choices"]);
			}

			$answerQuestions[$questionID]["answer"] = $row;
		}

		return $answerQuestions;
	}

	/**
	 * 回答内容のチェックON、OFF配列を取得する
	 *
	 * @param	string	$answerValue	回答内容
	 * @param	array	$choices		選択肢配列
	 * @return array	チェックON、OFF配列
	 * @access	public
	 */
	function getAnswerValues($answerValue, $choices) {
		$answerChecks = explode("|", $answerValue);
		$answerValues = array();
		foreach (array_keys($choices) as $choiceID) {
			$index = count($answerValues);
			$answerValues[$choiceID] = $answerChecks[$index];
		}

		return $answerValues;
	}

	/**
	 * 質問IDの回答データを取得する
	 *
     * @return array	回答データ配列
	 * @access	public
	 */
	function &getQuestionaryAnswer()
	{
		$question = $this->_request->getParameter("question");
		$params = array($question["question_id"], $question["questionnaire_id"]);

		$sql = "SELECT A.answer_id, A.summary_id, A.answer_value, ".
						"S.answer_number, S.insert_user_id, S.insert_user_name ".
					"FROM {questionnaire_summary} S " .
					"LEFT JOIN {questionnaire_answer} A ".
					"ON (S.summary_id = A.summary_id AND A.question_id = ?) ".
					"WHERE S.questionnaire_id = ?";
		$answers = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeAnswers"), $question);
		if ($answers === false) {
			$answers->_db->addError();
		}

		//非会員回答処理
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		foreach (array_keys($answers) as $key) {
			if (empty($answers[$key]["insert_user_name"])) {
				 $answers[$key]["insert_user_name"] = $smartyAssign->getLang("questionnaire_nonmember_answer");
			}
		}

		return $answers;
	}

	/**
	 * 回答配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$question	質問データ
	 * @return array	回答データ配列
	 * @access	private
	 */
	function &_makeAnswers(&$recordSet, &$question)
	{
		$answers = array();
		while ($row = $recordSet->fetchRow()) {
			if (!empty($row["answer_value"]) && $question["question_type"] != QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
				$row["answer_value"] = $this->getAnswerValues($row["answer_value"], $question["choices"]);
			}

			$answers[] = $row;
		}

		return $answers;
	}

	/**
	 * 集計結果データを取得する
	 *
     * @return string	集計結果データ配列
	 * @access	public
	 */
	function &getTotal() {
		$params = array($this->_request->getParameter("questionnaire_id"));
		$sql = $this->_getQuestionSQL("questionnaire_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeQuestions"));
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}

		$sql = "SELECT MAX(choice_sequence) ".
				"FROM {questionnaire_choice} ".
				"WHERE questionnaire_id = ?";
		$sequences = $this->_db->execute($sql, $params, null, null, false);
		if ($sequences === false) {
			$this->_db->addError();
			return $sequences;
		}
		$maxChoiceCount = $sequences[0][0];
		if ($maxChoiceCount < 3) {
			$maxChoiceCount = 3;
		}

		$config = $this->getChoiceConfig();
		if ($config === false) {
        	return $config;
        }
        $choiceDefaults = $this->getDefaultChoiceLists($maxChoiceCount, $config["choice_label"], $config["graph_color"]);

		$sql = $this->_getChoiceSQL("questionnaire_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeTotal"), array($questions, $choiceDefaults));
		if ($questions === false) {
			$this->_db->addError();
		}

 		return $questions;
	}

	/**
	 * 集計データを質問データ配列に設定する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$params[0]	質問データ配列
	 * @param	array	$params[1]	デフォルト選択肢配列
	 * @return array	質問データ配列
	 * @access	private
	 */
	function &_makeTotal(&$recordSet, &$params)
	{
		$questions = $params[0];
		$choiceDefaults = $params[1];
		while ($row = $recordSet->fetchRow()) {
			$row = $this->_getShare($row);

			$questionID = $row["question_id"];
			if (empty($questions[$questionID]["choices"])) {
				$questions[$questionID]["choices"] = array();
			}

			$index = count($questions[$questionID]["choices"]);
			$row["label"] = $choiceDefaults[$index]["label"];

			$choiceID = $row["choice_id"];
			$questions[$questionID]["choices"][$choiceID] = $row;
		}

		return $questions;
	}

	/**
	 * 選択肢の選択率を集計データ配列に設定する
	 *
	 * @param	array	$row	集計データ配列
	 * @return array	集計データ配列
	 * @access	private
	 */
	function &_getShare(&$row)
	{
		$questionnaire = $this->_request->getParameter("questionnaire");
		if ($questionnaire["answer_count"] > 0) {
			$row["share"] = $row["choice_count"] / $questionnaire["answer_count"] * 100;
		} else {
			$row["share"] = 0;
		}
		$row["remain"] = 100 - $row["share"];

		return $row;
	}

	/**
	 * CSVデータを作成する
	 *
	 * @return boolean	true:正常、false:異常
	 * @access	public
	 */
	function setCSV()
	{
		$questions = $this->getQuestions();
		if ($questions === false) {
			return false;
		}

		$this->_request->setParameter("chiefItemShow", true);
		$summaries = $this->getSummaries();
		if ($summaries === false) {
			return false;
		}

		$params = array($this->_request->getParameter("questionnaire_id"));
		$sql = $this->_getAnswerSQL("questionnaire_id");
		$result = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeCSV"), array($questions, $summaries));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

 		return true;
	}

	/**
	 * CSVデータを設定する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$params[0]	質問データ配列
	 * @param	array	$params[1]	集計データ配列
	 * @return boolean	true:正常、false:異常
	 * @access	private
	 */
	function _makeCSV(&$recordSet, &$params)
	{

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		$csvMain =& $container->getComponent("csvMain");

		$data = array(
			$smartyAssign->getLang("questionnaire_answerer"),
			$smartyAssign->getLang("questionnaire_answer_date"),
			$smartyAssign->getLang("questionnaire_answer_number"),
			$smartyAssign->getLang("questionnaire_question_number"),
			$smartyAssign->getLang("questionnaire_question_title"),
			$smartyAssign->getLang("questionnaire_answer_title")
		);
		$csvMain->add($data);

		$questions = $params[0];
		$summaries = $params[1];
		$questionnaire = $this->_request->getParameter("questionnaire");
		$anonymity = $questionnaire["anonymity_flag"];
		while ($row = $recordSet->fetchRow()) {
			$questionID = $row["question_id"];
			$question = $questions[$questionID];
			$summaryID = $row["summary_id"];
			$summary = $summaries[$summaryID];

			if ($question["question_type"] != QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
				$choiceValues = array();
				$answerValues = $this->getAnswerValues($row["answer_value"], $question["choices"]);
				foreach (array_keys($answerValues) as $choiceID) {
					if ($answerValues[$choiceID] == _ON) {
						$choiceValues[] = $question["choices"][$choiceID]["choice_value"];
					}
				}
				$row["answer_value"] = implode(",", $choiceValues);
			}

			if ($anonymity == _ON) {
				$summary["insert_user_name"] = $smartyAssign->getLang("questionnaire_anonymity_name");
				$summary["answer_number"] = "";
			}

			$data = array(
				$summary["insert_user_name"],
				timezone_date_format($summary["answer_time"], _FULL_DATE_FORMAT),
				$summary["answer_number"],
				$question["question_sequence"],
				$question["question_value"],
				$row["answer_value"]
			);
			$csvMain->add($data);
		}

		return true;
	}

	/**
	 * メール送信データを取得する
	 *
	 * @param	string	$summary_id	集計ID
	 * @return array	メール送信データ配列
	 * @access	public
	 */
	function &getMail($summary_id)
	{
		$params[] = $summary_id;

		$sql = "SELECT S.summary_id, S.questionnaire_id, S.insert_user_name, S.answer_time, ".
						"Q.questionnaire_name, Q.mail_subject, Q.mail_body ".
				"FROM {questionnaire_summary} S ".
				"INNER JOIN {questionnaire} Q ".
				"ON S.questionnaire_id = Q.questionnaire_id ".
				"WHERE S.summary_id = ?";
		$mails = $this->_db->execute($sql, $params);
		if ($mails === false) {
			$this->_db->addError();
			return $mails;
		}

		return $mails[0];
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($block_id_arr)
	{
    	$sql = "SELECT questionnaire.*, block.block_id" .
    			" FROM {questionnaire} questionnaire" .
    			" INNER JOIN {questionnaire_block} block ON (questionnaire.questionnaire_id=block.questionnaire_id)" .
    			" WHERE block.block_id IN (".implode(",", $block_id_arr).")" .
    			" AND questionnaire.status <> ?" .
    			" ORDER BY block.insert_time DESC, block.questionnaire_id DESC";

        return $this->_db->execute($sql, array("status"=>QUESTIONNAIRE_STATUS_INACTIVE_VALUE));
	}

}
?>