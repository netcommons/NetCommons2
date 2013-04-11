<?php
/**
 * 会員管理アップデートクラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		$isIndexedItemId = false;
		$isIndexedContent = false;
		$isFullTextIndexedContent = false;
		$sql = "SHOW INDEX "
			. "FROM {users_items_link};";
		$indexes = $this->db->execute($sql);
		if ($indexes === false) {
			return false;
		}
		foreach($indexes as $index) {
			if ($index['Key_name'] == 'item_id') {
				$isIndexedItemId = true;
			}
			if ($index['Key_name'] == 'content') {
				$isIndexedContent = true;

				if ($index['Index_type'] == 'FULLTEXT') {
					$isFullTextIndexedContent = true;
				}
			}
		}
		if (!$isIndexedItemId) {
			$sql = "ALTER TABLE {users_items_link} "
				. "ADD INDEX ("
					. "`item_id`, "
					. "`email_reception_flag`);";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		if ($isFullTextIndexedContent) {
			$sql = "ALTER TABLE {users_items_link} "
				. "DROP INDEX `content`;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$isIndexedContent = false;
		}
		if (!$isIndexedContent) {
			$sql = "ALTER TABLE {users_items_link} "
				. "ADD INDEX (`content`(255));";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		$sql = "SHOW INDEX "
			. "FROM {users};";
		$indexes = $this->db->execute($sql);
		if ($indexes === false) {
			return false;
		}
		$isIndexedLoginId = false;
		$isIndexedHandle = false;
		$isIndexedActiveFlag = false;
		$isIndexedActivateKey = false;
		foreach($indexes as $index) {
			if ($index['Key_name'] == 'active_flag') {
				$isIndexedActiveFlag = true;
			}
			if ($index['Key_name'] == 'login_id') {
				$isIndexedLoginId = true;
			}
			if ($index['Key_name'] == 'handle') {
				$isIndexedHandle = true;
			}
			if ($index['Key_name'] == 'activate_key') {
				$isIndexedActivateKey = true;
			}
		}
		if (!$isIndexedLoginId) {
			$sql = "ALTER TABLE {users} "
				. "ADD INDEX (`login_id`);";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		if (!$isIndexedHandle) {
			$sql = "ALTER TABLE {users} "
				. "ADD INDEX (`handle`);";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		if (!$isIndexedActiveFlag) {
			$sql = "ALTER TABLE {users} "
				. "ADD INDEX ("
					. "`active_flag`, "
					. "`system_flag`, "
					. "`role_authority_id`);";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		if (!$isIndexedActivateKey) {
			$sql = "ALTER TABLE {users} "
				. "ADD INDEX (`activate_key`);";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		return true;
	}
}
?>
