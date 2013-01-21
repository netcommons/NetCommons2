<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限クラス登録用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authorities_Action
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
	function Authorities_Action() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 権限テーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insAuthority($params=array())
	{
		$role_authority_id = $this->_db->insertExecute("authorities", $params, true, "role_authority_id");
		if($role_authority_id === false) {
			return false;
		}
		return $role_authority_id;
	}

	/**
	 * 権限モジュールリンクテーブルInsert
	 * 
	 * @param   array   $params     パラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function insAuthorityModuleLink($params=array())
	{
		$result = $this->_db->insertExecute("authorities_modules_link", $params, true);
        if ($result === false) {
	       	return $result;
		}
		return true;
	}

	/**
	 * 権限テーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updAuthority($params=array(), $where_params=array())
	{
		$result = $this->_db->updateExecute("authorities", $params, $where_params, true);
        if ($result === false) {
	       	return $result;
		}
		return true;
	}

	/**
	 * 権限モジュールリンクテーブルUpdate
	 * 
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updAuthorityModuleLink($params=array(), $where_params=array())
	{
		$result = $this->_db->updateExecute("authorities_modules_link", $params, $where_params, true);
		if ($result === false) {
	       	return $result;
		}
		return true;
	}

	/**
	 * 権限テーブルDelete(role_authority_id)
	 *　
	 * @param   int   $authority_id       モジュールID
	 * @return boolean true or false
	 * @access	public
	 */
	function delAuthorityById($authority_id)
	{
		$params = array( 
			"role_authority_id" => $authority_id
		);
		$result = $this->_db->deleteExecute("authorities", $params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return true;
	}
	
	/**
	 * 権限モジュールリンクテーブルDelete
	 *　
	 * @param   int   $authority_id	    権限ID
	 * @param   int   $module_id       モジュールID
	 * @return boolean true or false
	 * @access	public
	 */
	function delAuthorityModuleLink($where_params)
	{
		$result = $this->_db->deleteExecute("authorities_modules_link", $where_params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return true;
	}
}
?>
