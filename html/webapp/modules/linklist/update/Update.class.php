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
class Linklist_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// linklistにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."linklist` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."linklist` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// linklist_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."linklist_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_linklist_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "linklist_id") {
				$alter_table_linklist_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_linklist_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."linklist_block` ADD INDEX ( `linklist_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."linklist_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// linklist_categoryにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."linklist_category` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_linklist_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "linklist_id") {
				$alter_table_linklist_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_linklist_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."linklist_category` ADD INDEX ( `linklist_id`, `category_sequence` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."linklist_category` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// linklist_linkにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."linklist_link` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_linklist_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "linklist_id") {
				$alter_table_linklist_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_linklist_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."linklist_link` ADD INDEX ( `linklist_id`, `category_id`, `link_sequence` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."linklist_link` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		return true;
	}
}
?>
