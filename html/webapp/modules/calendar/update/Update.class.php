<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アップデートクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

    /**
     * execute実行
     *
     * @access  public
     */
	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaTables = $adodb->MetaTables();

		if (!in_array($this->db->getPrefix()."calendar_select_room", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."calendar_select_room` (" .
					" `block_id` int(11) unsigned NOT NULL," .
					" `room_id` int(11) unsigned NOT NULL," .
					" PRIMARY KEY (`block_id`, `room_id`)" .
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."calendar_block");
		if (!isset($metaColumns["select_room"]) && !isset($metaColumns["SELECT_ROOM"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."calendar_block` ADD `select_room` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `display_count`;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."calendar_block");
		if (!isset($metaColumns["myroom_flag"]) && !isset($metaColumns["MYROOM_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."calendar_block"."` ADD `myroom_flag` TINYINT(1) NULL DEFAULT 0 AFTER `select_room` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// calendar_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."calendar_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."calendar_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// calendar_planにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."calendar_plan` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_plan_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "plan_id") {
				$alter_table_plan_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_plan_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."calendar_plan` ADD INDEX ( `plan_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."calendar_plan` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// calendar_plan_detailsにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."calendar_plan_details` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."calendar_plan_details` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// calendar_select_roomにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."calendar_select_room` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."calendar_select_room` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		return true;
	}
}
?>
