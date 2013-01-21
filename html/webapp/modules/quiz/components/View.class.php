<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テストデータ取得コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Components_View
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
	function Quiz_Components_View()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * 小テストが配置されているブロックータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock()
	{
		$params = array($this->_request->getParameter("quiz_id"));
		$sql = "SELECT Q.room_id, B.block_id ".
				"FROM {quiz} Q ".
				"INNER JOIN {quiz_block} B ".
				"ON Q.quiz_id = B.quiz_id ".
				"WHERE Q.quiz_id = ? ".
				"ORDER BY B.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}

	/**
	 * 小テストが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function quizExists()
	{
		$params = array(
			$this->_request->getParameter("quiz_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT quiz_id ".
				"FROM {quiz} ".
				"WHERE quiz_id = ? ".
				"AND room_id = ?";
		$quizIDs = $this->_db->execute($sql, $params);
		if ($quizIDs === false) {
			$this->_db->addError();
			return $quizIDs;
		}

		if (count($quizIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDの小テスト件数を取得する
	 *
     * @return string	小テスト件数
	 * @access	public
	 */
	function getQuizCount()
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$count = $this->_db->countExecute("quiz", $params);

		return $count;
	}

	/**
	 * 現在配置されている小テストのIDを取得する
	 *
     * @return string	配置されている小テストID
	 * @access	public
	 */
	function &getCurrentQuizID()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT quiz_id ".
				"FROM {quiz_block} ".
				"WHERE block_id = ?";
		$quizIDs = $this->_db->execute($sql, $params);
		if ($quizIDs === false) {
			$this->_db->addError();
			return $quizIDs;
		}

		return $quizIDs[0]["quiz_id"];
	}

	/**
	 * 小テストの設定データを取得する
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
	 * 小テスト一覧データを取得する
	 *
     * @return array	小テスト一覧データ配列
	 * @access	public
	 */
	function &getQuizzes()
	{
		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "quiz_id";
		}
		$sortColumn = "Q.". $sortColumn;
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT Q.quiz_id, Q.quiz_name, Q.icon_name, Q.status, Q.nonmember_flag, Q.answer_count, ".
							"Q.insert_time, Q.insert_user_id, Q.insert_user_name, ".
							"COUNT(QS.question_id) AS question_count ".
				"FROM {quiz} Q ".
				"LEFT JOIN {quiz_question} QS ".
				"ON Q.quiz_id = QS.quiz_id ".
				"WHERE Q.room_id = ? ".
				"GROUP BY Q.quiz_id, Q.quiz_name, Q.icon_name, Q.status, ".
							"Q.insert_time, Q.insert_user_id, Q.insert_user_name ".
				$this->_db->getOrderSQL($orderParams);
		$quizzes = $this->_db->execute($sql, $params, $limit, $offset);
		if ($quizzes === false) {
			$this->_db->addError();
			return $quizzes;
		}

		return $quizzes;
	}

	/**
	 * 小テスト用デフォルトデータを取得する
	 *
     * @return array	小テスト用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultQuiz()
	{
		$config = $this->getConfig();
		if ($config === false) {
        	return $config;
        }

		$quiz = array(
			"old_use" => constant($config["old_use"]["conf_value"]),
			"status" => QUIZ_STATUS_INACTIVE_VALUE,
			"mail_send" => constant($config["mail_send"]["conf_value"]),
			"quiz_type" => constant($config["quiz_type"]["conf_value"]),
			"nonmember_flag" => constant($config["nonmember_flag"]["conf_value"]),
			"image_authentication" => constant($config["image_authentication"]["conf_value"]),
			"repeat_flag" => constant($config["repeat_flag"]["conf_value"]),
			"correct_flag" => constant($config["correct_flag"]["conf_value"]),
			"total_flag" => constant($config["total_flag"]["conf_value"])
		);

		return $quiz;
	}

	/**
	 * 小テストデータを取得する
	 *
     * @return array	小テストデータ配列
	 * @access	public
	 */
	function &getQuiz()
	{
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$session =& $container->getComponent("Session");
		$edit = $session->getParameter("quiz_edit". $this->_request->getParameter("block_id"));

		$sql = "SELECT quiz_id, quiz_name, icon_name, status";
		$format = "";
		if ($actionName == "quiz_view_edit_quiz_entry") {
			$sql .= ", quiz_type, period, ".
						"nonmember_flag, image_authentication, repeat_flag, correct_flag, total_flag, ".
						"perfect_score, answer_count, mail_send, mail_subject, mail_body ";
			$format = _INPUT_DATE_FORMAT;

		} elseif ($actionName == "quiz_view_edit_answer") {
			$sql .= ", answer_count ";

		} elseif ($actionName == "quiz_view_edit_reference") {
			$sql .= ", ". QUIZ_TYPE_LIST_VALUE. " AS quiz_type, period ";

		} elseif (strpos($actionName, "quiz_view_edit") === 0 ||
					strpos($actionName, "quiz_action_edit") === 0) {
			$sql .= " ";

		} elseif ($edit) {
			$sql .= ", quiz_type, period, ".
						"nonmember_flag, correct_flag, total_flag, ".
						"perfect_score, quiz_score, answer_count ";

		}

		$params = array($this->_request->getParameter("quiz_id"));
		$sql .= "FROM {quiz} ".
				"WHERE quiz_id = ?";

		$quizzes = $this->_db->execute($sql, $params, 1, null, true, array($this, "_makeQuiz"), $format);
		if ($quizzes === false) {
			$this->_db->addError();
			return $quizzes;
		}

		return $quizzes[0];
	}

	/**
	 * ルームIDの既存小テストデータを取得する
	 *
     * @return array	既存小テストデータ配列
	 * @access	public
	 */
	function &getOldQuizzes()
	{
		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT quiz_id, quiz_name ".
				"FROM {quiz} Q ".
				"WHERE room_id = ? ".
				"ORDER BY quiz_id DESC";

		$quizzes = $this->_db->execute($sql, $params);
		if ($quizzes === false) {
			$this->_db->addError();
		}

		return $quizzes;
	}

	/**
	 * 現在配置されている小テストデータを取得する
	 *
     * @return array	配置されている小テストデータ配列
	 * @access	public
	 */
	function &getCurrentQuiz()
	{
		$params = array(
			$this->_request->getParameter("block_id"),
			QUIZ_STATUS_INACTIVE_VALUE
		);

		$sql = "SELECT Q.quiz_id, Q.quiz_name, Q.icon_name, Q.status, Q.quiz_type, Q.period, ".
						"Q.nonmember_flag, Q.image_authentication, Q.repeat_flag, Q.correct_flag, Q.total_flag, ".
						"Q.perfect_score, Q.quiz_score, Q.answer_count, Q.mail_send ".
				"FROM {quiz_block} B ".
				"INNER JOIN {quiz} Q ".
				"ON B.quiz_id = Q.quiz_id ".
				"WHERE B.block_id = ? ".
				"AND Q.status != ?";
		$quizzes = $this->_db->execute($sql, $params, 1, null, true, array($this, "_makeQuiz"));
		if ($quizzes === false) {
			$this->_db->addError();
			return $quizzes;
		}

		return $quizzes[0];
	}

	/**
	 * 小テスト配列を作成する
	 * 期限の日時をフォーマットする
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @access	private
	 */
	function &_makeQuiz(&$recordSet, $format = _DATE_FORMAT)
	{
		$quizzes = array();
		while ($row = $recordSet->fetchRow()) {
			if (empty($row["period"])) {
				$quizzes[] = $row;
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

			$quizzes[] = $row;
		}

		return $quizzes;
	}

	/**
	 * 問題用デフォルトデータを取得する
	 *
     * @return array	問題用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultQuestion()
	{
		$config = $this->getConfig();
		if ($config === false) {
        	return $config;
        }

        $question = array(
        	"quiz_id" => $this->_request->getParameter("quiz_id"),
        	"question_type" => constant($config["question_type"]["conf_value"]),
        	"allotment" => $config["allotment"]["conf_value"],
        	"require_flag" => constant($config["require_flag"]["conf_value"])
        );

		$choiceCount = $config["choice_count"]["conf_value"];
		$choiceLabel = $config["choice_label"]["conf_value"];
		$graphColor = $config["graph_color"]["conf_value"];
		$question["choices"] = $this->getDefaultChoiceLists($choiceCount, $choiceLabel, $graphColor);
		$question["choice_words"] = $this->getDefaultWordLists($choiceCount, $choiceLabel);

		$params = array(
			"quiz_id" => $this->_request->getParameter("quiz_id")
		);
    	$question["question_sequence"] = $this->_db->countExecute("quiz_question", $params);
		if ($question["question_sequence"] === false) {
			return $question["question_sequence"];
		}
		$question["question_sequence"]++;

		return $question;
	}

	/**
	 * 問題データ取得用のSQL文を取得する
	 *
	 * @param	string	$whereColumn	WHERE句カラム名称
     * @return string	問題データ取得用のSQL文
	 * @access	public
	 */
	function &_getQuestionSQL($whereColumn)
	{
		$sql = "SELECT question_id, quiz_id, question_sequence, question_value";

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		switch($actionName) {
			case "quiz_view_edit_question_list":
				$sql .= " ";
				break;

			case "quiz_view_main_init":
			case "quiz_view_main_single":
			case "quiz_view_edit_reference":
				$sql .= ", question_type, allotment, require_flag ";
				break;

			default:
				$sql .= ", question_type, allotment, correct, require_flag, description ";
		}

		$sql .= "FROM {quiz_question} ".
				"WHERE ". $whereColumn. " = ? ".
				"ORDER BY question_sequence";

		return $sql;
	}

	/**
	 * 問題データ取得用のSQL文を取得する
	 *
	 * @param	string	$whereColumn	WHERE句カラム名称
     * @return string	問題データ取得用のSQL文
	 * @access	public
	 */
	function &_getChoiceSQL($whereColumn)
	{
		$sql = "SELECT choice_id, question_id, choice_value, choice_count, graph ".
					"FROM {quiz_choice} ".
					"WHERE ". $whereColumn. " = ? ".
					"ORDER BY question_id, choice_sequence";

		return $sql;
	}

	/**
	 * 問題データを取得する
	 *
	 * @param	array	$questionID	問題ID
     * @return array	問題データ配列
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
		if ($question["question_type"] == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
				$actionName != "quiz_view_edit_question_entry") {
			return $question;
		}

		$choiceConfig = $this->getChoiceConfig();
		if ($choiceConfig === false) {
        	return $choiceConfig;
        }

		if ($question["question_type"] == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
			$question["choices"] = $this->getDefaultChoiceLists($choiceConfig["choice_count"], $choiceConfig["choice_label"], $choiceConfig["graph_color"]);
			$question["choice_words"] = $this->getDefaultWordLists($choiceConfig["choice_count"], $choiceConfig["choice_label"]);
		} else {
			$questions = array($questionID => $question);
			$sql = $this->_getChoiceSQL("question_id");
			$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeChoices"), array($questions, $choiceConfig["choice_label"]));
			if ($questions === false) {
				$this->_db->addError();
				return $questions;
			}
			if ($question["question_type"] == QUIZ_QUESTION_TYPE_WORD_VALUE) {
				$question["choices"] = $this->getDefaultChoiceLists($choiceConfig["choice_count"], $choiceConfig["choice_label"], $choiceConfig["graph_color"]);
				$question["choice_words"] = $questions[$questionID]["choice_words"];
			} else {
				$question["choices"] = $questions[$questionID]["choices"];
				$question["choice_words"] = $this->getDefaultWordLists($choiceConfig["choice_count"], $choiceConfig["choice_label"]);
			}
		}

		return $question;
	}

	/**
	 * 小テストIDの全問題データを取得する
	 *
     * @return array	問題データ配列
	 * @access	public
	 */
	function &getQuestions()
	{
		$params = array($this->_request->getParameter("quiz_id"));
		$sql = $this->_getQuestionSQL("quiz_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeQuestions"));
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "quiz_view_edit_question_list") {
			return $questions;
		}

		$sql = $this->_getChoiceSQL("quiz_id");
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
	 * @param	array	$params[0]	問題データ配列
	 * @param	string	$params[1]	選択肢ラベル文字列
	 * @return array	問題データ配列
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

			if ($questions[$questionID]["question_type"] == QUIZ_QUESTION_TYPE_WORD_VALUE) {
				$column = "choice_words";
			} else {
				$column = "choices";
			}

			if (empty($questions[$questionID][$column])) {
				$questions[$questionID][$column] = array();
			}

			if (!empty($questions[$questionID]["correct"])) {
				$correctChecks = explode("|", $questions[$questionID]["correct"]);
				$index = count($questions[$questionID][$column]);
				$row["correct"] = $correctChecks[$index];
			}

			$choiceID = $row["choice_id"];
			$questions[$questionID][$column][$choiceID] = $row;
		}

		return $questions;
	}

	/**
	 * 問題データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	問題データ配列
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
	        if ($i == 0) {
	        	$choiceLists[$i]["correct"] = _ON;
	        }
		}

		return $choiceLists;
	}
	/**
	 * 正解一覧用用デフォルトデータを取得する
	 *
	 * @param	string	$choiceCount	選択肢数
	 * @param	string	$graphColor	選択肢ラベル文字列
	 * @param	string	$graphColor	グラフ色文字列
     * @return array	選択肢一覧用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultWordLists($choiceCount, $choiceLabel, $graphColor="")
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
	 * 小テストIDの全問題ID配列を取得する
	 *
     * @return array	問題ID配列
	 * @access	public
	 */
	function &getQuestionIDs()
	{
		$params = array($this->_request->getParameter("quiz_id"));
		$sql = "SELECT question_id ".
				"FROM {quiz_question} ".
				"WHERE quiz_id = ? ".
				"ORDER BY question_sequence";
		$questionIDs = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeQuestionIDs"));
		if ($questionIDs === false) {
			$this->_db->addError();
			return $questionIDs;
		}

		$quiz = $this->_request->getParameter("quiz");
		if ($quiz["quiz_type"] != QUIZ_TYPE_RANDOM_VALUE) {
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
	 * 問題ID配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	問題データ配列
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
	 * 問題番号データを取得する
	 *
     * @return array	問題番号データ配列
	 * @access	public
	 */
	function &getQuestionSequence()
	{
		$params = array(
			$this->_request->getParameter("drag_question_id"),
			$this->_request->getParameter("drop_question_id"),
			$this->_request->getParameter("quiz_id")
		);

		$sql = "SELECT question_id, question_sequence ".
				"FROM {quiz_question} ".
				"WHERE (question_id = ? ".
				"OR question_id = ?) ".
				"AND quiz_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false ||
			count($result) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$result[0]["question_id"]] = $result[0]["question_sequence"];
		$sequences[$result[1]["question_id"]] = $result[1]["question_sequence"];

		return $sequences;
	}

	/**
	 * 解答済みかどうか判断する
	 *
     * @return boolean	true:解答済み、false:未解答
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
			$this->_request->getParameter("quiz_id"),
			$userID,
			QUIZ_ANSWER_NONE_VALUE
		);
		$sql = "SELECT summary_id ".
					"FROM {quiz_summary} ".
					"WHERE quiz_id = ? ".
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
	 * 解答一覧件数を取得する
	 *
     * @return array	解答一覧件数
	 * @access	public
	 */
	function &getSummaryCount()
	{
		$chiefItemShow = $this->_request->getParameter("chiefItemShow");

		$chiefWhere = "";
		if (!$chiefItemShow) {
			$chiefWhere = "AND insert_user_id = ?";
		}

		$params = array($this->_request->getParameter("quiz_id"));
		if (!$chiefItemShow) {
			$container =& DIContainerFactory::getContainer();
			$session =& $container->getComponent("Session");
			$params[] = $session->getParameter("_user_id");
		}

		$sql = "SELECT COUNT(summary_id) ".
				"FROM {quiz_summary} ".
				"WHERE quiz_id = ? ".
				$chiefWhere;
		$counts = $this->_db->execute($sql, $params, 1, null, false);
		if ($counts === false) {
			$this->_db->addError();
			return $counts;
		}

		return $counts[0][0];
	}

	/**
	 * 解答一覧データを取得する
	 *
	 * @param	array	$statistics	統計データ配列
     * @return array	解答一覧データ配列
	 * @access	public
	 */
	function &getSummaries($statistics = null)
	{
		if (empty($statistics)) {
			$statistics = $this->getStatistics();
		}
		if (empty($statistics)) {
			return $statistics;
		}

		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "summary_id";
		}
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$container =& DIContainerFactory::getContainer();
			$session =& $container->getComponent("Session");
			$sortDirection = ($session->getParameter("_mobile_flag")==_ON ? "DESC" : "ASC");
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

		$params = array($this->_request->getParameter("quiz_id"));
		if (!$chiefItemShow) {
			$container =& DIContainerFactory::getContainer();
			$session =& $container->getComponent("Session");
			$params[] = $session->getParameter("_user_id");
		}

		$sql = "SELECT summary_id, answer_flag, answer_number, summary_score, answer_time ".
						$chiefSelect.
				"FROM {quiz_summary} ".
				"WHERE quiz_id = ? ".
				$chiefWhere.
				$this->_db->getOrderSQL($orderParams);

		$summaries = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makeSummaries"), $statistics);
		if ($summaries === false) {
			$this->_db->addError();
			return $summaries;
		}

		//非会員回答処理
		if ($chiefItemShow) {
			$container =& DIContainerFactory::getContainer();
			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			foreach (array_keys($summaries) as $summari_id) {
				if (empty($summaries[$summari_id]["insert_user_name"])) {
					 $summaries[$summari_id]["insert_user_name"] = $smartyAssign->getLang("quiz_nonmember_answer");
				}
			}
		}

		return $summaries;
	}

	/**
	 * 解答一覧データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$statistics	統計データ配列
     * @return string	解答一覧データ配列
	 * @access	public
	 */
	function &_makeSummaries(&$recordSet, &$statistics)
	{
		$summaries = array();
		while ($row = $recordSet->fetchRow()) {
			$row["deviation"] = $this->_getDeviation($row["summary_score"], $statistics[0], $statistics[1]);
			$summaryID = $row["summary_id"];
			$summaries[$summaryID] = $row;
		}

		return $summaries;
	}

	/**
	 * 統計データを取得する
	 *
     * @param	array	$roomIDs	対象ルームID配列（quiz_view_admin_personalinfアクションの場合のみ）
     * @return string	統計データ配列
	 * @access	public
	 */
	function &getStatistics($roomIDs = null) {
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($actionName == "quiz_view_admin_personalinf") {
			$params = array();
			$sql = "SELECT quiz_id, AVG(summary_score), STDDEV(summary_score) ".
						"FROM {quiz_summary} ".
						"WHERE room_id IN (". implode(",", $roomIDs). ") ".
						"GROUP BY quiz_id";
			$statistics = $this->_db->execute($sql, $params, null, null, false, array($this, "_makeStatistics"));
			if ($statistics === false) {
				$this->_db->addError();
				return $statistics;
			}

			return $statistics;

		} else {
			$params = array($this->_request->getParameter("quiz_id"));
			$sql = "SELECT AVG(summary_score), STDDEV(summary_score) ".
						"FROM {quiz_summary} ".
						"WHERE quiz_id = ?";
			$statistics = $this->_db->execute($sql, $params, 1, null, false);
			if ($statistics === false) {
				$this->_db->addError();
				return $statistics;
			}

			return $statistics[0];
		}
	}

	/**
	 * 統計データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
     * @return string	解答一覧データ配列
	 * @access	public
	 */
	function &_makeStatistics(&$recordSet)
	{
		$statistics = array();
		while ($row = $recordSet->fetchRow()) {
			$quizID = $row[0];
			$statistics[$quizID][0] = $row[1];
			$statistics[$quizID][1] = $row[2];
		}

		return $statistics;
	}

	/**
	 * 偏差値を取得する
	 *
	 * @param	string	$summary_score	合計点
	 * @param	string	$average		平均点
	 * @param	string	$stddev			標準偏差
     * @return string	問題データ配列
	 * @access	public
	 */
	function &_getDeviation($summary_score, $average, $stddev) {
		if ($stddev == 0) {
			$deviation = 50;
		} else {
			$deviation = ((10 * ($summary_score - $average)) / $stddev) + 50;
		}

		return $deviation;
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
			$this->_request->getParameter("quiz_id")
		);
		$sql = "SELECT summary_id, quiz_id, answer_number, summary_score, ".
						"answer_time, insert_user_id, insert_user_name ".
					"FROM {quiz_summary} ".
					"WHERE summary_id = ? ".
					"AND quiz_id = ?";
		$summaries = $this->_db->execute($sql, $params);
		if ($summaries === false) {
			$this->_db->addError();
		}
		if (empty($summaries)) {
			return $summaries;
		}
		$summary = $summaries[0];

		$statistics = $this->getStatistics();
		if (empty($statistics)) {
			return $statistics;
		}
		list($summary["average"], $stddev) = $statistics;

		$summary["deviation"] = $this->_getDeviation($summary["summary_score"], $summary["average"], $stddev);

		//非会員回答処理
		if (empty($summary["insert_user_name"])) {
			$container =& DIContainerFactory::getContainer();
			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			$summary["insert_user_name"] = $smartyAssign->getLang("quiz_nonmember_answer");
		}

		return $summary;
	}

	/**
	 * 非会員解答データを取得する
	 *
     * @return string	非会員解答データ配列
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
		$answers = $session->getParameter("quiz_nonmember_answers". $blockID);

		foreach (array_keys($answers) as $questionID) {
			$answerQuestions[$questionID] = $questions[$questionID];
			unset($questions[$questionID]);

			if ($answerQuestions[$questionID]["question_type"] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
				$answerQuestions[$questionID]["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE) {

				$answers[$questionID]["answer_value"] = $this->getAnswerValues($answers[$questionID]["answer_value"], $answerQuestions[$questionID]["choices"]);
			}

			$answerQuestions[$questionID]["answer"] = $answers[$questionID];
		}

		return $answerQuestions;
	}

	/**
	 * 解答データ取得用のSQL文を取得する
	 *
	 * @param	string	$whereColumn	WHERE句カラム名称
     * @return string	解答データ取得用のSQL文
	 * @access	public
	 */
	function &_getAnswerSQL($whereColumn)
	{
		$sql = "SELECT answer_id, question_id, summary_id, answer_value, answer_flag, score ".
				"FROM {quiz_answer} ".
				"WHERE ". $whereColumn. " = ? ".
				"ORDER BY summary_id, question_id";

		return $sql;
	}

	/**
	 * 解答データを取得する
	 *
     * @return string	解答データ配列
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
		if ($actionName == 'quiz_view_main_confirm') {
			$session =& $container->getComponent('Session');
			$sessionKey = 'quiz_confirm' . $this->_request->getParameter('block_id');
			$confirmDatas = $session->getParameter($sessionKey);

			foreach (array_keys($questions) as $questionId) {
				$answer['answer_value'] = $confirmDatas['answer_value'][$questionId];
				if ($questions[$questionId]['question_type'] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
					$questions[$questionId]["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE) {

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
	 * 解答配列を元に問題配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$questions	問題データ配列
	 * @return array	問題データ配列
	 * @access	private
	 */
	function &_makeQuestionAnswers(&$recordSet, &$questions)
	{
		$answerQuestions = $questions;
		while ($row = $recordSet->fetchRow()) {
			$questionID = $row["question_id"];
			if ($answerQuestions[$questionID]["question_type"] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
				$answerQuestions[$questionID]["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE) {

				$row["answer_value"] = $this->getAnswerValues($row["answer_value"], $answerQuestions[$questionID]["choices"]);
			}

			$answerQuestions[$questionID]["answer"] = $row;
		}

		return $answerQuestions;
	}

	/**
	 * 解答内容のチェックON、OFF配列を取得する
	 *
	 * @param	string	$answerValue	解答内容
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
	 * 配点データを取得する
	 *
     * @return string	配点データ配列
	 * @access	public
	 */
	function &getAllotment()
	{
        $answerIDs = array_keys($this->_request->getParameter("answer_flag"));
        if (empty($answerIDs)) {
        	return $answerIDs;
        }

		$params = array($this->_request->getParameter("quiz_id"));
		$sql = "SELECT A.answer_id, A.question_id, A.summary_id, Q.allotment, Q.question_sequence ".
					"FROM {quiz_answer} A ".
					"INNER JOIN {quiz_question} Q ".
					"ON A.question_id = Q.question_id ".
					"WHERE A.quiz_id = ? ".
					"AND A.answer_id IN (". implode(",", $answerIDs). ")";

		$allotments = $this->_db->execute($sql, $params);
		if ($allotments === false) {
			$this->_db->addError();
		}

		return $allotments;
	}

	/**
	 * 問題IDの解答データを取得する
	 *
     * @return array	解答データ配列
	 * @access	public
	 */
	function &getQuestionaryAnswer()
	{
		$question = $this->_request->getParameter("question");
		$params = array($question["question_id"], $question["quiz_id"]);

		$sql = "SELECT A.answer_id, A.summary_id, A.answer_value, A.answer_flag, A.score, ".
						"S.answer_number, S.insert_user_id, S.insert_user_name ".
					"FROM {quiz_summary} S ".
					"LEFT JOIN {quiz_answer} A ".
					"ON (S.summary_id = A.summary_id AND A.question_id = ?) ".
					"WHERE S.quiz_id = ?";
		$answers = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeAnswers"), $question);
		if ($answers === false) {
			$this->_db->addError();
		}

		//非会員回答処理
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		foreach (array_keys($answers) as $key) {
			if (empty($answers[$key]["insert_user_name"])) {
				 $answers[$key]["insert_user_name"] = $smartyAssign->getLang("quiz_nonmember_answer");
			}
		}

		return $answers;
	}

	/**
	 * 解答配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$question	問題データ
	 * @return array	解答データ配列
	 * @access	private
	 */
	function &_makeAnswers(&$recordSet, &$question)
	{
		$answers = array();
		while ($row = $recordSet->fetchRow()) {
			if (!empty($row["answer_value"]) && $question["question_type"] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
				!empty($row["answer_value"]) && $question["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE) {

				$row["answer_value"] = $this->getAnswerValues($row["answer_value"], $question["choices"]);
			}

			$answers[] = $row;
		}

		return $answers;
	}

	/**
	 * 問題IDより解答状態毎の件数を取得する
	 *
     * @return array	未解答者数、正解者数の配列
	 * @access	public
	 */
	function &getAnswerFlagCount()
	{
		$params = array($this->_request->getParameter("question_id"));
		$sql = "SELECT answer_flag, COUNT(answer_id) ".
				"FROM {quiz_answer} ".
				"WHERE question_id = ? ".
				"GROUP BY answer_flag ".
				"ORDER BY answer_flag";
		$result = $this->_db->execute($sql, $params, null, null, false, array($this, "_makeAnswerFlags"));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result;
	}

	/**
	 * 解答状態毎を件数配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	解答データ配列
	 * @access	private
	 */
	function &_makeAnswerFlags(&$recordSet)
	{
		$answerFlags = array();
		while ($row = $recordSet->fetchRow()) {
			$answerFlag = $row[0];
			$answerFlags[$answerFlag] = $row[1];
		}

		return $answerFlags;
	}

	/**
	 * 集計結果データを取得する
	 *
     * @return string	集計結果データ配列
	 * @access	public
	 */
	function &getTotal() {
		$params = array($this->_request->getParameter("quiz_id"));
		$sql = $this->_getQuestionSQL("quiz_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeQuestions"));
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}

		$sql = "SELECT MAX(choice_sequence) ".
				"FROM {quiz_choice} ".
				"WHERE quiz_id = ?";
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

		$sql = $this->_getChoiceSQL("quiz_id");
		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeTotal"), array($questions, $choiceDefaults));
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}

		$params[] = QUIZ_QUESTION_TYPE_TEXTAREA_VALUE;
		$params[] = QUIZ_QUESTION_TYPE_WORD_VALUE;
		$sql = "SELECT A.question_id, A.answer_flag, COUNT(A.answer_id) AS choice_count ".
				"FROM {quiz_question} Q ".
				"LEFT JOIN {quiz_answer} A ".
				"ON Q.question_id = A.question_id ".
				"WHERE A.quiz_id = ? ".
				"AND (Q.question_type = ? OR Q.question_type = ?) ".
				"GROUP BY A.question_id, A.answer_flag ".
				"ORDER BY A.question_id, A.answer_flag";

		$questions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeTextareaTotal"), array($questions, $choiceDefaults));
		if ($questions === false) {
			$this->_db->addError();
			return $questions;
		}

 		return $questions;
	}

	/**
	 * 集計データを問題データ配列に設定する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$params[0]	問題データ配列
	 * @param	array	$params[1]	デフォルト選択肢配列
	 * @return array	問題データ配列
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
	 * 集計データを問題データ配列に設定する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$params[0]	問題データ配列
	 * @param	array	$params[1]	デフォルト選択肢配列
	 * @return array	問題データ配列
	 * @access	private
	 */
	function &_makeTextareaTotal(&$recordSet, &$params)
	{
		$questions = $params[0];
		$choiceDefaults = $params[1];

		foreach (array_keys($questions) as $questionID) {
			if ($questions[$questionID]["question_type"] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
				$questions[$questionID]["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE) {
				continue;
			}
			if ($questions[$questionID]["question_type"] == QUIZ_QUESTION_TYPE_WORD_VALUE) {
				$questions[$questionID]["choices"] = array();
			}

			$total["graph"] = $choiceDefaults[0]["graph"];
			$total["label"] = $choiceDefaults[0]["label"];
			$total["share"] = 0;
			$total["remain"] = 100;
			$questions[$questionID]["choices"][QUIZ_ANSWER_NOT_MARK_VALUE] = $total;

			$total["graph"] = $choiceDefaults[1]["graph"];
			$total["label"] = $choiceDefaults[1]["label"];
			$questions[$questionID]["choices"][QUIZ_ANSWER_CORRECT_VALUE] = $total;

			$total["graph"] = $choiceDefaults[2]["graph"];
			$total["label"] = $choiceDefaults[2]["label"];
			$questions[$questionID]["choices"][QUIZ_ANSWER_WRONG_VALUE] = $total;
		}

		while ($row = $recordSet->fetchRow()) {
			$row = $this->_getShare($row);

			$questionID = $row["question_id"];
			$answerFlag = $row["answer_flag"];
			$questions[$questionID]["choices"][$answerFlag] = array_merge($questions[$questionID]["choices"][$answerFlag], $row);
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
		$quiz = $this->_request->getParameter("quiz");
		if ($quiz["answer_count"] > 0) {
			$row["share"] = $row["choice_count"] / $quiz["answer_count"] * 100;
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

		$params = array($this->_request->getParameter("quiz_id"));
		$sql = $this->_getAnswerSQL("quiz_id");
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
	 * @param	array	$params[0]	問題データ配列
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
			$smartyAssign->getLang("quiz_answerer"),
			$smartyAssign->getLang("quiz_answer_date"),
			$smartyAssign->getLang("quiz_answer_number"),
			$smartyAssign->getLang("quiz_deviation"),
			$smartyAssign->getLang("quiz_question_number"),
			$smartyAssign->getLang("quiz_question_title"),
			$smartyAssign->getLang("quiz_answer_title"),
			$smartyAssign->getLang("quiz_score"),
			$smartyAssign->getLang("quiz_allotment")
		);
		$csvMain->add($data);

		$questions = $params[0];
		$summaries = $params[1];
		while ($row = $recordSet->fetchRow()) {
			$questionID = $row["question_id"];
			$question = $questions[$questionID];
			$summaryID = $row["summary_id"];
			$summary = $summaries[$summaryID];

			if ($question["question_type"] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
				$question["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE) {

				$choiceValues = array();
				$answerValues = $this->getAnswerValues($row["answer_value"], $question["choices"]);
				foreach (array_keys($answerValues) as $choiceID) {
					if ($answerValues[$choiceID] == _ON) {
						$choiceValues[] = $question["choices"][$choiceID]["choice_value"];
					}
				}
				$row["answer_value"] = implode(",", $choiceValues);
			}

			$data = array(
				$summary["insert_user_name"],
				timezone_date_format($summary["answer_time"], _FULL_DATE_FORMAT),
				$summary["answer_number"],
				sprintf($smartyAssign->getLang("quiz_deviation_value"), $summary["deviation"]),
				$question["question_sequence"],
				$question["question_value"],
				$row["answer_value"],
				$row["score"],
				$question["allotment"]
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
		$params = array($summary_id	);
		$sql = "SELECT S.summary_id, S.quiz_id, S.insert_user_name, S.answer_time, ".
						"Q.quiz_name, Q.mail_subject, Q.mail_body ".
				"FROM {quiz_summary} S ".
				"INNER JOIN {quiz} Q ".
				"ON S.quiz_id = Q.quiz_id ".
				"WHERE S.summary_id = ?";
		$mails = $this->_db->execute($sql, $params);
		if ($mails === false) {
			$this->_db->addError();
			return $mails;
		}

		return $mails[0];
	}

	/**
	 * 個人情報データを取得する
	 *
	 * @return array	個人情報データ配列
	 * @access	public
	 */
	function &getPersonalSummaries()
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
    	$ownUserID = $session->getParameter("_user_id");
    	$otherUserID = $this->_request->getParameter("user_id");

		$pagesView =& $container->getComponent("pagesView");
		if ($ownUserID != $otherUserID) {
			$ownRoomIDs = $pagesView->getRoomIdByUserId($ownUserID);
	    	$otherRoomIDs = $pagesView->getRoomIdByUserId($otherUserID, _AUTH_GUEST);
			$roomIDs = array_intersect($otherRoomIDs, $ownRoomIDs);
		} else {
			$roomIDs =  $pagesView->getRoomIdByUserId($ownUserID, _AUTH_GUEST);
		}
		if (empty($roomIDs)) {
			return $roomIDs;
		}

		$params = array($this->_request->getParameter("user_id"));
		$sql = "SELECT Q.quiz_id, Q.room_id, Q.quiz_name, Q.perfect_score, ".
						"S.summary_id, S.answer_flag, S.answer_number, S.summary_score, S.answer_time, ".
						"P.page_name ".
				"FROM {quiz} Q ".
				"LEFT JOIN {quiz_summary} S ".
				"ON Q.quiz_id = S.quiz_id ".
				"AND S.insert_user_id = ? ".
				"INNER JOIN {pages} P ".
				"ON Q.room_id = P.page_id ".
				"WHERE Q.room_id IN (". implode(",", $roomIDs). ") ".
				"ORDER BY Q.room_id, Q.quiz_id DESC, S.answer_number";
		$personalSummaries = $this->_db->execute($sql, $params, null, null, true, array($this, "_makePersonalSummaries"), $roomIDs);
		if ($personalSummaries === false) {
			$this->_db->addError();
			return $personalSummaries;
		}

		return $personalSummaries;
	}

	/**
	 * 解答一覧データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$roomIDs	対象ルームID配列
     * @return string	解答一覧データ配列
	 * @access	public
	 */
	function &_makePersonalSummaries(&$recordSet, $roomIDs)
	{
		$statistics = $this->getStatistics($roomIDs);
		if (empty($statistics)) {
			return $statistics;
		}

		$roomIDKeys = array_flip($roomIDs);
		$oldQuizID = "";
		while ($row = $recordSet->fetchRow()) {
			$quizID = $row["quiz_id"];
			if (!empty($statistics[$quizID])) {
				$row["deviation"] = $this->_getDeviation($row["summary_score"], $statistics[$quizID][0], $statistics[$quizID][1]);
			}

			$roomID = $row["room_id"];
			if (!is_array($roomIDKeys[$roomID])) {
				$roomIDKeys[$roomID] = array();
			} else {
				$row["page_name"] = "";

				if ($oldQuizID == $quizID) {
					$row["quiz_name"] = "";
				}
			}

			$oldQuizID = $quizID;
			$roomIDKeys[$roomID][] = $row;
		}

		$personalSummaries = array();
		foreach (array_keys($roomIDKeys) as $roomID) {
			if (is_array($roomIDKeys[$roomID])) {
				$personalSummaries = array_merge($personalSummaries, $roomIDKeys[$roomID]);
			}
		}

		return $personalSummaries;
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($block_id_arr)
	{
    	$sql = "SELECT quiz.*, block.block_id" .
    			" FROM {quiz} quiz" .
    			" INNER JOIN {quiz_block} block ON (quiz.quiz_id=block.quiz_id)" .
    			" WHERE block.block_id IN (".implode(",", $block_id_arr).")" .
    			" AND quiz.status <> ?" .
    			" ORDER BY block.insert_time DESC, block.quiz_id DESC";

        return $this->_db->execute($sql, array("status"=>QUIZ_STATUS_INACTIVE_VALUE));
	}

    /**
	 * 文字の名寄せ
	 * 　
	 * @return string dirname
     * @access  public
	 */
	function getSynonym($str)
	{
		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$escapeText =& $commonMain->registerClass(WEBAPP_DIR.'/components/escape/Text.class.php', "Escape_Text", "escapeText");

		$_result = $escapeText->convertSynonym($str);
		return $_result;
	}

}
?>