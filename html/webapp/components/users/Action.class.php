<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員テーブル登録用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Users_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Users_Action() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * 会員テーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updUsers($params=array(), $where_params=array(), $footer_flag=true) {
		return $this->_db->updateExecute("users", $params, $where_params, $footer_flag);
	}

	/**
	 * 会員テーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insUser($params=array()) {
		$sitesView =& $this->_container->getComponent("sitesView");
		$sites = $sitesView->getSelfSite();
		while(1) {
			$id = $this->_db->nextSeq("users");
			$user_id = sha1(uniqid($sites['site_id'].$id, true));
			// Hash値で同じものがないか念のためチェック
			$result = $this->_db->selectExecute("users", array("user_id" => $user_id));
			if ($result === false) {
				return false;
			}
			if(!isset($result[0]['user_id'])) {
				break;
			}
		}
		$params = array_merge(array("user_id" => $user_id), $params);
		$result = $this->_db->insertExecute("users", $params, true);
		if ($result === false) {
			return false;
		}
		return $user_id;
	}

	/**
	 * 項目テーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insItem($params=array()) {
		$item_id = $this->_db->insertExecute("items", $params, true, "item_id");
		if ($item_id === false) {
			return false;
		}
		
		return $item_id;
	}

	/**
	 * 項目説明文テーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insItemDesc($item_id, $params=array()) {
		$db_params = array("item_id"=>0);
		$db_params = array_merge($db_params, $params);
		$db_params["item_id"] = $item_id;

		return $this->_db->insertExecute("items_desc", $db_params);
	}

	/**
	 * 項目選択式テーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insItemOptions($item_id, $params=array()) {
		$db_params = array("item_id"=>0);
		$db_params = array_merge($db_params, $params);
		$db_params["item_id"] = $item_id;

		return $this->_db->insertExecute("items_options", $db_params);
	}
	
	/**
	 * 項目権限リンクテーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insItemsAuthLink($params = array()) {
		return $this->_db->insertExecute("items_authorities_link", $params, true);
	}
	
	/**
	 * 項目権限リンクテーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updItemsAuthLink($params=array(), $where_params=array(), $footer_flag=true) {
		return $this->_db->updateExecute("items_authorities_link", $params, $where_params, $footer_flag);
	}
	
	/**
	 * 会員項目リンクテーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insUserItemLink($params = array()) {
		return $this->_db->insertExecute("users_items_link", $params, false);
	}

	/**
	 * 項目テーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updItem($params=array(), $where_params=array(), $footer_flag=true) {
		return $this->_db->updateExecute("items", $params, $where_params, $footer_flag);
	}

	/**
	 * 項目説明文テーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updItemDesc($params=array(), $where_params=array()) {
		return $this->_db->updateExecute("items_desc", $params, $where_params, false);
	}

	/**
	 * 項目選択式テーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updItemOptions($params=array(), $where_params=array()) {
		return $this->_db->updateExecute("items_options", $params, $where_params, false);
	}

	/**
	 * 会員項目リンクテーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updUsersItemsLink($params=array(), $where_params=array()) {
		return $this->_db->updateExecute("users_items_link", $params, $where_params, false);
	}

	/**
	 * 会員項目リンクテーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function _updUsersItemsLink(&$result, &$params) {
		$container =& DIContainerFactory::getContainer();
		$db =& $container->getComponent("DbObject");

		$func = array("Users_Action","_updateExecute");
		while ($obj = $result->fetchRow()) {
			$db_params = array("content"=>$params[$obj["tag_name"]]);
			$where_params = array("user_id"=>$params["user_id"],"item_id"=>$obj["item_id"]);
			$sql = $db->getUpdateSQL("users_items_link", $db_params, $where_params, false);
	        $res = $db->execute($sql, $db_params);
			if (!$res) {
				$db->addError();
				return false;
			}
		}

		return true;
	}

	/**
	 * 会員テーブルDelete(user_id)
	 *　
	 * @param   int   $user_id       ユーザーID
	 * @return boolean true or false
	 * @access	public
	 */
	function delUserById($user_id) {
		$params = array( "user_id" => $user_id );
		$result = $this->_db->deleteExecute("users", $params);
		if (!$result) {
			return false;
		}
		$result = $this->_db->deleteExecute("users_items_link", $params);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * 項目テーブルDelete(item_id)
	 *　
	 * @param   int   $item_id       項目ID
	 * @return boolean true or false
	 * @access	public
	 */
	function delItemById($item_id) {
		$params = array( "item_id" => $item_id );
		$result = $this->_db->deleteExecute("items", $params);
		if (!$result) {
			return false;
		}
		$result = $this->_db->deleteExecute("items_desc", $params);
		if (!$result) {
			return false;
		}
		$result = $this->_db->deleteExecute("items_options", $params);
		if (!$result) {
			return false;
		}
		$result = $this->_db->deleteExecute("users_items_link", $params);
		if (!$result) {
			return false;
		}
		$result = $this->_db->deleteExecute("items_authorities_link", $params);
		if (!$result) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * users_items_linkテーブルDelete
	 *　
	 * @param   int   item_id       項目ID
	 * @return boolean true or false
	 * @access	public
	 */
	function delUsersItemsLinkById($item_id, $user_id = null)
	{
		if($user_id == null) {
			$params = array( 
				"item_id" => $item_id
			);
		} else {
			$params = array( 
				"item_id" => $item_id,
				"user_id" => $user_id
			);
		}
		$result = $this->_db->deleteExecute("users_items_link", $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return true;
	}
	
	/**
	 * ItemDescテーブルDelete
	 *　
	 * @param   int   item_id       項目ID
	 * @return boolean true or false
	 * @access	public
	 */
	function delItemDescById($item_id)
	{
		$params = array( 
			"item_id" => $item_id
		);
		$result = $this->_db->deleteExecute("items_desc", $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return true;
	}
	
	/**
	 * items_optionsテーブルDelete
	 *　
	 * @param   int   item_id       項目ID
	 * @return boolean true or false
	 * @access	public
	 */
	function delItemOptionsById($item_id)
	{
		$params = array( 
			"item_id" => $item_id
		);
		$result = $this->_db->deleteExecute("items_options", $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return true;
	}
	
	/**
	 * 会員データを削除する
	 *
	 * @param mix $roomId 削除対象ルームID
	 * @return boolean true or false
	 * @access public
	 */
	function deleteUser($userId)
	{
		if (empty($userId)) {
			return true;
		}

		$userIds = $userId;
		if (!is_array($userId)) {
			$userIds = array($userId);
		}
		$inValue = "'" . implode("','", $userIds) . "'";

		$sql = "SELECT P.room_id, "
					. "U.role_authority_id "
				. "FROM {pages} P "
				. "INNER JOIN {users} U "
					. "ON P.insert_user_id = U.user_id "
				. "WHERE P.thread_num = ? "
				. "AND P.private_flag = ? "
				. "AND P.space_type = ? "
				. "AND P.insert_user_id IN (" . $inValue . ")";
		$bindValues = array(
			0,
			_ON,
			_SPACE_TYPE_GROUP
		);
		$privateRoomsPerRole = $this->_db->execute($sql, $bindValues, null, null, true, array($this, '_fetchPrivateRoomPerRole'));
		
		$roles = array_keys($privateRoomsPerRole);
		$sql = "SELECT M.action_name, "
					. "M.delete_action, "
					. "AM.role_authority_id "
				. "FROM {modules} M "
				. "INNER JOIN {authorities_modules_link} AM "
					. "ON M.module_id = AM.module_id "
				. "WHERE M.system_flag = ? "
				. "AND AM.role_authority_id IN ('" . implode("','", $roles) . "')";
		$bindValues = array(
				_OFF
		);
		$modulesPerRole =& $this->_db->execute($sql, $bindValues, null, null, true, array($this, '_fetchModulePerRole'));
		if ($modulesPerRole === false) {
			return false;
		}

		$pagesAction =& $this->_container->getComponent('pagesAction');
		foreach ($privateRoomsPerRole as $roleId => $privateRooms) {
			if (!$pagesAction->deleteEachModule($privateRooms, $modulesPerRole[$roleId])) {
				return false;
			}

			foreach ($privateRooms as $roomId) {
				if (!$pagesAction->deleteRoom($roomId)) {
					return false;
				}
			}
		}
		
		if (!$this->deleteByInOperator('users', $inValue)) {
			return false;
		}
		
		if (!$this->deleteByInOperator('users_items_link', $inValue)) {
			return false;
		}
		
		if (!$this->deleteByInOperator('pages_users_link', $inValue)) {
			return false;
		}
		
		$sql = "UPDATE {uploads} "
				. "SET "
				. "garbage_flag = ? "
				. "WHERE action_name = ? "
				. "AND unique_id IN (" . $inValue . ") "
				. "AND garbage_flag = ?";
		$bindValues = array(
			_ON,
			'common_download_user',
			_OFF
		);
		if (!$this->_db->execute($sql, $bindValues)) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * ロール毎のプライベートスペースデータ配列を作成する。
	 *
	 * @param object $recordSet プライベートスペースデータADORecordSet
	 * @return array モジュールデータ配列
	 * @access public
	 */
	function _fetchPrivateRoomPerRole($recordSet)
	{
		$privateRoomsPerRole = array();
		while ($privateRoom = $recordSet->fetchRow()) {
			$roleId = $privateRoom['role_authority_id'];
			$privateRoomsPerRole[$roleId][] = $privateRoom['room_id'];
		}

		return $privateRoomsPerRole;
	}

	/**
	 * ロール毎にモジュールデータ配列を作成する
	 *
	 * @param array $recordSet タスクADORecordSet
	 * @return array モジュールデータ配列
	 * @access	private
	 */
	function _fetchModulePerRole($recordSet) {
		$modulesPerRole = array();
		while ($module = $recordSet->fetchRow()) {
			$roleId = $module['role_authority_id'];
			$modulesPerRole[$roleId][] = $module;
		}

		return $modulesPerRole;
	}

	/**
	 * IN演算子でデータを削除する。
	 *
	 * @param string $tableName 対象テーブル名称
	 * @param string $inValue IN演算子の値（カンマ区切り文字列）
	 * @return boolean true or false
	 * @access public
	 */
	function deleteByInOperator($tableName, $inValue)
	{
		if (empty($inValue)) {
			return true;
		}
	
		$sql = "DELETE FROM {" . $tableName . "} "
				. "WHERE user_id IN (" . $inValue . ")";
		if (!$this->_db->execute($sql)) {
			$this->_db->addError();
			return false;
		}
	
		return true;
	}
}
?>
