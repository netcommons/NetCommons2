<?php
/**
 * モジュールアップデートクラス
 * テーブル項目追加
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Update extends Action
{
	var $module_id = null;
	//使用コンポーネントを受け取るため
	var $db = null;
	var $modulesView = null;

	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."journal");
		if(!isset($metaColumns["COMMENT_AGREE_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal`
						ADD `comment_agree_flag` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `agree_mail_body` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."journal");
		if(!isset($metaColumns["COMMENT_AGREE_MAIL_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal`
						ADD `comment_agree_mail_flag` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `comment_agree_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."journal");
		if(!isset($metaColumns["COMMENT_AGREE_MAIL_SUBJECT"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal`
						ADD `comment_agree_mail_subject` VARCHAR( 255 ) DEFAULT NULL AFTER `comment_agree_mail_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."journal");
		if(!isset($metaColumns["COMMENT_AGREE_MAIL_BODY"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal`
						ADD `comment_agree_mail_body` TEXT AFTER `comment_agree_mail_subject` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."journal");
		if(!isset($metaColumns["SNS_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal`
						ADD `sns_flag` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `comment_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// journalにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."journal` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// journal_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."journal_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// journal_postにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."journal_post` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		$alter_table_category_id_flag = true;
		$alter_table_journal_id_2_flag = true;
		$alter_table_insert_time_flag = true;
		$alter_table_update_time_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "category_id") {
				$alter_table_category_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "journal_id_2") {
				$alter_table_journal_id_2_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "insert_time") {
				$alter_table_insert_time_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "update_time") {
				$alter_table_update_time_flag = false;
			}
		}
		if($alter_table_journal_id_2_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_post`
						DROP INDEX `journal_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_post`
						ADD INDEX `journal_id_2` ( `journal_id`,`journal_date`,`insert_time` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_category_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_post` ADD INDEX ( `category_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_post` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if(!$alter_table_insert_time_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_post`
						DROP INDEX `insert_time` ;";
			//$result = $this->db->execute($sql);
			//if($result === false) return false;
		}
		if(!$alter_table_update_time_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_post`
						DROP INDEX `update_time` ;";
			//$result = $this->db->execute($sql);
			//if($result === false) return false;
		}

		// journal_categoryにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."journal_category` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		$alter_table_journal_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "journal_id") {
				$alter_table_journal_id_flag = false;
			}
		}
		if($alter_table_journal_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_category`
						DROP INDEX `display_sequence` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_category`
						ADD INDEX `journal_id` ( `journal_id`,`display_sequence` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."journal_category` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// 件数表示で全てを選択されている場合、100件にする
		$sql = "UPDATE `".$this->db->getPrefix()."journal_block` SET visible_item = 100 WHERE visible_item = 0";
		$result = $this->db->execute($sql);
		if($result === false) return false;

		return true;
	}
}
?>
