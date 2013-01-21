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
class Cabinet_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// cabinet_manageにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."cabinet_manage` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."cabinet_manage` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// cabinet_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."cabinet_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_cabinet_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "cabinet_id") {
				$alter_table_cabinet_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_cabinet_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."cabinet_block` ADD INDEX ( `cabinet_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."cabinet_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// cabinet_commentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."cabinet_comment` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."cabinet_comment` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// cabinet_fileにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."cabinet_file` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_cabinet_id_flag = true;
		$alter_table_parent_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "cabinet_id") {
				$alter_table_cabinet_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "parent_id") {
				$alter_table_parent_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_cabinet_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."cabinet_file` ADD INDEX ( `cabinet_id`,`file_type` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_parent_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."cabinet_file` ADD INDEX ( `parent_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."cabinet_file` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		return true;
	}
}
?>
