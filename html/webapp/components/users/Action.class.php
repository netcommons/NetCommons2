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
}
?>
