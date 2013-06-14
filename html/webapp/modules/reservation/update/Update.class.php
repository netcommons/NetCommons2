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
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."reservation_location");
		if (!isset($metaColumns["use_auth_flag"]) && !isset($metaColumns["USE_AUTH_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_location"."` ADD `use_auth_flag` TINYINT unsigned NOT NULL default '0' AFTER `use_private_flag` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
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

		$sql = "SELECT RD.reserve_details_id, "
					. "R.room_id "
				. "FROM {reservation_reserve_details} RD "
				. "LEFT JOIN {reservation_reserve} R "
					. "ON RD.reserve_details_id = R.reserve_details_id "
				. "WHERE R.room_id != RD.room_id "
				. "OR R.room_id IS NULL "
				. "GROUP BY RD.reserve_details_id";
		$reserves = $this->db->execute($sql);
		foreach ($reserves as $reserve) {
			$whereArray = array(
				'reserve_details_id' => $reserve['reserve_details_id']
			);
			if (isset($reserve['room_id'])) {
				$updateColumns = array(
					'room_id' => $reserve['room_id']
				);
				$result = $this->db->updateExecute('reservation_reserve_details', $updateColumns, $whereArray, false);
			} else {
				$result = $this->db->deleteExecute('reservation_reserve_details', $whereArray);  
			}
			if ($result === false) {
				return false;
			}
		}

		// reservation_blockにカラムを追加
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix().'reservation_block');
		if(!isset($metaColumns['DISPLAY_TIMEFRAME'])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."reservation_block` ".
					" ADD `display_timeframe` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `display_type` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		// reservation_timeframeテーブルを追加
		$metaTables = $adodb->MetaTables();
		if (!in_array($this->db->getPrefix()."reservation_timeframe", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."reservation_timeframe` (" .
					" `timeframe_id`        int(11) NOT NULL default '0'," .
					" `timeframe_name`      varchar(255) default NULL," .
					" `start_time`          varchar(14) NOT NULL default ''," .
					" `end_time`            varchar(14) NOT NULL default ''," .
					" `timezone_offset`     float(3,1) NOT NULL default '0.0'," .
					" `timeframe_color`     varchar(16) NOT NULL default ''," .
					" `insert_time`        varchar(14) NOT NULL default ''," .
					" `insert_site_id`     varchar(40) NOT NULL default ''," .
					" `insert_user_id`     varchar(40) NOT NULL default ''," .
					" `insert_user_name`   varchar(255) NOT NULL default ''," .
					" `update_time`        varchar(14) NOT NULL default ''," .
					" `update_site_id`     varchar(40) NOT NULL default ''," .
					" `update_user_id`     varchar(40) NOT NULL default ''," .
					" `update_user_name`   varchar(255) NOT NULL default ''," .
					" PRIMARY KEY  (`timeframe_id`)" .
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		return true;
	}
}
?>
