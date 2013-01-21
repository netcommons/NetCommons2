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
class Reservation_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// reservation_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."reservation_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// reservation_categoryにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."reservation_category` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_category_name_flag = true;
		$alter_table_display_sequence_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "category_name") {
				$alter_table_category_name_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "display_sequence") {
				$alter_table_display_sequence_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_category_name_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_category` ADD INDEX ( `category_name` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_display_sequence_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_category` ADD INDEX ( `display_sequence`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_category` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// reservation_locationにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."reservation_location` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_category_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "category_id") {
				$alter_table_category_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_category_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_location` ADD INDEX ( `category_id` , `display_sequence` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_location` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// reservation_location_detailsにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."reservation_location_details` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_location_details` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// reservation_location_roomsにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."reservation_location_rooms` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_location_rooms` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// reservation_reserveにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."reservation_reserve` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_reserve_details_id_flag = true;
		$alter_table_location_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "reserve_details_id") {
				$alter_table_reserve_details_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "location_id") {
				$alter_table_location_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_reserve_details_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_reserve` ADD INDEX ( `reserve_details_id`,`start_time_full`,`end_time_full`) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_location_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_reserve` ADD INDEX ( `location_id` ,`start_time_full` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_reserve` ADD INDEX ( `room_id`, `location_id`, `reserve_details_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// reservation_reserve_detailsにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."reservation_reserve_details` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_location_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "location_id") {
				$alter_table_location_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_location_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_reserve_details` ADD INDEX ( `location_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_reserve_details` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		return true;
	}
}
?>
