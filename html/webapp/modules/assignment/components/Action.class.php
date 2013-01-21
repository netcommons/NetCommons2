<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Components_Action
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
	function Assignment_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_session =& $this->_container->getComponent("Session");
	}

	/**
	 * 課題データを登録処理
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setAssignment($update=false)
	{
		if (!$update) {
			$params = array(
				"report_id" => 0,
				"body" => $this->_request->getParameter("assignment_body")
			);
			$result = $this->_db->insertExecute("assignment_body", $params, true, "body_id");
			if (!$result) {
				return false;
			}
			$body_id = $result;
		} else {
			$assignment = $this->_request->getParameter("assignment");
			$body_id = $assignment["body_id"];
			$params = array(
				"body_id" => $body_id,
				"report_id" => 0,
				"body" => $this->_request->getParameter("assignment_body")
			);
			$result = $this->_db->updateExecute("assignment_body", $params, "body_id", true);
			if (!$result) {
				return false;
			}
		}

		$params = array(
			"assignment_name" => $this->_request->getParameter("assignment_name"),
			"icon_name" => $this->_request->getParameter("icon_name"),
			"period" => $this->_request->getParameter("period"),
			"grade_authority" => intval($this->_request->getParameter("grade_authority"))
		);

		if (!$update) {
			$params["body_id"] = $body_id;
			$activity = intval($this->_request->getParameter("activity"));
			$params["activity"] = $activity;
			$result = $this->_db->insertExecute("assignment", $params, true, "assignment_id");
		} else {
			$assignment_id = $this->_request->getParameter("assignment_id");
			$params["assignment_id"] = $assignment_id;
			$result = $this->_db->updateExecute("assignment", $params, "assignment_id", true);
		}
		if (!$result) {
			return false;
		}
		if (empty($assignment_id)) {
        	$assignment_id = $result;
        }
		$this->_request->setParameter("assignment_id", $assignment_id);

		$params = array(
			"assignment_id" => $assignment_id,
			"mail_send" => intval($this->_request->getParameter("mail_send")),
			"mail_subject" => $this->_request->getParameter("mail_subject"),
			"mail_body" => $this->_request->getParameter("mail_body")
		);
		if (!$update) {
			$result = $this->_db->insertExecute("assignment_mail", $params, true);
			if (!$result) {
				return false;
			}
			$params = array(
				"assignment_id" => $assignment_id,
				"body_id" => $body_id
			);
			$result = $this->_db->updateExecute("assignment_body", $params, "body_id", true);
		} else {
			$result = $this->_db->updateExecute("assignment_mail", $params, "assignment_id", true);
		}
		if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$activity = $this->_request->getParameter("activity");
		if (isset($activity)) {
	        if (!$this->setActivity()) {
				return false;
			}
		}

		$block_id = $this->_request->getParameter("block_id");

		$params = array(
			"block_id" => $block_id
		);
		$sql = "SELECT block_id, assignment_id ".
				"FROM {assignment_block} ".
				"WHERE block_id = ?";
		$block_ids = $this->_db->execute($sql, $params);
		if ($block_ids === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $block_id,
			"assignment_id" => $this->_request->getParameter("assignment_id")
		);
		if (!empty($block_ids)) {
			$result = $this->_db->updateExecute("assignment_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("assignment_block", $params, true);
		}
        if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * 課題データ削除処理
	 *
	 * @return boolean
	 * @access public
	 */
	function deleteAssignment()
	{
    	$params = array(
			"assignment_id" => $this->_request->getParameter("assignment_id")
		);

    	$result = $this->_db->deleteExecute("assignment_block", $params);
    	if ($result === false) {
    		return false;
    	}
    	$result = $this->_db->deleteExecute("assignment", $params);
    	if ($result === false) {
    		return false;
    	}

    	$result = $this->_db->deleteExecute("assignment_mail", $params);
    	if ($result === false) {
    		return false;
    	}

    	$result = $this->_db->deleteExecute("assignment_body", $params);
    	if ($result === false) {
    		return false;
    	}

    	$result = $this->_db->deleteExecute("assignment_submitter", $params);
    	if ($result === false) {
    		return false;
    	}

    	$result = $this->_db->deleteExecute("assignment_report", $params);
    	if ($result === false) {
    		return false;
    	}

		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$whatsnewAction->delete($this->_request->getParameter("assignment_id"));
    	return true;
	}

	/**
	 * 動作/停止を変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setActivity()
	{
		$params = array(
			"assignment_id" => $this->_request->getParameter("assignment_id"),
			"activity" => intval($this->_request->getParameter("activity"))
		);
        if (!$this->_db->updateExecute("assignment", $params, "assignment_id", true)) {
			return false;
		}

		$result = $this->setWhatsnew();
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * 提出データを登録処理
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function submitReport($update = false)
	{
		$submit_id = $this->_request->getParameter("submit_id");
		if (empty($submit_id)) {
			$params = array(
				"assignment_id" => $this->_request->getParameter("assignment_id"),
				"user_id" => $this->_session->getParameter("_user_id")
			);

			$sql = "SELECT submit_id".
					" FROM {assignment_submitter}".
					" WHERE assignment_id = ?" .
					" AND insert_user_id = ?";
			$submit_ids = $this->_db->execute($sql, $params);
			if ($submit_ids === false) {
				$this->_db->addError();
				return false;
			}
			if (!empty($submit_ids)) {
				$submit_id = $submit_ids[0]["submit_id"];
			}
		}

		$submitterInsert = false;
		$temporary = intval($this->_request->getParameter("temporary"));
		$update_time = timezone_date();
		if (empty($submit_id)) {
			$params = array(
				"assignment_id" => $this->_request->getParameter("assignment_id"),
				"submit_flag" => $temporary == _ON ? ASSIGNMENT_SUBMIT_FLAG_YET_REREASED : ASSIGNMENT_SUBMIT_FLAG_SUBMITTED,
				"grade_value" => "",
				"insert_time" => $update_time,
				"update_time" => $update_time
			);

			$result = $this->_db->insertExecute("assignment_submitter", $params, true, "submit_id");
			if (!$result) {
				return false;
			}
			$submit_id = $result;
			$submitterInsert = true;
		} else {
			$params = array(
				"submit_id" => $submit_id,
				"grade_value" => ""
			);
			if ($temporary != _ON) {
				$params["submit_flag"] = ASSIGNMENT_SUBMIT_FLAG_SUBMITTED;
				$params["update_time"] = $update_time;
			}

			$result = $this->_db->updateExecute("assignment_submitter", $params, "submit_id", false);
			if (!$result) {
				return false;
			}
		}

		if (!$update) {
			$params = array(
				"assignment_id" => $this->_request->getParameter("assignment_id"),
				"body" => $this->_request->getParameter("report_body")
			);
			$result = $this->_db->insertExecute("assignment_body", $params, true, "body_id");
			if (!$result) {
				return false;
			}
			$body_id = $result;

			$params = array(
				"assignment_id" => $this->_request->getParameter("assignment_id"),
				"submit_id" => $submit_id,
				"body_id" => $body_id,
				"status" => $temporary == _ON ? ASSIGNMENT_STATUS_BEFORE_REREASED : ASSIGNMENT_STATUS_REREASED,
				"insert_time" => $update_time,
				"update_time" => $update_time
			);
			$result = $this->_db->insertExecute("assignment_report", $params, true, "report_id");

			$status = $params["status"];
			if (!$result) {
				return false;
			}
        	$report_id = $result;
			$this->_request->setParameter("report_id", $report_id);

			$params = array(
				"report_id" => $report_id,
				"body_id" => $body_id
			);
			$result = $this->_db->updateExecute("assignment_body", $params, "body_id", true);

			$insertFlag = true;
		} else {
			$report = $this->_request->getParameter("report");
			$body_id = $report["body_id"];
			$params = array(
				"body_id" => $body_id,
				"body" => $this->_request->getParameter("report_body")
			);
			$result = $this->_db->updateExecute("assignment_body", $params, "body_id", true);
			if (!$result) {
				return false;
			}

			$report_id = $this->_request->getParameter("report_id");
			$params = array(
				"report_id" => $report["report_id"],
				"status" => $temporary == _ON ? ASSIGNMENT_STATUS_TEMPORARY : ASSIGNMENT_STATUS_REREASED,
				"update_time" => $update_time
			);
			if ($report["status"] == ASSIGNMENT_STATUS_BEFORE_REREASED && $temporary == _ON) {
				$params["status"] = ASSIGNMENT_STATUS_BEFORE_REREASED;
			}
			if ($report["status"] == ASSIGNMENT_STATUS_BEFORE_REREASED && $temporary == _OFF) {
				$params["insert_time"] = $update_time;
			}
			$result = $this->_db->updateExecute("assignment_report", $params, "report_id", true);

			$status = $params["status"];
			$insertFlag = false;

			if (!$submitterInsert && $temporary != _ON) {
				$params = array(
					"submit_id" => $submit_id,
					"update_time" => $update_time
				);
				$result = $this->_db->updateExecute("assignment_submitter", $params, "submit_id", true);
			}
		}
		if (!$result) {
			return false;
		}

		$assignment = $this->_request->getParameter("assignment");
		if ($assignment["mail_send"] == _ON &&
				$status == ASSIGNMENT_STATUS_REREASED &&
				($insertFlag ||
					$report["status"] == ASSIGNMENT_STATUS_BEFORE_REREASED)) {
			$this->_session->setParameter("assignment_mail_report_id", $report_id);
		}

		return true;
	}

	/**
	 * 評価処理
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function gradeReport()
	{
		$submit_id = $this->_request->getParameter("submit_id");
		$grade_value = $this->_request->getParameter("grade_value");
		$submit_flag = (empty($grade_value) ? ASSIGNMENT_SUBMIT_FLAG_RESUBMITTED : ASSIGNMENT_SUBMIT_FLAG_GRADED);

		$params = array(
			"submit_id" => $submit_id,
			"submit_flag" => $submit_flag,
			"grade_value" => $grade_value
		);

		$result = $this->_db->updateExecute("assignment_submitter", $params, "submit_id", false);
		if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * レポートデータ削除処理
	 *
	 * @return boolean
	 * @access public
	 */
	function deleteReport()
	{
    	$params = array(
			"report_id" => $this->_request->getParameter("report_id")
		);

    	$result = $this->_db->deleteExecute("assignment_report", $params);
    	if ($result === false) {
    		return false;
    	}

    	$result = $this->_db->deleteExecute("assignment_body", $params);
    	if ($result === false) {
    		return false;
    	}

    	$result = $this->_db->deleteExecute("assignment_comment", $params);
    	if ($result === false) {
    		return false;
    	}

    	$params = array(
			"submit_id" => $this->_request->getParameter("submit_id")
		);
    	$count = $this->_db->countExecute("assignment_report", $params);
		if ($count <= 0) {
	    	$result = $this->_db->deleteExecute("assignment_submitter", $params);
	    	if ($result === false) {
	    		return false;
	    	}
			$this->_request->setParameter("submit_id", null);
		}

		$this->_request->setParameter("report_id", null);
    	return true;
	}

	/**
	 * コメントデータを登録処理
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setComment($update = false)
	{
		$comment_id = $this->_request->getParameter("comment_id");

		if (!$update) {
			$params = array(
				"report_id" => $this->_request->getParameter("report_id"),
				"assignment_id" => $this->_request->getParameter("assignment_id"),
				"comment_value" => $this->_request->getParameter("comment_value")
			);
			$result = $this->_db->insertExecute("assignment_comment", $params, true, "comment_id");
		} else {
			$params = array(
				"comment_id" => $comment_id,
				"report_id" => $this->_request->getParameter("report_id"),
				"assignment_id" => $this->_request->getParameter("assignment_id"),
				"comment_value" => $this->_request->getParameter("comment_value")
			);
			$result = $this->_db->updateExecute("assignment_comment", $params, "comment_id", true);
		}
		if (!$result) {
			return false;
		}
		if (empty($comment_id)) {
        	$comment_id = $result;
        }
		$this->_request->setParameter("comment_id", $comment_id);

		return true;
	}

	/**
	 * コメントデータ削除処理
	 *
	 * @return boolean
	 * @access public
	 */
	function deleteComment()
	{
    	$params = array(
			"comment_id" => $this->_request->getParameter("comment_id")
		);

    	$result = $this->_db->deleteExecute("assignment_comment", $params);
    	if ($result === false) {
    		return false;
    	}

		$this->_request->setParameter("comment_id", null);
    	return true;
	}

	/**
	 * 評価値データを登録処理
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setGradeValue()
	{
		$params = array(
			"room_id" => $this->_request->getParameter("room_id")
		);

    	$result = $this->_db->deleteExecute("assignment_grade_value", $params);
    	if ($result === false) {
    		return false;
    	}

		$grade_values = $this->_request->getParameter("grade_value");
		if (empty($grade_values)) { return true; }

		$disp_seq = 0;
		foreach ($grade_values as $i=>$grade_value) {
			if (empty($grade_value)) { continue; }

			$disp_seq++;
			$params = array(
				"grade_value" => $grade_value,
				"display_sequence" => $disp_seq
			);
			$result = $this->_db->insertExecute("assignment_grade_value", $params, true);
			if (!$result) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 期限切れ課題を終了に変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setPeriodOver()
	{
		$params = array(
			"period" => timezone_date()
		);
		$sql = "UPDATE {assignment} SET activity = " . _OFF .
				" WHERE period != ''" .
				" AND activity = " . _ON .
				" AND period < ?";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
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
		$assignment = $this->_request->getParameter("assignment");
		if (empty($assignment)) {
			$assignmentView =& $this->_container->getComponent("assignmentView");
			$assignment = $assignmentView->getAssignment();
			if (empty($assignment)) {
				return false;
			}
		}
		$activity = intval($this->_request->getParameter("activity"));

		if ($activity == _ON) {
			$whatsnew_lang = ASSIGNMENT_START_WHATSNEW;
		} else {
			return true;
		}

		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
    	$assignment_name = mb_substr($assignment["assignment_name"], 0, ASSIGNMENT_WHATSNEW_TITLE, INTERNAL_CODE);
    	if ($assignment_name != $assignment["assignment_name"]) {
    		$assignment_name .= _SEARCH_MORE;
    	}

		$whatsnew = array(
			"unique_id" => $assignment["assignment_id"],
			"title" => sprintf($whatsnew_lang, $assignment_name),
			"description" => "",
			"action_name" => "assignment_view_main_whatsnew",
			"parameters" => "assignment_id=". $assignment["assignment_id"]
		);
		$result = $whatsnewAction->insert($whatsnew);
		if ($result === false) {
			return false;
		}

		return true;
	}

}
?>