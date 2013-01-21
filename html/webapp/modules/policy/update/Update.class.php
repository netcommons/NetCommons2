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
class Policy_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $dbObject = null;

	function execute()
	{
		$sql = "SELECT item_id "
				. "FROM {items} "
				. "WHERE tag_name = ?";
		$inputs = array(
			'password'
		);
		$items = $this->dbObject->execute($sql, $inputs);
		if ($items === false) {
			return false;
		}

		$itemId = $items[0]['item_id'];
		$sql = "SELECT user_authority_id, "
					. "under_public_flag, "
					. "self_public_flag, "
					. "over_public_flag "
				. "FROM {items_authorities_link} "
				. "WHERE item_id = ? "
				. "AND (under_public_flag = ? "
					. "OR self_public_flag = ? "
					. "OR over_public_flag = ?)";
		$inputs = array(
			$itemId,
			USER_PUBLIC,
			USER_PUBLIC,
			USER_PUBLIC
		);
		$itemAuthorities = $this->dbObject->execute($sql, $inputs);
		if ($itemAuthorities === false) {
			return false;
		}
		foreach ($itemAuthorities as $itemAuthority) {
			$updateStatements = array();
			if ($itemAuthority['under_public_flag'] == USER_PUBLIC) {
				$updateStatements['under_public_flag'] = USER_NO_PUBLIC;
			}
			if ($itemAuthority['self_public_flag'] == USER_PUBLIC) {
				$updateStatements['self_public_flag'] = USER_NO_PUBLIC;
			}
			if ($itemAuthority['over_public_flag'] == USER_PUBLIC) {
				$updateStatements['over_public_flag'] = USER_NO_PUBLIC;
			}
			$whereStatements = array(
				'item_id' => $itemId,
				'user_authority_id' => $itemAuthority['user_authority_id'],
			);
			if (!$this->dbObject->updateExecute('items_authorities_link', $updateStatements, $whereStatements, true)) {
				return false;
			}
		}

		$sql = "SELECT item_id "
				. "FROM {items} "
				. "WHERE tag_name = ?";
		$inputs = array(
			'handle'
		);
		$items = $this->dbObject->execute($sql, $inputs);
		if ($items === false) {
			return false;
		}

		$itemId = $items[0]['item_id'];
		$sql = "SELECT user_authority_id, "
					. "under_public_flag, "
					. "self_public_flag, "
					. "over_public_flag "
				. "FROM {items_authorities_link} "
				. "WHERE item_id = ? "
				. "AND (under_public_flag = ? "
					. "OR self_public_flag = ? "
					. "OR over_public_flag = ?)";
		$inputs = array(
			$itemId,
			USER_NO_PUBLIC,
			USER_NO_PUBLIC,
			USER_NO_PUBLIC
		);
		$itemAuthorities = $this->dbObject->execute($sql, $inputs);
		if ($itemAuthorities === false) {
			return false;
		}
		foreach ($itemAuthorities as $itemAuthority) {
			$updateStatements = array();
			if ($itemAuthority['under_public_flag'] == USER_NO_PUBLIC) {
				$updateStatements['under_public_flag'] = USER_PUBLIC;
			}
			if ($itemAuthority['self_public_flag'] == USER_NO_PUBLIC) {
				$updateStatements['self_public_flag'] = USER_PUBLIC;
			}
			if ($itemAuthority['over_public_flag'] == USER_NO_PUBLIC) {
				$updateStatements['over_public_flag'] = USER_PUBLIC;
			}
			$whereStatements = array(
				'item_id' => $itemId,
				'user_authority_id' => $itemAuthority['user_authority_id'],
			);
			if (!$this->dbObject->updateExecute('items_authorities_link', $updateStatements, $whereStatements, true)) {
				return false;
			}
		}

		$sql = "SELECT item_id "
				. "FROM {items_authorities_link} "
				. "WHERE user_authority_id = ? "
				. "AND under_public_flag = ? ";
		$inputs = array(
			_AUTH_GENERAL,
			USER_EDIT
		);
		$itemAuthorities = $this->dbObject->execute($sql, $inputs);
		if ($itemAuthorities === false) {
			return false;
		}
		$updateStatements = array(
			'under_public_flag' => USER_PUBLIC
		);
		foreach ($itemAuthorities as $itemAuthority) {
			$whereStatements = array(
				'item_id' => $itemAuthority['item_id'],
				'user_authority_id' => _AUTH_GENERAL,
			);
			if (!$this->dbObject->updateExecute('items_authorities_link', $updateStatements, $whereStatements, true)) {
				return false;
			}
		}

		return true;
	}
}
?>