<?php
/**
 * モジュールアップデートアクションクラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Update extends Action
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
		$sql = "SELECT DISTINCT "
					. "U.photo_id, "
					. "P.room_id "
				. "FROM {photoalbum_user_photo} U "
				. "INNER JOIN {photoalbum_photo} P "
				. "ON U.photo_id = P.photo_id "
				. "AND U.room_id != P.room_id";
		$photos = $this->dbObject->execute($sql);
		if ($photos === false) {
			return false;
		}

		foreach ($photos as $photo) {
			$sql = "UPDATE {photoalbum_user_photo} SET "
					. "room_id = ? "
					. "WHERE photo_id = ?";
			$inputs = array(
				$photo['room_id'],
				$photo['photo_id']
			);
			$result = $this->dbObject->execute($sql, $inputs);
			if ($result === false) {
				return false;
			}
		}

		// photoalbumにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."photoalbum` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// photoalbum_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."photoalbum_block` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_block_id_flag = true;
		$alter_table_photoalbum_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "PRIMARY") {
				$alter_table_block_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "photoalbum_id") {
				$alter_table_photoalbum_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_block_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_block` ADD PRIMARY KEY ( `block_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		if($alter_table_photoalbum_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_block` ADD INDEX ( `photoalbum_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// photoalbum_commentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."photoalbum_comment` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_photo_id_flag = true;
		$alter_table_album_id_flag = true;
		$alter_table_photoalbum_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "photo_id") {
				$alter_table_photo_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "album_id") {
				$alter_table_album_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "photoalbum_id") {
				$alter_table_photoalbum_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_photo_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_comment` ADD INDEX ( `photo_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_album_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_comment` ADD INDEX ( `album_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_photoalbum_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_comment` ADD INDEX ( `photoalbum_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_comment` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// photoalbum_photoにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."photoalbum_photo` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_album_id_flag = true;
		$alter_table_photoalbum_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "album_id") {
				$alter_table_album_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "photoalbum_id") {
				$alter_table_photoalbum_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_album_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_photo` ADD INDEX ( `album_id`, `photo_sequence` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_photoalbum_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_photo` ADD INDEX ( `photoalbum_id`, `album_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_photo` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// photoalbum_user_photoにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."photoalbum_user_photo` ;";
		$results = $this->dbObject->execute($sql);
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
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_user_photo`
						DROP INDEX `user_id` ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_user_photo` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// photoalbum_albumにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."photoalbum_album` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_photoalbum_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "photoalbum_id") {
				$alter_table_photoalbum_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_photoalbum_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_album` ADD INDEX ( `photoalbum_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."photoalbum_album` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// 件数表示で全てを選択されている場合、100件にする
		$sql = "UPDATE `".$this->dbObject->getPrefix()."photoalbum_block` SET album_visible_row = 100 WHERE album_visible_row = 0";
		$result = $this->dbObject->execute($sql);
		if($result === false) return false;

		return true;
	}
}
?>