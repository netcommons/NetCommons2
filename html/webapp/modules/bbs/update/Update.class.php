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
class Bbs_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// bbsにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."bbs` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// bbs_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."bbs_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_bbs_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "bbs_id") {
				$alter_table_bbs_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_bbs_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs_block` ADD INDEX ( `bbs_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// bbs_postにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."bbs_post` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs_post` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// bbs_post_bodyにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."bbs_post_body` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs_post_body` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// bbs_topicにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."bbs_topic` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs_topic` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// bbs_user_postにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."bbs_user_post` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_user_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
		if(isset($result['Key_name']) && $result['Key_name'] == "user_id") {
				$alter_table_user_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if(!$alter_table_user_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs_user_post`
						DROP INDEX `user_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."bbs_user_post` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// 件数表示で全てを選択されている場合、100件にする
		$sql = "UPDATE `".$this->db->getPrefix()."bbs_block` SET visible_row = 100 WHERE visible_row = 0";
		$result = $this->db->execute($sql);
		if($result === false) return false;

		return true;
	}
}
?>
