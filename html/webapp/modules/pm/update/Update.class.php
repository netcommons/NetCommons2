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
class Pm_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// pm_filter_action_linkにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."pm_filter_action_link` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_insert_user_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "insert_user_id") {
				$alter_table_insert_user_id_flag = false;
			}
		}
		if($alter_table_insert_user_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pm_filter_action_link` ADD INDEX ( `insert_user_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// pm_message_receiverにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."pm_message_receiver` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_receiver_user_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "receiver_user_id") {
				$alter_table_receiver_user_id_flag = false;
			}
		}
		if($alter_table_receiver_user_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pm_message_receiver`
						DROP INDEX `idx_pm_message_receiver_user_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->db->getPrefix()."pm_message_receiver` ADD INDEX ( `receiver_user_id`, `delete_state`,`mailbox`,`read_state`,`importance_flag` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// pm_message_tag_linkにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."pm_message_tag_link` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_receiver_id_flag = true;
		$alter_table_insert_user_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "receiver_id") {
				$alter_table_receiver_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "insert_user_id") {
				$alter_table_insert_user_id_flag = false;
			}
		}
		if($alter_table_receiver_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pm_message_tag_link` ADD INDEX ( `receiver_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_insert_user_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pm_message_tag_link` ADD INDEX ( `insert_user_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		return true;
	}
}
?>
