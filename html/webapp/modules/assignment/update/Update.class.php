<?php
/**
 * モジュールアップデートクラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// assignmentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_activity_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "activity") {
				$alter_table_activity_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_activity_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment` ADD INDEX ( `activity`, `period` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// assignment_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_assignment_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "assignment_id") {
				$alter_table_assignment_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_assignment_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_block` ADD INDEX ( `assignment_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// assignment_bodyにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment_body` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_assignment_id_flag = true;
		$alter_table_report_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "assignment_id") {
				$alter_table_assignment_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "report_id") {
				$alter_table_report_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_assignment_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_body` ADD INDEX ( `assignment_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_report_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_body` ADD INDEX ( `report_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_body` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// assignment_commentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment_comment` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_assignment_id_flag = true;
		$alter_table_report_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "assignment_id") {
				$alter_table_assignment_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "report_id") {
				$alter_table_report_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_assignment_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_comment` ADD INDEX ( `assignment_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_report_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_comment` ADD INDEX ( `report_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_comment` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// assignment_grade_valueにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment_grade_value` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_grade_value` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// assignment_mailにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment_mail` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_mail` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// assignment_reportにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment_report` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_assignment_id_flag = true;
		$alter_table_body_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "assignment_id") {
				$alter_table_assignment_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "body_id") {
				$alter_table_body_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_assignment_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_report` ADD INDEX ( `assignment_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_body_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_report` ADD INDEX ( `body_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_report` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// assignment_submitterにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."assignment_submitter` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_assignment_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "assignment_id") {
				$alter_table_assignment_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_assignment_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_submitter` ADD INDEX ( `assignment_id`, `insert_user_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."assignment_submitter` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		return true;
	}
}
?>
