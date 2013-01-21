<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Components_View
{
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

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
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Assignment_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_session =& $this->_container->getComponent("Session");
	}

	/**
	 * 評価項目データを取得する
	 *
     * @return array	評価項目データ配列
	 * @access	public
	 */
	function &getGradeValues()
	{
		$params = array(
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT grade_value".
				" FROM {assignment_grade_value}" .
				" WHERE room_id = ?" .
				" AND grade_value != ''" .
				" ORDER BY display_sequence";

		$gradeValues = $this->_db->execute($sql, $params, null, null, true);
		if ($gradeValues === false) {
			$this->_db->addError();
		}

		return $gradeValues;
	}

	/**
	 * 評価値データを取得する(デフォルト)
	 *
     * @return array	評価値データ配列
	 * @access	public
	 */
	function &getDefaultGradeValues()
	{
		$gradeValues = array(
			0 => array("grade_value" => ASSIGNMENT_GRADE_A),
			1 => array("grade_value" => ASSIGNMENT_GRADE_B),
			2 => array("grade_value" => ASSIGNMENT_GRADE_C),
			3 => array("grade_value" => ASSIGNMENT_GRADE_D)
		);
		return $gradeValues;
	}

	/**
	 * 期限切れ課題データIDを取得する
	 *
     * @return array	課題データ配列
	 * @access	public
	 */
	function existsPeriodOver()
	{
		$params = array(
			"period" => timezone_date()
		);

		$sql = "SELECT assignment_id" .
				" FROM {assignment}" .
				" WHERE period != ''" .
				" AND activity = " . _ON .
				" AND period < ?";

		$assignments = $this->_db->execute($sql, $params, null, null, true);
		if ($assignments === false) {
			$this->_db->addError();
			return $assignments;
		}

		if (count($assignments) > 0) {
			return true;
		}
		return false;
	}

	/**
	 * ルームIDの課題件数を取得する
	 *
     * @return string	課題件数
	 * @access	public
	 */
	function getAssignmentCount()
	{
 		$params = array(
			"room_id" => $this->_request->getParameter("room_id")
		);
    	$count = $this->_db->countExecute("assignment", $params);

		return $count;
	}

	/**
	 * 課題一覧データを取得する
	 *
     * @return array	課題一覧データ配列
	 * @access	public
	 */
	function &getAssignments()
	{
		$sort_col = $this->_request->getParameter("sort_col");
		if (empty($sort_col)) {
			$sort_col = "Assign.assignment_id";
		}
		$sort_dir = $this->_request->getParameter("sort_dir");
		if (empty($sort_dir)) {
			$sort_dir = "DESC";
		}
		$order[$sort_col] = $sort_dir;

		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$params = array(
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT Assign.*, COUNT(Submitter.submit_id) AS submit_count".
				" FROM {assignment} Assign".
				" LEFT JOIN {assignment_submitter} Submitter" .
					" ON (Assign.assignment_id = Submitter.assignment_id)".
				" WHERE Assign.room_id = ?" .
				" GROUP BY Assign.assignment_id".
				" ".$this->_db->getOrderSQL($order);

		$assignments = $this->_db->execute($sql, $params, $limit, $offset, true);
		if ($assignments === false) {
			$this->_db->addError();
		}

		return $assignments;
	}

	/**
	 * 課題データを取得する
	 *
     * @return array	課題データ配列
	 * @access	public
	 */
	function &getAssignment()
	{
		$params = array(
			"assignment_id" => $this->_request->getParameter("assignment_id"),
			"room_id" => $this->_request->getParameter("room_id")
		);

		$sql = "SELECT Assign.*, ABody.body AS assignment_body, AMail.*" .
				" FROM {assignment} Assign" .
				" INNER JOIN {assignment_body} ABody" .
					" ON (Assign.body_id = ABody.body_id)".
				" INNER JOIN {assignment_mail} AMail" .
					" ON (Assign.assignment_id = AMail.assignment_id)".
				" WHERE Assign.assignment_id = ?".
				" AND Assign.room_id = ?";

		$assignment = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeAssignmentArray"));
		if ($assignment === false) {
			$this->_db->addError();
			return $assignment;
		}

		return $assignment;
	}

	/**
	 * 課題データ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_makeAssignmentArray(&$recordSet)
	{
		if ($row = $recordSet->fetchRow()) {
			$this->_makePeriod($row);
		}
		return $row;
	}

	/**
	 * 課題データ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function _makePeriod(&$assignment)
	{
		static $soon_period = null;
		static $thisDate = null;
		static $soonDate = null;

		if (!isset($soon_period)) {
			$soon_period = $this->_getSoonPeriod();
			$thisDate = timezone_date_format(null, null);
			$soonDate = date("YmdHis", mktime(0, 0, 0,
								intval(substr($thisDate, 4, 2)),
								intval(substr($thisDate, 6, 2)) + $soon_period,
								intval(substr($thisDate, 0, 4))));
		}

		if (!empty($assignment["period"])) {
			$period = timezone_date_format($assignment["period"], null);
			if (substr($period, 8) == "000000") {
				$previousDay = -1;
				$timeFormat = "";
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
			$assignment["display_period_date"] = date(_DATE_FORMAT, $date);
			$assignment["display_period_time"] = date($timeFormat, $date);

			$assignment["input_period"] = date(_INPUT_DATE_FORMAT, $date);

			if ($assignment["activity"] == _ON) {
				if ($thisDate > $period) {
					$assignment["period_class_name"] = ASSIGNMENT_PERIOD_CLASS_NAME_OVER;
				} elseif ($soonDate >= $period) {
					$assignment["period_class_name"] = ASSIGNMENT_PERIOD_CLASS_NAME_SOON;
				} else {
					$assignment["period_class_name"] = "";
				}
			}
		}
		return true;
	}

	/**
	 * 課題が存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function assignmentExists()
	{
		$params = array(
			$this->_request->getParameter("assignment_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT assignment_id".
				" FROM {assignment}".
				" WHERE assignment_id = ?".
				" AND room_id = ?";
		$assignmentIDs = $this->_db->execute($sql, $params);
		if ($assignmentIDs === false) {
			$this->_db->addError();
			return $assignmentIDs;
		}

		if (count($assignmentIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * 最終更新日を取得する
	 *
     * @return array	課題データ配列
	 * @access	public
	 */
	function &getResponseLastTime()
	{
		$params = array(
			"assignment_id" => $this->_request->getParameter("assignment_id")
		);

		$sql = "SELECT COUNT(*) AS submit_summary_count, " .
					"MAX(update_time) AS response_last_update_time" .
				" FROM {assignment_submitter}".
				" WHERE assignment_id = ?" .
				" AND submit_flag != ". ASSIGNMENT_SUBMIT_FLAG_YET_REREASED ;

		$summaries = $this->_db->execute($sql, $params);

		if ($summaries === false) {
			$this->_db->addError();
		}
		if (empty($summaries)) {
			return $summaries;
		}

		return $summaries[0];
	}

	/**
	 * 解答が存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function submitterExists()
	{
		$params = array(
			$this->_request->getParameter("assignment_id"),
			$this->_request->getParameter("submit_user_id")
		);
		$sql = "SELECT submit_id".
				" FROM {assignment_submitter}".
				" WHERE assignment_id = ?".
				" AND insert_user_id = ?";
		$submitIDs = $this->_db->execute($sql, $params);
		if ($submitIDs === false) {
			$this->_db->addError();
			return $submitIDs;
		}

		if (count($submitIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * 最新の解答IDを取得
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function getNewestReportID()
	{
		$params = array(
			$this->_request->getParameter("assignment_id"),
			$this->_request->getParameter("submit_user_id")
		);
		$sql = "SELECT Report.report_id".
				" FROM {assignment_submitter} Submitter".
				" INNER JOIN {assignment_report} Report " .
					"ON (Submitter.submit_id = Report.submit_id)".
				" WHERE Submitter.assignment_id = ?".
				" AND Submitter.insert_user_id = ?" .
				" ORDER BY Report.update_time DESC";
		$reportIDs = $this->_db->execute($sql, $params, 1, 0);
		if ($reportIDs === false) {
			$this->_db->addError();
			return $reportIDs;
		}

		return $reportIDs[0]["report_id"];
	}

	/**
	 * レポート(or 返信)データを取得する
	 *
     * @return array	課題データ配列
	 * @access	public
	 */
	function &getReports()
	{
		$params = array(
			$this->_request->getParameter("assignment_id"),
			$this->_request->getParameter("submit_user_id")
		);
		$sql = "SELECT ".$this->_getReportSelectColumSQL().
				" FROM {assignment_report} Report " .
				" INNER JOIN {assignment_body} RBody" .
					" ON (Report.body_id = RBody.body_id)".
				" INNER JOIN {assignment_submitter} Submitter" .
					" ON (Report.submit_id = Submitter.submit_id)".
				$this->_getAuthorityFromSQL().
				" WHERE Report.assignment_id = ?".
				" AND Submitter.insert_user_id = ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY Report.insert_time DESC";

		$reports = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeReportArray"));
		if ($reports === false) {
			$this->_db->addError();
			return $reports;
		}

		return $reports;
	}

	/**
	 * レポート(or 返信)データを取得する
	 *
     * @return array	課題データ配列
	 * @access	public
	 */
	function &getReport($report_id)
	{
		$params = array(
			$report_id
		);
		$sql = "SELECT ".$this->_getReportSelectColumSQL().
				" FROM {assignment_report} Report " .
				" INNER JOIN {assignment_body} RBody" .
					" ON (Report.body_id = RBody.body_id)".
				" INNER JOIN {assignment_submitter} Submitter" .
					" ON (Report.submit_id = Submitter.submit_id)".
				$this->_getAuthorityFromSQL().
				" WHERE Report.report_id = ? ".
				$this->_getAuthorityWhereSQL($params);

		$reports = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeReportArray"));
		if ($reports === false) {
			$this->_db->addError();
			return $reports;
		}

		return $reports[$report_id];
	}

	/**
	 * 解答者データを取得する
	 *
     * @return array	課題データ配列
	 * @access	public
	 */
	function &getSubmitter()
	{
		$params = array(
			$this->_request->getParameter("assignment_id"),
			$this->_request->getParameter("submit_id")
		);

		$sql = "SELECT ".$this->_getReportSelectColumSQL().
				" FROM {assignment_report} Report " .
				" INNER JOIN {assignment_body} RBody" .
					" ON (Report.body_id = RBody.body_id)".
				" INNER JOIN {assignment_submitter} Submitter" .
					" ON (Report.submit_id = Submitter.submit_id)".
				" WHERE Submitter.assignment_id = ? ".
				" AND Submitter.submit_id = ?" .
				" ORDER BY Report.update_time DESC";

		$reports = $this->_db->execute($sql, $params, 1, 0, true, array($this, "_makeReportArray"));
		if ($reports === false) {
			$this->_db->addError();
			return $reports;
		}
		foreach ($reports as $key=>$report) {
			break;
		}
		return $report;
	}

	/**
	 * 課題データ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_makeReportArray(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$row["hasEditAuthority"] = $this->hasEditAuthority($row);
			$row["hasCommentWriteAuthority"] = $this->hasCommentWriteAuthority($row);
			$row["hasGradeAuthority"] = $this->hasGradeAuthority($row);

			$result[$row["report_id"]] = $row;
		}
		return $result;
	}

	/**
	 * レポートが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function reportExists()
	{
		$params = array(
			$this->_request->getParameter("submit_id"),
			$this->_request->getParameter("report_id")
		);

		$sql = "SELECT Report.report_id".
				" FROM {assignment_report} Report " .
				$this->_getAuthorityFromSQL().
				" WHERE Report.submit_id = ? ".
				" AND Report.report_id = ? ".
				$this->_getAuthorityWhereSQL($params);

		$reportIDs = $this->_db->execute($sql, $params);
		if ($reportIDs === false) {
			$this->_db->addError();
			return $reportIDs;
		}

		if (count($reportIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * コメントデータを取得する
	 *
     * @return array	コメントデータ配列
	 * @access	public
	 */
	function &getComments($report_id)
	{
		$params = array(
			$report_id
		);
		$sql = "SELECT * " .
				" FROM {assignment_comment} " .
				" WHERE report_id = ?" .
				" ORDER BY update_time";

		$comments = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeCommentArray"));
		if ($comments === false) {
			$this->_db->addError();
			return $comments;
		}

		return $comments;
	}

	/**
	 * コメントデータを取得する
	 *
     * @return array	コメントデータ配列
	 * @access	public
	 */
	function &getComment()
	{
		$params = array(
			$this->_request->getParameter("assignment_id"),
			$this->_request->getParameter("room_id"),
			$this->_request->getParameter("report_id"),
			$this->_request->getParameter("comment_id"),
		);
		$sql = "SELECT * " .
				" FROM {assignment_comment} " .
				" WHERE assignment_id = ?" .
				" AND room_id = ?" .
				" AND report_id = ?" .
				" AND comment_id = ?";

		$comments = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeCommentArray"));
		if ($comments === false) {
			$this->_db->addError();
			return $comments;
		}
		if (empty($comments)) {
			$comments = false;
			return $comments;
		}
		return $comments[0];
	}

	/**
	 * 課題データ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_makeCommentArray(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$row["hasCommentEditAuthority"] = $this->hasCommentEditAuthority($row["insert_user_id"]);
			$result[] = $row;
		}
		return $result;
	}

	/**
	 * 解答IDを取得する
	 *
     * @return array	配置されている課題データ配列
	 * @access	public
	 */
	function getSubmitterID($submit_id = null)
	{
		if (empty($submit_id)) {
			$params = array(
				"assignment_id" => $this->_request->getParameter("assignment_id")
			);

			$sql = "SELECT Assign.grade_authority, '". $this->_session->getParameter("_user_id"). "' AS insert_user_id" .
					" FROM {assignment} Assign".
					" WHERE Assign.assignment_id = ?";
		} else {
			$params = array(
				"submit_id" => $submit_id,
				"assignment_id" => $this->_request->getParameter("assignment_id")
			);

			$sql = "SELECT Assign.grade_authority, Submitter.insert_user_id " .
					" FROM {assignment} Assign".
					" LEFT JOIN {assignment_submitter} Submitter" .
						" ON (Assign.assignment_id = Submitter.assignment_id AND Submitter.submit_id = ?)".
					" WHERE Assign.assignment_id = ?";
		}

		$submits = $this->_db->execute($sql, $params);
		if ($submits === false) {
			$this->_db->addError();
		}
		if (empty($submits)) {
			return "";
		}

		$authID = $this->_session->getParameter("_auth_id");

		if (!empty($submit_id)) {
			return $submits[0]["insert_user_id"];
		} elseif ($submits[0]["grade_authority"] <= $authID || $authID <= _AUTH_GUEST) {
			return "";
		} else {
			return $submits[0]["insert_user_id"];
		}
	}

	/**
	 * 現在配置されている課題IDを取得する
	 *
     * @return string	配置されている課題ID
	 * @access	public
	 */
	function &getCurrentAssignmentID()
	{
		$params = array(
			$this->_request->getParameter("block_id")
		);
		$sql = "SELECT assignment_id".
				" FROM {assignment_block}".
				" WHERE block_id = ?";

		$assignmentIDs = $this->_db->execute($sql, $params);
		if ($assignmentIDs === false) {
			$this->_db->addError();
			return $assignmentIDs;
		}

		return $assignmentIDs[0]["assignment_id"];
	}

	/**
	 * 現在配置されている課題データを取得する
	 *
     * @return array	配置されている課題データ配列
	 * @access	public
	 */
	function &getCurrentAssignment()
	{
		$params = array(
			"block_id" => $this->_request->getParameter("block_id")
		);

		$sql = "SELECT Assign.*, ABody.body AS assignment_body, AMail.*" .
				" FROM {assignment_block} Block".
				" INNER JOIN {assignment} Assign" .
					" ON (Block.assignment_id = Assign.assignment_id)".
				" INNER JOIN {assignment_body} ABody" .
					" ON (Assign.body_id = ABody.body_id)".
				" INNER JOIN {assignment_mail} AMail" .
					" ON (Assign.assignment_id = AMail.assignment_id)".
				" WHERE Block.block_id = ?";

		$assignment = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeAssignmentArray"));
		if ($assignment === false) {
			$this->_db->addError();
		}
		if (empty($assignment)) {
			return $assignment;
		}

		return $assignment;
	}

	/**
	 * 提出者リストを取得する
	 *
     * @return array	提出者リストデータ配列
	 * @access	public
	 */
	function &getSubmitters($yet_submit=false)
	{
		$params = array();

		$sort_col = $this->_request->getParameter("sort_col");
		if (empty($sort_col)) {
			$sort_col = "submit_time";
		}
		$sort_dir = $this->_request->getParameter("sort_dir");
		if (empty($sort_dir)) {
			$sort_dir = "DESC";
		}

		$sql_order = "";
		switch ($sort_col) {
			case "grade_value":
				$sql_order .= ", Submitter.submit_flag ".$sort_dir;
				$sql_order .= ", Submitter.grade_value ".$sort_dir;
				break;
			case "submitter":
				$sql_order .= ", U.handle ". $sort_dir;
				break;
			case "submit_time":
				$sql_order .= ", Submitter.update_time ". $sort_dir;
			default:

		}

		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sql = "SELECT U.user_id, U.handle, Submitter.*, COUNT(Report.report_id) AS report_count" .
				$this->_getSubmittersFromSQL().
				" LEFT JOIN {assignment_report} Report " .
					"ON (Submitter.submit_id = Report.submit_id)".
				" WHERE ". $this->_getSubmittersWhereSQL($params, $yet_submit) .
				" GROUP BY U.user_id".
				(empty($sql_order) ? "" : " ORDER BY".substr($sql_order,1));

		$submitters = $this->_db->execute($sql, $params, $limit, $offset, true);

		if ($submitters === false) {
			$this->_db->addError();
		}
		if (empty($submitters)) {
			return $submitters;
		}
		return $submitters;
	}

	/**
	 * 提出者リストの総件数を取得する
	 *
     * @return int	課題データ配列
	 * @access	public
	 */
	function &getSubmitterCount($yet_submit=false)
	{
		$params = array();

		$sql = "SELECT COUNT(*) " .
				$this->_getSubmittersFromSQL().
				" WHERE ". $this->_getSubmittersWhereSQL($params, $yet_submit) ;

		$submitters = $this->_db->execute($sql, $params, null, null, false);

		if ($submitters === false) {
			$this->_db->addError();
		}
		return $submitters[0][0];
	}

	/**
	 * 集計結果を取得する
	 *
     * @return array	課題データ配列
	 * @access	public
	 */
	function &getSummary()
	{
		$params = array();

		$sql = "SELECT Submitter.submit_flag, Submitter.grade_value, COUNT(*) AS grade_count " .
				$this->_getSubmittersFromSQL().
				" WHERE ". $this->_getSubmittersWhereSQL($params, true, true) .
				" GROUP BY Submitter.submit_flag, Submitter.grade_value";

		$summary = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeSummaryArray"));

		if ($summary === false) {
			$this->_db->addError();
		}
		if (empty($summary)) {
			return $summary;
		}
		return $summary;
	}

	/**
	 * 集計データ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_makeSummaryArray(&$recordSet)
	{
		$result = array(
			ASSIGNMENT_SUBMIT_FLAG_YET_REREASED => 0,
			ASSIGNMENT_SUBMIT_FLAG_SUBMITTED => 0,
			ASSIGNMENT_SUBMIT_FLAG_GRADED => array(),
			ASSIGNMENT_SUBMIT_FLAG_RESUBMITTED => 0,
			"total" => 0
		);
		while ($row = $recordSet->fetchRow()) {
			switch ($row["submit_flag"]) {
				case ASSIGNMENT_SUBMIT_FLAG_SUBMITTED:
				case ASSIGNMENT_SUBMIT_FLAG_RESUBMITTED:
					$result[$row["submit_flag"]] += $row["grade_count"];
					break;

				case ASSIGNMENT_SUBMIT_FLAG_GRADED:
					if (!isset($result[$row["submit_flag"]][$row["grade_value"]])) { $result[$row["submit_flag"]][$row["grade_value"]] = 0; }

					$result[$row["submit_flag"]][$row["grade_value"]] += $row["grade_count"];
					break;

				default:
					$result[ASSIGNMENT_SUBMIT_FLAG_YET_REREASED] += $row["grade_count"];
			}

			$result["total"] += $row["grade_count"];
		}
		return $result;
	}


	/**
	 * 集計表示権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasSummaryAuthority()
	{
		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			return false;
		}

		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= $assignment["grade_authority"]) {
			return true;
		}

	    return false;
	}

	/**
	 * 解答レポート編集権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasEditAuthority(&$report)
	{
		$reference = $this->_request->getParameter("reference");
		if ($reference == _ON) {
			return false;
		}

		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			return false;
		}

		if ($assignment["activity"] == _OFF) {
			return false;
		}

		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}

		$newestReportID = $this->_request->getParameter("newest_report_id");
		if ($newestReportID != $report["report_id"]) {
			return false;
		}

		$userID = $this->_session->getParameter("_user_id");
		if ($report["insert_user_id"] == $userID) {
			if ($report["status"] != ASSIGNMENT_STATUS_REREASED) {
				return true;
			}
			return false;
		}

		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($report["insert_user_id"]);

		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * コメント投稿権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasCommentWriteAuthority(&$report)
	{
		$reference = $this->_request->getParameter("reference");
		if ($reference == _ON) {
			return false;
		}

		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			return false;
		}

		if ($assignment["activity"] == _OFF) {
			return false;
		}

		if ($report["status"] != ASSIGNMENT_STATUS_REREASED) {
			return false;
		}

		$newestReportID = $this->_request->getParameter("newest_report_id");
		if ($newestReportID != $report["report_id"]) {
			return false;
		}

		$userID = $this->_session->getParameter("_user_id");
		if ($report["insert_user_id"] == $userID) {
			return true;
		}

		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($report["insert_user_id"]);

		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}


	/**
	 * コメント編集権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasCommentEditAuthority(&$insertUserID)
	{
		$reference = $this->_request->getParameter("reference");
		if ($reference == _ON) {
			return false;
		}

		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			return false;
		}

		if ($assignment["activity"] == _OFF) {
			return false;
		}

		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}

		$userID = $this->_session->getParameter("_user_id");
		if ($insertUserID == $userID) {
			return true;
		}

		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($insertUserID);

		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * 解答権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasAnswerAuthority($resetting = false)
	{
		$reference = $this->_request->getParameter("reference");
		if ($reference == _ON) {
			return false;
		}

		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			return false;
		}

		$report = $this->_request->getParameter("report");
		if ($resetting && !empty($report)) {
			if ($report["submit_flag"] == ASSIGNMENT_SUBMIT_FLAG_GRADED ||
				$report["status"] != ASSIGNMENT_STATUS_REREASED) {
				return false;
			}
		}

		$authID = $this->_session->getParameter("_auth_id");
		if ($assignment["activity"] == _ON && $authID >= _AUTH_GENERAL) {
			return true;
		}

	    return false;
	}

	/**
	 * 他人のレポート権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasSubmitListView()
	{
		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			return false;
		}

		$prefix_id_name = $this->_request->getParameter("prefix_id_name");
		if ($prefix_id_name == ASSIGNMENT_SUBMITTER_PREFIX_NAME.$assignment["assignment_id"]) {
			return false;
		}

		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}

		if ($authID >= $assignment["grade_authority"]) {
			return true;
		}

	    return false;
	}

	/**
	 * 採点権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasGradeAuthority(&$report)
	{
		$reference = $this->_request->getParameter("reference");
		if ($reference == _ON) {
			return false;
		}

		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			return false;
		}

		$newestReportID = $this->_request->getParameter("newest_report_id");
		if ($newestReportID != $report["report_id"]) {
			return false;
		}

		if ($report["status"] != ASSIGNMENT_STATUS_REREASED) {
			return false;
		}

		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}

		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($report["insert_user_id"]);

		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($hierarchy > $insetUserHierarchy && $authID >= $assignment["grade_authority"]) {
	        return true;
		}

	    return false;
	}

	/**
	 * レポート閲覧権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function hasSubmitterView($insertUserID)
	{
		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}

		$userID = $this->_session->getParameter("_user_id");
		if ($insertUserID == $userID) {
			return true;
		}

		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($insertUserID);

		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * 限間近警告日数データを取得する
	 *
     * @return string	限間近警告日数データ
	 * @access	public
	 */
	function &_getSoonPeriod()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfigByConfname($moduleID, "soon_period");
		if ($config === false) {
        	return $config;
        }

        return $config["conf_value"];
	}

	/**
	 * 権限判断用のSQL文FROM句を取得する
	 *
     * @return string	権限判断用のSQL文FROM句
	 * @access	public
	 */
	function &_getAuthorityFromSQL()
	{
		$authID = $this->_session->getParameter("_auth_id");

		$sql = "";
		if ($authID >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= " LEFT JOIN {pages_users_link} PU ".
					"ON (Report.insert_user_id = PU.user_id AND Report.room_id = PU.room_id)".
				" LEFT JOIN {authorities} A ".
					"ON (PU.role_authority_id = A.role_authority_id) ";

		return $sql;
	}

	/**
	 * 権限判断用のSQL文WHERE句を取得する
	 * パラメータ用配列に必要な値を追加する
	 *
	 * @param	array	$params	パラメータ用配列
     * @return string	権限判断用のSQL文WHERE句
	 * @access	public
	 */
	function &_getAuthorityWhereSQL(&$params)
	{
		$authID = $this->_session->getParameter("_auth_id");

		$sql = "";
		if ($authID >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "AND (Report.status = ? OR A.hierarchy < ? OR Report.insert_user_id = ?";

		$defaultEntry = $this->_session->getParameter("_default_entry_flag");
		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($defaultEntry == _ON && $hierarchy > $this->_session->getParameter("_default_entry_hierarchy")) {
			$sql .= " OR A.hierarchy IS NULL) ";
		} else {
			$sql .= ") ";
		}

		$params[] = ASSIGNMENT_STATUS_REREASED;
		$params[] = $hierarchy;
		$params[] = $this->_session->getParameter("_user_id");

		return $sql;
	}

	/**
	 * 提出者リスト用のSQL文FROM句を取得する
	 *
     * @return string	提出者用のSQL文FROM句
	 * @access	public
	 */
	function &_getSubmittersFromSQL()
	{
		$defaultEntry = $this->_session->getParameter("_default_entry_flag");

		$sql = " FROM {users} U" .
				" LEFT JOIN {assignment_submitter} Submitter " .
					"ON (U.user_id = Submitter.insert_user_id AND Submitter.assignment_id = ?)" .
				" LEFT JOIN {pages_users_link} PU " .
					"ON (U.user_id = PU.user_id" .
						($defaultEntry == _ON ? " AND PU.room_id = ?" : "") . ")" .
				" LEFT JOIN {authorities} A ".
					"ON (PU.role_authority_id = A.role_authority_id)";

		return $sql;
	}

	/**
	 * 提出者リスト用のSQL文WHERE句を取得する
	 *
     * @return string	提出者用のSQL文FROM句
	 * @access	public
	 */
	function &_getSubmittersWhereSQL(&$params, $yet_submit=false, $force=false)
	{
		$defaultEntry = $this->_session->getParameter("_default_entry_flag");
		$defaultEntryAuth = $this->_session->getParameter("_default_entry_auth");
		$assignment = $this->_request->getParameter("assignment");

		$sql = ($defaultEntry == _ON ? "1=1" : "PU.room_id = ?") .
				" AND (Submitter.assignment_id = ?";

		$params[] = $this->_request->getParameter("assignment_id");
		$params[] = $this->_request->getParameter("room_id");
		$params[] = $this->_request->getParameter("assignment_id");

		$authID = $this->_session->getParameter("_auth_id");
		if ($authID < _AUTH_CHIEF && !$force) {
			$sql .= " AND (A.hierarchy < ? OR Submitter.insert_user_id = ?";

			$hierarchy = $this->_session->getParameter("_hierarchy");
			if ($defaultEntry == _ON && $hierarchy > $this->_session->getParameter("_default_entry_hierarchy")) {
				$sql .= " OR A.hierarchy IS NULL) ";
			} else {
				$sql .= ") ";
			}

			$params[] = $hierarchy;
			$params[] = $this->_session->getParameter("_user_id");
		}

		if ($yet_submit) {
			$sql .= " OR (Submitter.assignment_id IS NULL)" .
					" AND (A.user_authority_id < ?" .
						" AND A.user_authority_id > ?" .
						($defaultEntry == _ON && $defaultEntryAuth > _AUTH_GUEST ? " OR A.user_authority_id IS NULL" : "") . "))" ;

			$params[] = $assignment["grade_authority"];
			$params[] = _AUTH_GUEST;
		} else {
			$sql .= ")";
		}

		return $sql;
	}

	/**
	 * 解答レポート用のSQL文SELECTを取得する
	 *
     * @return string	提出者用のSQL文FROM句
	 * @access	public
	 */
	function &_getReportSelectColumSQL()
	{
		$sql = "Report.*, RBody.body AS report_body, ".
				"Submitter.submit_id, Submitter.submit_flag, Submitter.grade_value, " .
				"Submitter.insert_user_id AS submit_user_id, " .
				"Submitter.insert_user_name AS submit_user_name, ".
				"Submitter.update_time AS submit_update_time";
		return $sql;
	}

	/**
	 * メール送信データを取得する
	 *
	 * @param	string	$postID	記事ID
	 * @return array	メール送信データ配列
	 * @access	public
	 */
	function &getMail($reportID)
	{
		$params = array(
			$reportID
		);

		$sql = "SELECT Assign.assignment_id, Assign.assignment_name, Assign.grade_authority, " .
						"AMail.mail_send, AMail.mail_subject, AMail.mail_body, " .
						"Report.submit_id, Report.report_id, RBody.body AS report_body, Report.insert_user_name, Report.insert_time".
				" FROM {assignment_report} Report" .
				" INNER JOIN {assignment_body} RBody" .
					" ON (Report.body_id = RBody.body_id)" .
				" INNER JOIN {assignment} Assign" .
					" ON (Report.assignment_id = Assign.assignment_id)".
				" INNER JOIN {assignment_mail} AMail" .
					" ON (Assign.assignment_id = AMail.assignment_id)" .
				" WHERE Report.report_id = ?";

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
	function &getPersonalAssignments()
	{
    	$ownUserID = $this->_session->getParameter("_user_id");
    	$otherUserID = $this->_request->getParameter("user_id");

		$pagesView =& $this->_container->getComponent("pagesView");
		if ($ownUserID != $otherUserID) {
			$ownRoomIDs = $pagesView->getRoomIdByUserId($ownUserID);
	    	$otherRoomIDs = $pagesView->getRoomIdByUserId($otherUserID, _AUTH_GENERAL);
			$roomIDs = array_intersect($otherRoomIDs, $ownRoomIDs);
		} else {
			$roomIDs = $pagesView->getRoomIdByUserId($ownUserID, _AUTH_GENERAL);
		}
		if (empty($roomIDs)) {
			return $roomIDs;
		}

		$sql = "SELECT Assign.room_id, Assign.assignment_name, Assign.icon_name, " .
					"Assign.activity, Assign.period, Assign.grade_authority, " .
					"Assign.insert_time, Assign.insert_user_id, " .
					"Submitter.submit_flag, Submitter.grade_value, " .
					"Submitter.insert_user_id AS submit_user_id, Submitter.update_time AS submit_update_time," .
					"Page.page_name, AssignBlock.block_id" .
				" FROM {assignment_block} AssignBlock" .
				" INNER JOIN {blocks} Block ON (AssignBlock.block_id = Block.block_id)".
				" INNER JOIN {assignment} Assign ON (AssignBlock.assignment_id = Assign.assignment_id)".
				" INNER JOIN {pages} Page ON (Assign.room_id = Page.page_id)".
				" LEFT JOIN {assignment_submitter} Submitter" .
					" ON (Assign.assignment_id = Submitter.assignment_id AND Submitter.insert_user_id = ?)".
				" WHERE Assign.room_id IN (".implode(",", $roomIDs).")" .
				" GROUP BY Assign.assignment_id" .
				" ORDER BY Assign.room_id, Assign.assignment_id DESC";

		$params = array(
			"insert_user_id" => $otherUserID
		);

		$personalAssignments = $this->_db->execute($sql, $params, null, null, true, array($this, "_makePersonalAssignments"), $roomIDs);
		if ($personalAssignments === false) {
			$this->_db->addError();
			return $personalAssignments;
		}

		return $personalAssignments;
	}

	/**
	 * 解答一覧データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$roomIDs	対象ルームID配列
     * @return string	解答一覧データ配列
	 * @access	public
	 */
	function &_makePersonalAssignments(&$recordSet, $roomIDs)
	{

		$roomIDKeys = array_flip($roomIDs);
		while ($row = $recordSet->fetchRow()) {
			if (!is_array($roomIDKeys[$row["room_id"]])) {
				$roomIDKeys[$row["room_id"]] = array();
			} else {
				$row["page_name"] = "";
			}
			$this->_makePeriod($row);
			$roomIDKeys[$row["room_id"]][] = $row;
		}

		$assignments = array();
		foreach (array_keys($roomIDKeys) as $roomID) {
			if (is_array($roomIDKeys[$roomID])) {
				$assignments = array_merge($assignments, $roomIDKeys[$roomID]);
			}
		}

		return $assignments;
	}

}
?>