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
class Whatsnew_Update extends Action
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
		if (!in_array($this->db->getPrefix()."whatsnew_user", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."whatsnew_user` (" .
					" `whatsnew_id` int(11) NOT NULL default 0," .
					" `user_id` varchar(40) NOT NULL," .
					" `room_id` int(11) default NULL," .
					" PRIMARY KEY (`user_id`, `whatsnew_id`)," .
					" KEY `whatsnew_id` (`whatsnew_id`)," .
					" KEY `user_id` (`user_id`)" .
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		if (!in_array($this->db->getPrefix()."whatsnew_select_room", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."whatsnew_select_room` (" .
					" `block_id` int(11) unsigned NOT NULL," .
					" `room_id` int(11) unsigned NOT NULL," .
					" PRIMARY KEY (`block_id`, `room_id`)" .
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew_user");
		if (!isset($metaColumns["room_id"]) && !isset($metaColumns["ROOM_ID"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_user"."` ADD `room_id` INT(11) NULL DEFAULT NULL AFTER `user_id` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew_block");
		if (!isset($metaColumns["select_room"]) && !isset($metaColumns["SELECT_ROOM"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_block"."` CHANGE `current_room` `select_room` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0 ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew_block");
		if (!isset($metaColumns["display_number"]) && !isset($metaColumns["DISPLAY_NUMBER"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_block"."` ADD `display_number` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `display_days`;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew_block");
		if (!isset($metaColumns["display_flag"]) && !isset($metaColumns["DISPLAY_FLAG"])){
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_block"."` ADD `display_flag` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `display_type` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew_block");
		if (!isset($metaColumns["myroom_flag"]) && !isset($metaColumns["MYROOM_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_block"."` ADD `myroom_flag` TINYINT(1) NULL DEFAULT 0 AFTER `select_room` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew");
		if (isset($metaColumns["UNIQUE_ID"]) && $metaColumns["UNIQUE_ID"]->type != "varchar") {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew"."` CHANGE `unique_id` `unique_id` VARCHAR(40) NOT NULL DEFAULT '0' ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew");
		if (!isset($metaColumns["count_num"]) && !isset($metaColumns["COUNT_NUM"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew"."` ADD `count_num` INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `parameters` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."whatsnew");
		if (!isset($metaColumns["child_update_time"]) && !isset($metaColumns["CHILD_UPDATE_TIME"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew"."` ADD `child_update_time` VARCHAR( 14 ) NULL DEFAULT NULL AFTER `count_num` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$sql = "UPDATE {whatsnew} SET child_update_time=update_time ";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			// count_numが０以上のものがあれば、child_update_timeをセット
			$where_params = array(
				"count_num!=0" => null
			);
			$whatsnews = $this->db->selectExecute("whatsnew", $where_params);
			foreach($whatsnews as $whatsnew) {
				// 日誌のみ新着の日付振り替え処理を実装
				$post_where_params = array(
					"post_id" => $whatsnew['unique_id']
				);
				$journal_post = $this->db->selectExecute("journal_post", $post_where_params, null, 1);
				if(isset($journal_post[0]) && $journal_post[0]['journal_date'] != "") {
					$whatsnew['insert_time'] = $journal_post[0]['journal_date'];
					$whatsnew['update_time'] = $journal_post[0]['journal_date'];
				}
				$params = array("child_update_time"=> $whatsnew['update_time'], "count_num"=> $whatsnew['count_num'], "insert_time" => $whatsnew['insert_time'], "update_time" => $whatsnew['update_time']);
				$result = $this->db->updateExecute("whatsnew", $params, array("module_id"=>$whatsnew['module_id'], "unique_id"=>$whatsnew['unique_id']));

				// 同じroom_id,module_id,unique_idのデータが2件以上あったら
				// 古いものを削除
				$results = $this->db->selectExecute("whatsnew", array("room_id"=>$whatsnew['room_id'], "module_id"=>$whatsnew['module_id'], "unique_id"=>$whatsnew['unique_id']), array("update_time"=> "DESC"));
				if(isset($results[1]))
					$result = $this->db->deleteExecute("whatsnew", array("whatsnew_id"=>$results[1]['whatsnew_id']));
			}
			if (isset($metaColumns["child_flag"]) || isset($metaColumns["CHILD_FLAG"])) {

				$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew` DROP `child_flag`";
				$result = $this->db->execute($sql);
				if ($result === false) {
					return false;
				}
			}

		}

		// add index whatsnew
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."whatsnew` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$room_id_alter_table_flag = true;
		$unique_id_alter_table_flag = true;
		$insert_time_alter_table_flag = true;
		$child_update_time_alter_table_flag = true;
		$module_id_alter_table_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$room_id_alter_table_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "unique_id") {
				$unique_id_alter_table_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "insert_time") {
				$insert_time_alter_table_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "child_update_time") {
				$child_update_time_alter_table_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "module_id") {
				$module_id_alter_table_flag = false;
			}
		}
		if($room_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew`
						ADD INDEX ( `room_id` , `insert_user_id` , `module_id` , `insert_time` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($unique_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew` ADD INDEX ( `unique_id`,`module_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($insert_time_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew` ADD INDEX ( `insert_time`,`whatsnew_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($child_update_time_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew` ADD INDEX ( `child_update_time`,`insert_time`,`whatsnew_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($module_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew` ADD INDEX ( `module_id`,`room_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// add index whatsnew_block
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."whatsnew_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$room_id_alter_table_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$room_id_alter_table_flag = false;
			}
		}
		if($room_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_block`
						ADD INDEX ( `room_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// add index whatsnew_select_room
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."whatsnew_select_room` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$room_id_alter_table_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$room_id_alter_table_flag = false;
			}
		}
		if($room_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_select_room`
						ADD INDEX ( `room_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// add index whatsnew_user
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."whatsnew_user` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$room_id_alter_table_flag = true;
		$whatsnew_id_alter_table_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "whatsnew_id") {
				$whatsnew_id_alter_table_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$room_id_alter_table_flag = false;
			}
		}
		if(!$whatsnew_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_user`
						DROP INDEX `whatsnew_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($room_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."whatsnew_user`
						ADD INDEX ( `room_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$sql = "SELECT WU.whatsnew_id, "
					. "W.room_id "
				. "FROM {whatsnew_user} WU "
				. "LEFT JOIN {whatsnew} W "
					. "ON WU.whatsnew_id = W.whatsnew_id "
				. "WHERE WU.room_id IS NULL "
				. "GROUP BY WU.whatsnew_id "
				. "ORDER BY W.room_id";
		$whatsnews = $this->db->execute($sql);
		$roomId = null;
		$targetIds = array();
		foreach ($whatsnews as $whatsnew) {
			if (!isset($whatsnew['room_id'])) {
				$whatsnew['room_id'] = 'NULL';
			}

			$roomId = $whatsnew['room_id'];
			$targetIds[$roomId][] = $whatsnew['whatsnew_id'];
		}

		foreach ($targetIds as $roomId => $whatsnewIds) {
			$whereClause = 'WHERE whatsnew_id IN (' . implode(',', $whatsnewIds) . ')';
			$bindValues = array();
			if ($roomId === 'NULL') {
				$sql = "DELETE FROM {whatsnew_user} "
						. $whereClause;
			} else {
				$sql = "UPDATE {whatsnew_user} SET "
						. "room_id = ? "
						. $whereClause;
				$bindValues = array(
					$roomId
				);
			}

			$result = $this->db->execute($sql, $bindValues);
			if ($result === false) {
				return false;
			}
		}

		return true;
	}
}
?>
