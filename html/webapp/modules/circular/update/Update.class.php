<?php

/**
 * アップデートクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Update extends Action
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
		// circular_fileテーブル
		if (in_array($this->db->getPrefix()."circular_file", $metaTables)) {
			$sql = "DROP TABLE `".$this->db->getPrefix()."circular_file;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// circular_choiceテーブル
		$tableExists = false;
		$roomIdColumnExists = false;
		$tableName = $this->db->getPrefix() . 'circular_choice';
		if (in_array($tableName, $metaTables)) {
			$tableExists = true;

			$metaColumns = $adodb->MetaColumns($tableName);
			if (isset($metaColumns['ROOM_ID'])) {
				$roomIdColumnExists = true;
			}
		}
		$reCreate = ($tableExists && !$roomIdColumnExists);

		if ($reCreate) {
			$temporaryTableNumber = 1;
			$temporaryTableName = $this->db->getPrefix() . 'circular_choice_' . $temporaryTableNumber;
			while (in_array($temporaryTableName, $metaTables)) {
				$temporaryTableNumber++;
				$temporaryTableName = $this->db->getPrefix() . 'circular_choice' . $temporaryTableNumber;
			}
			$sql = 'RENAME TABLE {circular_choice} '
					. 'TO ' . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$tableExists = false;
		}

		if (!$tableExists) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."circular_choice` (" .
					" `choice_id` int(11) NOT NULL default '0'," .
					" `circular_id` int(11) NOT NULL default '0'," .
					" `choice_sequence` int(11) NOT NULL default '0'," .
					" `choice_value` text," .
					" `room_id` int(11) NOT NULL default '0'," .
					" `insert_time` varchar(14) NOT NULL default ''," .
					" `insert_site_id` varchar(40) NOT NULL default ''," .
					" `insert_user_id` varchar(40) NOT NULL default ''," .
					" `insert_user_name` varchar(255) NOT NULL default ''," .
					" `update_time` varchar(14) NOT NULL default ''," .
					" `update_site_id` varchar(40) NOT NULL default ''," .
					" `update_user_id` varchar(40) NOT NULL default ''," .
					" `update_user_name` varchar(255) NOT NULL default ''," .
					" PRIMARY KEY (`choice_id`)," .
					" KEY `room_id` (`room_id`)" .
					");";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		if ($reCreate) {
			$sql = "INSERT INTO {circular_choice} ("
						. "`choice_id`, "
						. "`circular_id`, "
						. "`choice_sequence`, "
						. "`choice_value`, "
						. "`room_id`, "
						. "`insert_time`, "
						. "`insert_site_id`, "
						. "`insert_user_id`, "
						. "`insert_user_name`, "
						. "`update_time`, "
						. "`update_site_id`, "
						. "`update_user_id`, "
						. "`update_user_name`) ".
					" SELECT CC.choice_id,"
						. "CC.circular_id,"
						. "CC.choice_sequence,"
						. "CC.choice_value,"
						. "C.room_id,"
						. "C.insert_time,"
						. "C.insert_site_id,"
						. "C.insert_user_id,"
						. "C.insert_user_name,"
						. "C.update_time,"
						. "C.update_site_id,"
						. "C.update_user_id,"
						. "C.update_user_name "
					. "FROM " . $temporaryTableName . " CC "
					. "INNER JOIN {circular} C "
						. "ON CC.circular_id = C.circular_id";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "DROP TABLE " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// circular_postscriptテーブル
		$tableExists = false;
		$roomIdColumnExists = false;
		$tableName = $this->db->getPrefix() . 'circular_postscript';
		if (in_array($tableName, $metaTables)) {
			$tableExists = true;

			$metaColumns = $adodb->MetaColumns($tableName);
			if (isset($metaColumns['ROOM_ID'])) {
				$roomIdColumnExists = true;
			}
		}
		$reCreate = ($tableExists && !$roomIdColumnExists);

		if ($reCreate) {
			$temporaryTableNumber = 1;
			$temporaryTableName = $this->db->getPrefix() . 'circular_postscript_' . $temporaryTableNumber;
			while (in_array($temporaryTableName, $metaTables)) {
				$temporaryTableNumber++;
				$temporaryTableName = $this->db->getPrefix() . 'circular_postscript_' . $temporaryTableNumber;
			}
			$sql = 'RENAME TABLE {circular_postscript} '
					. 'TO ' . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$tableExists = false;
		}

		if (!$tableExists) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."circular_postscript` (" .
					" `postscript_id` int(11) NOT NULL default '0'," .
					" `circular_id` int(11) NOT NULL default '0'," .
					" `postscript_sequence` int(11) NOT NULL default '0'," .
					" `postscript_value` text," .
					" `room_id` int(11) NOT NULL default '0'," .
					" `insert_time` varchar(14) NOT NULL default ''," .
					" `insert_site_id` varchar(40) NOT NULL default ''," .
					" `insert_user_id` varchar(40) NOT NULL default ''," .
					" `insert_user_name` varchar(255) NOT NULL default ''," .
					" `update_time` varchar(14) NOT NULL default ''," .
					" `update_site_id` varchar(40) NOT NULL default ''," .
					" `update_user_id` varchar(40) NOT NULL default ''," .
					" `update_user_name` varchar(255) NOT NULL default ''," .
					" PRIMARY KEY (`postscript_id`)," .
					" KEY `room_id` (`room_id`)" .
					");";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		if ($reCreate) {
			$sql = "INSERT INTO {circular_postscript} ("
						. "`postscript_id`, "
						. "`circular_id`, "
						. "`postscript_sequence`, "
						. "`postscript_value`, "
						. "`room_id`, "
						. "`insert_time`, "
						. "`insert_site_id`, "
						. "`insert_user_id`, "
						. "`insert_user_name`, "
						. "`update_time`, "
						. "`update_site_id`, "
						. "`update_user_id`, "
						. "`update_user_name`) ".
					" SELECT P.postscript_id,"
						. "P.circular_id,"
						. "P.postscript_sequence,"
						. "P.postscript_value,"
						. "C.room_id,"
						. "P.insert_time,"
						. "P.insert_site_id,"
						. "P.insert_user_id,"
						. "P.insert_user_name,"
						. "P.update_time,"
						. "P.update_site_id,"
						. "P.update_user_id,"
						. "P.update_user_name "
					. "FROM " . $temporaryTableName . " P "
					. "INNER JOIN {circular} C "
						. "ON P.circular_id = C.circular_id";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "DROP TABLE " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// circular_groupテーブル
		$tableExists = false;
		$roomIdColumnExists = false;
		$tableName = $this->db->getPrefix() . 'circular_group';
		if (in_array($tableName, $metaTables)) {
			$tableExists = true;

			$sql = "SHOW INDEX FROM {circular_group};";
			$indexes = $this->db->execute($sql);
			if ($indexes === false) {
				return false;
			}
			foreach($indexes as $index) {
				if ($index['Key_name'] == 'room_id') {
					$roomIdColumnExists = true;
					break;
				}
			}
		}
		$reCreate = ($tableExists && !$roomIdColumnExists);

		if ($reCreate) {
			$temporaryTableNumber = 1;
			$temporaryTableName = $this->db->getPrefix() . 'circular_group_' . $temporaryTableNumber;
			while (in_array($temporaryTableName, $metaTables)) {
				$temporaryTableNumber++;
				$temporaryTableName = $this->db->getPrefix() . 'circular_group_' . $temporaryTableNumber;
			}
			$sql = 'RENAME TABLE {circular_group} '
					. 'TO ' . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$tableExists = false;
		}

		if (!$tableExists) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."circular_group` (" .
					" `group_id` int(11) NOT NULL default '0'," .
					" `user_id` varchar(40) NOT NULL default ''," .
					" `group_name` varchar(255) NOT NULL default ''," .
					" `group_member` text," .
					" `room_id` int(11) NOT NULL default '0'," .
					" `insert_time` varchar(14) NOT NULL default ''," .
					" `insert_site_id` varchar(40) NOT NULL default ''," .
					" `insert_user_id` varchar(40) NOT NULL default ''," .
					" `insert_user_name` varchar(255) NOT NULL default ''," .
					" `update_time` varchar(14) NOT NULL default ''," .
					" `update_site_id` varchar(40) NOT NULL default ''," .
					" `update_user_id` varchar(40) NOT NULL default ''," .
					" `update_user_name` varchar(255) NOT NULL default ''," .
					" PRIMARY KEY (`group_id`)," .
					" KEY `room_id` (`room_id`)," .
					" KEY `user_id` (`user_id`)" .
					");";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		if ($reCreate) {
			$sql = "INSERT INTO {circular_group} ("
						. "`group_id`, "
						. "`user_id`, "
						. "`group_name`, "
						. "`group_member`, "
						. "`room_id`, "
						. "`insert_time`, "
						. "`insert_site_id`, "
						. "`insert_user_id`, "
						. "`insert_user_name`, "
						. "`update_time`, "
						. "`update_site_id`, "
						. "`update_user_id`, "
						. "`update_user_name`) ".
					" SELECT G.group_id,"
						. "G.user_id,"
						. "G.group_name,"
						. "G.group_member,"
						. "P.room_id,"
						. "G.insert_time,"
						. "G.insert_site_id,"
						. "G.insert_user_id,"
						. "G.insert_user_name,"
						. "G.update_time,"
						. "G.update_site_id,"
						. "G.update_user_id,"
						. "G.update_user_name "
					. "FROM " . $temporaryTableName . " G "
					. "INNER JOIN {pages} P "
						. "ON P.space_type = ? "
						. "AND P.private_flag = ? "
						. "AND P.thread_num = ? "
						. "AND G.user_id = P.insert_user_id "
						. "AND P.default_entry_flag = ?;";
			$inputs = array(
				_SPACE_TYPE_GROUP,
				_ON,
				0,
				_OFF
			);
			$result = $this->db->execute($sql, $inputs);
			if ($result === false) {
				return false;
			}

			$sql = "DROP TABLE " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// circular_configテーブル
		if (in_array($this->db->getPrefix()."circular_mail_config", $metaTables)) {
			if (!in_array($this->db->getPrefix()."circular_config", $metaTables)) {
				$sql = "CREATE TABLE {circular_config} (" .
						" `room_id` int(11) NOT NULL default '0'," .
						" `create_authority` tinyint(1) NOT NULL default '0'," .
						" `mail_subject` varchar(255) NOT NULL default ''," .
						" `mail_body` text default ''," .
						" `insert_time` varchar(14) NOT NULL default ''," .
						" `insert_site_id` varchar(40) NOT NULL default ''," .
						" `insert_user_id` varchar(40) NOT NULL default ''," .
						" `insert_user_name` varchar(255) NOT NULL default ''," .
						" `update_time` varchar(14) NOT NULL default ''," .
						" `update_site_id` varchar(40) NOT NULL default ''," .
						" `update_user_id` varchar(40) NOT NULL default ''," .
						" `update_user_name` varchar(255) NOT NULL default ''," .
						" PRIMARY KEY (`room_id`)" .
						");";
				$result = $this->db->execute($sql);
				if ($result === false) {
					return false;
				}
			}
			$sql = "SELECT MAX(C.block_id) AS block_id, "
						. "MIN(B.create_authority) AS create_authority "
					. "FROM {circular_mail_config} C "
					. "INNER JOIN {circular_block} B "
					. "ON C.block_id = B.block_id "
					. "GROUP BY C.room_id;";
			$circularConfigs = $this->db->execute($sql);
			if ($circularConfigs === false) {
				return false;
			}
			$blockIdInStatements = array();
			foreach($circularConfigs as $circularConfig) {
				$sql = "INSERT INTO {circular_config} ("
							. "`room_id`, "
							. "`create_authority`, "
							. "`mail_subject`, "
							. "`mail_body`, "
							. "`insert_time`, "
							. "`insert_site_id`, "
							. "`insert_user_id`, "
							. "`insert_user_name`, "
							. "`update_time`, "
							. "`update_site_id`, "
							. "`update_user_id`, "
							. "`update_user_name`) ".
						" SELECT room_id,"
							. $circularConfig['create_authority'] . ","
							. "mail_subject,"
							. "mail_body,"
							. "insert_time,"
							. "insert_site_id,"
							. "insert_user_id,"
							. "insert_user_name,"
							. "update_time,"
							. "update_site_id,"
							. "update_user_id,"
							. "update_user_name "
						. "FROM {circular_mail_config} "
						. "WHERE block_id = ?;";
				$result = $this->db->execute($sql, array($circularConfig['block_id']));
				if ($result === false) {
					return false;
				}
			}

			$sql = "DROP TABLE `".$this->db->getPrefix()."circular_mail_config;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// circular_blockテーブル
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."circular_block");
		if (!isset($metaColumns["block_type"]) && !isset($metaColumns["BLOCK_TYPE"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."circular_block"."` ADD `block_type` TINYINT(1) NOT NULL DEFAULT '0' AFTER `create_authority` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		if (isset($metaColumns['PAGE_ID'])) {
			$temporaryTableNumber = 1;
			$temporaryTableName = $this->db->getPrefix() . 'circular_block_' . $temporaryTableNumber;
			while (in_array($temporaryTableName, $metaTables)) {
				$temporaryTableNumber++;
				$temporaryTableName = $this->db->getPrefix() . 'circular_block_' . $temporaryTableNumber;
			}
			$sql = 'RENAME TABLE {circular_block} '
					. 'TO ' . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "CREATE TABLE {circular_block} ( "
					. "`block_id` int(11) NOT NULL default '0',"
					. "`visible_row` int(11) NOT NULL default '0',"
					. "`block_type` tinyint(1) NOT NULL default '0',"
					. "`room_id` int(11) NOT NULL default '0',"
					. "`insert_time` varchar(14) NOT NULL default '',"
					. "`insert_site_id` varchar(40) NOT NULL default '',"
					. "`insert_user_id` varchar(40) NOT NULL default '',"
					. "`insert_user_name` varchar(255) NOT NULL default '',"
					. "`update_time` varchar(14) NOT NULL default '',"
					. "`update_site_id` varchar(40) NOT NULL default '',"
					. "`update_user_id` varchar(40) NOT NULL default '',"
					. "`update_user_name` varchar(255) NOT NULL default '',"
					. "PRIMARY KEY (`block_id`),"
					. "KEY `room_id` (`room_id`)"
					. ");";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "INSERT INTO {circular_block} ("
						. "`block_id`, "
						. "`visible_row`, "
						. "`block_type`, "
						. "`room_id`, "
						. "`insert_time`, "
						. "`insert_site_id`, "
						. "`insert_user_id`, "
						. "`insert_user_name`, "
						. "`update_time`, "
						. "`update_site_id`, "
						. "`update_user_id`, "
						. "`update_user_name`) ".
					" SELECT block_id,"
						. "visible_row,"
						. "block_type,"
						. "room_id,"
						. "insert_time,"
						. "insert_site_id,"
						. "insert_user_id,"
						. "insert_user_name,"
						. "update_time,"
						. "update_site_id,"
						. "update_user_id,"
						. "update_user_name "
					. "FROM " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "DROP TABLE " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// circularテーブル
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."circular");
		if (!isset($metaColumns["reply_type"]) && !isset($metaColumns["REPLY_TYPE"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."circular"."` ADD `reply_type` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		if (!isset($metaColumns["seen_option"]) && !isset($metaColumns["SEEN_OPTION"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."circular"."` ADD `seen_option` TINYINT(1) NOT NULL DEFAULT '0' AFTER `reply_type` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		if (isset($metaColumns["PAGE_ID"])) {
			$temporaryTableNumber = 1;
			$temporaryTableName = $this->db->getPrefix() . 'circular_' . $temporaryTableNumber;
			while (in_array($temporaryTableName, $metaTables)) {
				$temporaryTableNumber++;
				$temporaryTableName = $this->db->getPrefix() . 'circular_' . $temporaryTableNumber;
			}
			$sql = 'RENAME TABLE {circular} '
					. 'TO ' . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "CREATE TABLE {circular} ( "
					. "`circular_id` int(11) NOT NULL default '0',"
					. "`circular_subject` varchar(255) NOT NULL default '',"
					. "`circular_body` text,"
					. "`icon_name` varchar(40) NOT NULL default '',"
					. "`post_user_id` varchar(40) NOT NULL default '',"
					. "`period` varchar(14) NOT NULL default '',"
					. "`status` tinyint(1) NOT NULL default '0',"
					. "`reply_type` tinyint(1) NOT NULL default '0',"
					. "`seen_option` tinyint(1) NOT NULL default '0',"
					. "`room_id` int(11) NOT NULL default '0',"
					. "`insert_time` varchar(14) NOT NULL default '',"
					. "`insert_site_id` varchar(40) NOT NULL default '',"
					. "`insert_user_id` varchar(40) NOT NULL default '',"
					. "`insert_user_name` varchar(255) NOT NULL default '',"
					. "`update_time` varchar(14) NOT NULL default '',"
					. "`update_site_id` varchar(40) NOT NULL default '',"
					. "`update_user_id` varchar(40) NOT NULL default '',"
					. "`update_user_name` varchar(255) NOT NULL default '',"
					. "PRIMARY KEY (`circular_id`),"
					. "KEY `room_id` (`room_id`),"
					. "KEY `post_user_id` (`post_user_id`),"
					. "KEY `insert_time` (`insert_time`)"
					. ");";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "INSERT INTO {circular} ("
						. "`circular_id`, "
						. "`circular_subject`, "
						. "`circular_body`, "
						. "`icon_name`, "
						. "`post_user_id`, "
						. "`period`, "
						. "`status`, "
						. "`reply_type`, "
						. "`seen_option`, "
						. "`room_id`, "
						. "`insert_time`, "
						. "`insert_site_id`, "
						. "`insert_user_id`, "
						. "`insert_user_name`, "
						. "`update_time`, "
						. "`update_site_id`, "
						. "`update_user_id`, "
						. "`update_user_name`) ".
					" SELECT circular_id,"
						. "circular_subject,"
						. "circular_body,"
						. "icon_name,"
						. "post_user_id,"
						. "period,"
						. "status,"
						. "reply_type,"
						. "seen_option,"
						. "room_id,"
						. "insert_time,"
						. "insert_site_id,"
						. "insert_user_id,"
						. "insert_user_name,"
						. "update_time,"
						. "update_site_id,"
						. "update_user_id,"
						. "update_user_name "
					. "FROM " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "DROP TABLE " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// circular_userテーブル
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."circular_user");
		if (!isset($metaColumns["reply_choice"]) && !isset($metaColumns["REPLY_CHOICE"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."circular_user"."` ADD `reply_choice` VARCHAR(255) NOT NULL default '' AFTER `reply_body` ;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		if (isset($metaColumns["PAGE_ID"])) {
			$temporaryTableNumber = 1;
			$temporaryTableName = $this->db->getPrefix() . 'circular_user_' . $temporaryTableNumber;
			while (in_array($temporaryTableName, $metaTables)) {
				$temporaryTableNumber++;
				$temporaryTableName = $this->db->getPrefix() . 'circular_user' . $temporaryTableNumber;
			}
			$sql = 'RENAME TABLE {circular_user} '
					. 'TO ' . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "CREATE TABLE {circular_user} ( "
					. "`circular_id` int(11) NOT NULL default '0',"
					. "`receive_user_id` varchar(40) NOT NULL default '',"
					. "`reply_flag` tinyint(1) NOT NULL default '0',"
					. "`reply_body` text,"
					. "`reply_choice` varchar(255) NOT NULL default '',"
					. "`room_id` int(11) NOT NULL default '0',"
					. "`insert_time` varchar(14) NOT NULL default '',"
					. "`insert_site_id` varchar(40) NOT NULL default '',"
					. "`insert_user_id` varchar(40) NOT NULL default '',"
					. "`insert_user_name` varchar(255) NOT NULL default '',"
					. "`update_time` varchar(14) NOT NULL default '',"
					. "`update_site_id` varchar(40) NOT NULL default '',"
					. "`update_user_id` varchar(40) NOT NULL default '',"
					. "`update_user_name` varchar(255) NOT NULL default '',"
					. "PRIMARY KEY (`circular_id`,`receive_user_id`),"
					. "KEY `room_id` (`room_id`)"
					. ");";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "INSERT INTO {circular_user} ("
						. "`circular_id`, "
						. "`receive_user_id`, "
						. "`reply_flag`, "
						. "`reply_body`, "
						. "`reply_choice`, "
						. "`room_id`, "
						. "`insert_time`, "
						. "`insert_site_id`, "
						. "`insert_user_id`, "
						. "`insert_user_name`, "
						. "`update_time`, "
						. "`update_site_id`, "
						. "`update_user_id`, "
						. "`update_user_name`) ".
					" SELECT circular_id,"
						. "receive_user_id,"
						. "reply_flag,"
						. "reply_body,"
						. "reply_choice,"
						. "room_id,"
						. "insert_time,"
						. "insert_site_id,"
						. "insert_user_id,"
						. "insert_user_name,"
						. "update_time,"
						. "update_site_id,"
						. "update_user_id,"
						. "update_user_name "
					. "FROM " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "DROP TABLE " . $temporaryTableName;
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		return true;
	}
}
?>