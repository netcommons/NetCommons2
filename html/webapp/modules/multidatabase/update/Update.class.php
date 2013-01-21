<?php
/**
 * モジュールアップデートクラス
 * 　　css_filesにカラム追加
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Update extends Action
{
	var $module_id = null;
	//使用コンポーネントを受け取るため
	var $db = null;
	var $modulesView = null;

	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."multidatabase_block");
		if(!isset($metaColumns["DEFAULT_SORT"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_block`
						ADD `default_sort` VARCHAR( 11 ) NOT NULL DEFAULT '' AFTER `visible_item` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."multidatabase_metadata");
		if(!isset($metaColumns["FILE_PASSWORD_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_metadata`
						ADD `file_password_flag` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `sort_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."multidatabase_metadata");
		if(!isset($metaColumns["FILE_COUNT_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_metadata`
						ADD `file_count_flag` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `file_password_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."multidatabase_file");
		if(!isset($metaColumns["FILE_PASSWORD"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_file`
						ADD `file_password` VARCHAR( 255 ) DEFAULT NULL AFTER `file_name` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."multidatabase_file");
		if(!isset($metaColumns["DOWNLOAD_COUNT"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_file`
						ADD `download_count` INT( 11 ) DEFAULT '0' AFTER `file_password` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$sql = "UPDATE `".$this->db->getPrefix()."uploads`
						SET `action_name` = 'common_download_main' WHERE `action_name` = 'multidatabase_action_main_filedownload' AND `module_id` = '".$this->module_id."' ;";
		$result = $this->db->execute($sql);
		if($result === false) return false;

		$sql = "SELECT metadata_content_id, content FROM `".$this->db->getPrefix()."multidatabase_metadata_content`,
						`".$this->db->getPrefix()."multidatabase_metadata`
						WHERE `".$this->db->getPrefix()."multidatabase_metadata_content`.`content` LIKE '%?action=common_download_main%'
						AND `".$this->db->getPrefix()."multidatabase_metadata_content`.`metadata_id` = `".$this->db->getPrefix()."multidatabase_metadata`.`metadata_id`
						AND ( `".$this->db->getPrefix()."multidatabase_metadata`.`type`=5 OR `".$this->db->getPrefix()."multidatabase_metadata`.`type`=0 ) ;";
		$contents = $this->db->execute($sql);
		if($contents === false) return false;
		if(!empty($contents)) {
			foreach($contents as $content) {
				$str_arr = explode("&", $content['content']);
				$param = array("content" => "?action=multidatabase_action_main_filedownload&".$str_arr[1]);
				$where_param = array("metadata_content_id" => $content['metadata_content_id']);
				$result = $this->db->updateExecute("multidatabase_metadata_content", $param, $where_param, false);
				if($result === false) return false;
			}
		}

		$sql = "SELECT content FROM `".$this->db->getPrefix()."multidatabase_metadata_content`,
						`".$this->db->getPrefix()."multidatabase_metadata`
						WHERE `".$this->db->getPrefix()."multidatabase_metadata_content`.`metadata_id` = `".$this->db->getPrefix()."multidatabase_metadata`.`metadata_id`
						AND ( `".$this->db->getPrefix()."multidatabase_metadata`.`type`=5 OR `".$this->db->getPrefix()."multidatabase_metadata`.`type`=0 ) ;";
		$contents = $this->db->execute($sql);
		if(!empty($contents)) {
			foreach($contents as $content) {
				if(!empty($content['content'])) {
					$str_arr = explode("&", $content['content']);
					if(!empty($str_arr[1]) && strpos($str_arr[1], "upload_id=") !== false) {
						$upload_id = intval(str_replace("upload_id=","", $str_arr[1]));
						if(empty($upload_id)) {
							continue;
						}
						$param = array("action_name" => "multidatabase_action_main_filedownload");
						$where_param = array(
							"upload_id" => $upload_id,
							"module_id" => $this->module_id,
							"action_name" => "common_download_main"
						);
						$result = $this->db->updateExecute("uploads", $param, $where_param, false);
						if($result === false) return false;
					}
				}
			}
		}

		// multidatabaseにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."multidatabase` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// multidatabase_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."multidatabase_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_block_id_flag = true;
		$alter_table_multidatabase_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "PRIMARY") {
				$alter_table_block_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "multidatabase_id") {
				$alter_table_multidatabase_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_block_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_block` ADD PRIMARY KEY ( `block_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_multidatabase_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_block` ADD INDEX ( `multidatabase_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// multidatabase_commentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."multidatabase_comment` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_content_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "content_id") {
				$alter_table_content_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_content_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_comment` ADD INDEX ( `content_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_comment` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// multidatabase_contentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."multidatabase_content` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_multidatabase_id_flag = true;
		$alter_table_room_id_flag = true;
		$alter_table_insert_time_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "multidatabase_id") {
				$alter_table_multidatabase_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "multidatabase_id" && $result['Column_name'] == "insert_time") {
				$alter_table_insert_time_flag = false;
			}
		}
		if($alter_table_multidatabase_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_content` ADD INDEX ( `multidatabase_id`, `display_sequence` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_content` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if ($alter_table_insert_time_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_content` DROP INDEX `multidatabase_id`," .
					" ADD INDEX `multidatabase_id` ( `multidatabase_id` , `display_sequence` , `insert_time` )";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// multidatabase_fileにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."multidatabase_file` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_upload_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "upload_id") {
				$alter_table_upload_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_upload_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_file` ADD INDEX ( `upload_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_file` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// multidatabase_metadataにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."multidatabase_metadata` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_multidatabase_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "multidatabase_id") {
				$alter_table_multidatabase_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_multidatabase_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_metadata` ADD INDEX ( `multidatabase_id`, `display_pos` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_metadata` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// multidatabase_metadata_contentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."multidatabase_metadata_content` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_content_id_flag = true;
		$alter_table_content_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "content_id") {
				$alter_table_content_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "content") {
				$alter_table_content_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_content_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_metadata_content` ADD INDEX ( `content_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_content_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_metadata_content` ADD FULLTEXT ( `content`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."multidatabase_metadata_content` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// 件数表示で全てを選択されている場合、100件にする
		$sql = "UPDATE `".$this->db->getPrefix()."multidatabase_block` SET visible_item = 100 WHERE visible_item = 0";
		$result = $this->db->execute($sql);
		if($result === false) return false;

		// 日付タイプのメタデータは検索対象としないようにする
		$sql = "UPDATE `".$this->db->getPrefix()."multidatabase_metadata` SET `search_flag` = 0 WHERE `type` = ".MULTIDATABASE_META_TYPE_DATE;
		$result = $this->db->execute($sql);
		if($result === false) return false;

		return true;
	}
}
?>
