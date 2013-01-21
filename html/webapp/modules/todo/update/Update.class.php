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
class Todo_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		$adodb = $this->db->getAdoDbObject();

		// todoにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."todo` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."todo` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// todo_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."todo_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."todo_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// todo_blockにカラムを追加
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."todo_block");
		if(!isset($metaColumns["USED_CATEGORY"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."todo_block` ".
					" ADD `used_category` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `default_sort` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// todo_taskにcategory_idを追加
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."todo_task");
		if(!isset($metaColumns["CATEGORY_ID"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."todo_task` ".
					" ADD `category_id` INT NOT NULL DEFAULT '0' AFTER `calendar_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// todo_taskにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."todo_task` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_todo_id_flag = true;
		$alter_table_todo_id2_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "todo_id") {
				$alter_table_todo_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "todo_id" && $result['Column_name'] == "category_id") {
				$alter_table_todo_id2_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_todo_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."todo_task` ADD INDEX ( `todo_id` , `category_id` , `task_sequence` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		} elseif ($alter_table_todo_id2_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."todo_task` DROP INDEX `todo_id` , ".
					" ADD INDEX `todo_id` ( `todo_id` , `category_id` , `task_sequence` );";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."todo_task` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaTables = $adodb->MetaTables();
		if (!in_array($this->db->getPrefix()."todo_category", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."todo_category` (" .
				" `category_id`        int(11) NOT NULL default '0'," .
				" `todo_id`            int(11) NOT NULL default '0'," .
				" `category_name`      varchar(255) default NULL," .
				" `display_sequence`   int(11) default NULL," .
				" `room_id`            int(11) NOT NULL default '0'," .
				" `insert_time`        varchar(14) NOT NULL default ''," .
				" `insert_site_id`     varchar(40) NOT NULL default ''," .
				" `insert_user_id`     varchar(40) NOT NULL default ''," .
				" `insert_user_name`   varchar(255) NOT NULL default ''," .
				" `update_time`        varchar(14) NOT NULL default ''," .
				" `update_site_id`     varchar(40) NOT NULL default ''," .
				" `update_user_id`     varchar(40) NOT NULL default ''," .
				" `update_user_name`   varchar(255) NOT NULL default ''," .
				" PRIMARY KEY  (`category_id`, `todo_id`)," .
				" KEY `room_id` (`room_id`)," .
				" KEY `todo_id` (`todo_id`,`display_sequence`)" .
				") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		return true;
	}
}
?>