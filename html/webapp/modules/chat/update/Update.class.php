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
class Chat_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// chatにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."chat` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."chat` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// chat_contentsにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."chat_contents` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_chat_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "PRIMARY" &&
				$result['Seq_in_index'] == 1 && $result['Column_name'] == "block_id") {
				$alter_table_chat_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_chat_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."chat_contents` DROP PRIMARY KEY , ADD PRIMARY KEY ( `block_id` , `chat_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."chat_contents` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// chat_loginにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."chat_login` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_block_id_flag = true;
		$alter_table_block_id_2_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "block_id") {
				$alter_table_block_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "block_id_2") {
				$alter_table_block_id_2_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_block_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."chat_login` ADD INDEX ( `block_id`, `update_user_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_block_id_2_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."chat_login`
						ADD INDEX `block_id_2` ( `block_id`, `update_time` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."chat_login` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$sql = "SELECT block_id "
			. "FROM " . $this->db->getPrefix() . "chat_contents "
			. "GROUP BY block_id "
			. "HAVING COUNT(chat_id) != MAX(chat_id)";
		$chatBlocks = $this->db->execute($sql);
		foreach ($chatBlocks as $chatBlock) {
			$chatId = 1;
			$sql = "SELECT chat_id "
				. "FROM " . $this->db->getPrefix() . "chat_contents "
				. "WHERE block_id = ? "
				. "ORDER BY chat_id";
			$chatContents = $this->db->execute($sql, $chatBlock['block_id']);
			foreach ($chatContents as $chatContent) {
				$updateColumns = array(
					'chat_id' => $chatId
				);
				$whereColumns = array(
					'block_id' => $chatBlock['block_id'],
					'chat_id' => $chatContent['chat_id']
				);
				$result = $this->db->updateExecute('chat_contents', $updateColumns, $whereColumns, false);
				if ($result === false) {
					return false;
				}
				$chatId++;
			}
		}

		return true;
	}
}
?>
