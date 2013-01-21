<?php
/**
 * モジュールアップデートアクションクラス
 * 　　登録者本人メール送信フラグカラムの追加
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $dbObject = null;

	/**
	 * モジュールアップデートアクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$adoConnection = $this->dbObject->getAdoDbObject();
		$metaColumns = $adoConnection->MetaColumns($this->dbObject->getPrefix() . 'registration');
		if (!isset($metaColumns['REGIST_USER_SEND'])) {
			$sql = "ALTER TABLE `" . $this->dbObject->getPrefix() . "registration` "
					. "ADD `regist_user_send` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mail_send`;";
			if (!$this->dbObject->execute($sql)) {
				return false;
			}

			$sql = "SELECT registration_id, "
						. "mail_body "
					. "FROM {registration} "
					. "WHERE mail_body LIKE ?";
			$inputs = array(
				'%{X-URL}%'
			);
			$registrations = $this->dbObject->execute($sql, $inputs);
			if ($registrations === false) {
				return false;
			}

			foreach ($registrations as $registration) {
				$sql = "UPDATE {registration} SET "
						. "mail_body = ? "
						. "WHERE registration_id = ?";
				$inputs = array(
					str_replace('{X-URL}', '', $registration['mail_body']),
					$registration['registration_id']
				);
				$result = $this->dbObject->execute($sql, $inputs);
				if ($result === false) {
					return false;
				}
			}
		}

		if (!isset($metaColumns['LIMIT_NUMBER'])) {
			$sql = "ALTER TABLE `" . $this->dbObject->getPrefix() . "registration` "
					. "ADD `limit_number` int(11) NOT NULL default '0' AFTER `image_authentication`,"
					. "ADD `period` varchar(14) NOT NULL default '' AFTER `limit_number`;";
			if (!$this->dbObject->execute($sql)) {
				return false;
			}
		}

		$sql = "SELECT DISTINCT "
					. "I.registration_id, "
					. "R.room_id "
				. "FROM {registration_item} I "
				. "INNER JOIN {registration} R "
				. "ON I.registration_id = R.registration_id "
				. "AND I.room_id != R.room_id";
		$registrations = $this->dbObject->execute($sql);
		if ($registrations === false) {
			return false;
		}

		foreach ($registrations as $registration) {
			$sql = "UPDATE {registration_item} SET "
					. "room_id = ? "
					. "WHERE registration_id = ?";
			$inputs = array(
				$registration['room_id'],
				$registration['registration_id']
			);
			$result = $this->dbObject->execute($sql, $inputs);
			if ($result === false) {
				return false;
			}
		}

		// registrationにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."registration` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration` ADD INDEX ( `room_id`, `active_flag` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// registration_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."registration_block` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_registration_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "registration_id") {
				$alter_table_registration_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_registration_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_block` ADD INDEX ( `registration_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// registration_dataにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."registration_data` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_data` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// registration_fileにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."registration_file` ;";
		$results = $this->dbObject->execute($sql);
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
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_file` ADD INDEX ( `upload_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_file` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// registration_itemにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."registration_item` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_registration_id_2_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "registration_id_2") {
				$alter_table_registration_id_2_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_registration_id_2_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_item`
						DROP INDEX `registration_id` ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_item` ADD INDEX `registration_id_2` ( `registration_id`, `item_sequence` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_item` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// registration_item_dataにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."registration_item_data` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_data_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "data_id") {
				$alter_table_data_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_data_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_item_data` ADD INDEX ( `data_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."registration_item_data` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		return true;
	}
}
?>