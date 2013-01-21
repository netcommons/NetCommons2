<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール操作時(move,copy,shortcut)に呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Admin_Operation extends Action
{
	var $mode = null;	//move or shortcut or copy
	// 移動元
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;
	var $unique_id = null;

	// 移動先
	var $move_page_id = null;
	var $move_room_id = null;
	var $move_block_id = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $commonOperation = null;
	var $whatsnewAction = null;

	function execute()
	{
		switch ($this->mode) {
    		case "move":
    			//存在チェック
				$where_params = array(
					"assignment_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);

				$sql = "SELECT *" .
						" FROM {assignment_body}".
						" WHERE assignment_id = ?".
						" AND room_id = ?";

				$assignments = $this->db->execute($sql, $where_params, null, null, true);
    			if ($assignments === false || !isset($assignments[0])) {
					return "false";
				}
				
				//評価ラベルの更新
				$where_params = array(
					"room_id"=> intval($this->move_room_id)
				);

				$sql = "SELECT COUNT(*)" .
						" FROM {assignment}".
						" WHERE room_id = ?";
				$assignCount = $this->db->execute($sql, $where_params, null, null, false);
    			if ($assignCount === false || !isset($assignCount[0])) {
					return "false";
				}
				$sql = "SELECT COUNT(*)" .
						" FROM {assignment_grade_value}" .
						" WHERE room_id = ?";					
				$gradeCount = $this->db->execute($sql, $where_params, null, null, false);
    			if ($gradeCount === false || !isset($gradeCount[0])) {
					return "false";
				}

				if ($assignCount[0][0] == 0 && $gradeCount[0][0] == 0) {
					$where_params = array(
						"room_id"=> intval($this->room_id)
					);
					$sql = "SELECT grade_value, display_sequence" .
							" FROM {assignment_grade_value}" .
							" WHERE room_id = ?";					
					$gradeValues = $this->db->execute($sql, $where_params, null, null, true);
	    			if ($gradeValues === false) {
						return "false";
					}
					if (!empty($gradeValues)) {
						foreach ($gradeValues as $i=>$row) {
			    			$params = array(
			    				"grade_value" => $row["grade_value"],
								"room_id"=> intval($this->move_room_id),
								"display_sequence" => $row["display_sequence"]
							);
							$result = $this->db->insertExecute("assignment_grade_value", $params, true);
							if ($result === false) {
								return "false";
							}
						}
					}
				}

    			//更新
				$where_params = array(
					"assignment_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("assignment", $params, $where_params, false);
				if ($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("assignment_mail", $params, $where_params, false);
				if ($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("assignment_body", $params, $where_params, false);
				if ($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("assignment_submitter", $params, $where_params, false);
				if ($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("assignment_report", $params, $where_params, false);
				if ($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("assignment_comment", $params, $where_params, false);
				if ($result === false) {
					return "false";
				}

				$block_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"assignment_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("assignment_block", $block_params, $where_params, false);
				if ($result === false) {
					return "false";
				}

				//
    			// 添付ファイル更新処理
    			// WYSIWYG
    			//
				$upload_id_arr = $this->commonOperation->getWysiwygUploads("body", $assignments);
    			$result = $this->commonOperation->updWysiwygUploads($upload_id_arr, $this->move_room_id);
    			if ($result === false) {
					return "false";
				}

				//--新着情報関連 Start--
				$whatsnew = array(
					"unique_id" => $this->unique_id,
					"room_id" => $this->move_room_id
				);
				$result = $this->whatsnewAction->moveUpdate($whatsnew);
				if ($result === false) {
					return false;
				}
				//--新着情報関連 End--

				break;
			default:
				return "false";
		}
		return "true";
	}
}
?>