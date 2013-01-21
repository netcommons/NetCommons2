<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限テーブル表示用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authorities_View 
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
	function Authorities_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
    	$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * authorityを取得する(role_authority_id)
	 * 
	 * @param  int 	    $id	      role_authority_id
	 * @return array
	 * @access	public
	 */
	function &getAuthorityById($id)
	{
		$where_params = array("role_authority_id" => $id);
		$result = $this->getAuthorities($where_params);
		if($result === false) {
			return $result;
		}
		if(!isset($result[0])) {
			$result = null;
			return 	$result;
		}
		return $result[0];
	}

	/**
	 * authority件数を取得する(role_authority_id)
	 * 重複チェック時に使用
	 * @param  int 	    $role_authority_id		権限ID
	 * @param  string 	$role_authority_name	権限名称
	 * @return int  件数
	 * @access	public
	 */
	function getCountAuthorityByName($role_authority_id, $role_authority_name, $where_params=array())
	{
		$db_params = array();
		$sql_where = "";
		if($role_authority_id != null) {
			$sql_where .= " AND role_authority_id<>?";
			$db_params[] = $role_authority_id;
		}
		$sql_where .= " AND role_authority_name=?";
		$db_params[] = $role_authority_name;

	    if (!empty($where_params)) {
	        foreach ($where_params as $key=>$val) {
	        	if (isset($val)) {
	        		$db_params[] = $val;
		        	$sql_where .= " AND ".$key."=?";
	        	} else {
	        		$sql_where .= " AND ".$key;
	        	}
	        }
	    }

	    $sql= "";
	    $sql .= "SELECT COUNT(*) AS cnt FROM {authorities} ";
		$sql .= ($sql_where ? " WHERE ".substr($sql_where,5) : "");	

		$result = $this->_db->execute($sql, $db_params);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result[0]["cnt"];
	}
	
	/**
	 * 管理系モジュールの権限IDを取得(role_authority_id,module_id)
	 * 会員管理は、権限(role_auth_id)毎で権限を変更できる仕様（権限管理参照）
	 * @param  int 	$role_authority_id   権限ID
	 * @param  int  $module_id           モジュールID
	 * @return int  authority_id
	 * @access	public
	 */
	function &getSystemAuthorityIdById($role_authority_id, $module_id)
	{
		$params = array(
			"module_id" => $module_id,
			"role_authority_id" => $role_authority_id
		);
		
		$result = $this->_db->execute("SELECT {authorities_modules_link}.authority_id FROM {authorities},{authorities_modules_link} " .
										" WHERE {authorities_modules_link}.module_id =? " .
										" AND {authorities}.role_authority_id={authorities_modules_link}.role_authority_id " .
										" AND {authorities}.role_authority_id=?",$params,null,null,false);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		if(isset($result[0][0])) {
			return $result[0][0];
		} else {
			$ret = false;
			return $ret;
		}
	}
	
	/**
	 * authoritiesの一覧を取得する
	 * 
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @return array 権限リスト
	 * @access	public
	 */
	function &getAuthorities($where_params=null, $order_params = array("{authorities}.user_authority_id"=>"DESC", "{authorities}.hierarchy"=>"DESC", "{authorities}.role_authority_name"=>"ASC"), $limit=null, $start=null, $list_flag=false)
	{
		$db_params = array();
		
		$sql = $this->_db->getSelectSQL("authorities", $db_params, $where_params, $order_params);
		if ($list_flag) {
			$func = array($this, "_getRoleAuthList");
		} else {
			$func = array($this, "_getAuthList");
		}
		$result = $this->_db->execute($sql ,$db_params, $limit, $start, true, $func);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
	/**
	 * authoritiesの一覧を取得する
	 * 
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @return array 権限リスト
	 * @access	private
	 */
	function _getRoleAuthList(&$result)
	{
		$data = array();
		while ($obj = $result->fetchRow()) {
			$obj["def_role_authority_name"] = $obj["role_authority_name"];
			if ($obj["system_flag"] == _ON && defined($obj["role_authority_name"])) {
				$obj["role_authority_name"] = constant($obj["role_authority_name"]);
			}
			$data[$obj["role_authority_id"]] = $obj["role_authority_name"];
		}
		return $data;
	}
	
	/**
	 * authoritiesの一覧を取得する
	 * 
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @return array 権限リスト
	 * @access	private
	 */
	function _getAuthList(&$result)
	{
		$data = array();
		while ($obj = $result->fetchRow()) {
			$obj["def_role_authority_name"] = $obj["role_authority_name"];
			if ($obj["system_flag"] == _ON && defined($obj["role_authority_name"])) {
				$obj["role_authority_name"] = constant($obj["role_authority_name"]);
			}
			$data[] = $obj;
		}
		return $data;
	}

	/**
	 * role_authority_id,module_idからauthority_object(authority_module_link_object)を取得する
	 * @param int role_authority_id, int module_id
	 * @return object authority_object(authority_module_link_object)
	 * @access	public
	 */
	/*
	function &getAuthorityModuleLinkById($role_authority_id, $module_id)
	{
		$params = array(
			"module_id" => $module_id,
			"role_authority_id" => $role_authority_id
		);
		
		$result = $this->_db->execute("SELECT {authorities}.*,{authorities_modules_link}.module_id,{authorities_modules_link}.authority_id FROM {authorities},{authorities_modules_link} " .
										" WHERE {authorities_modules_link}.module_id =? " .
										" AND {authorities}.role_authority_id={authorities_modules_link}.role_authority_id " .
										" AND {authorities}.role_authority_id=?",$params);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result[0];
	}
	*/
	/**
	 * authority_object(authority_module_link_object)の一覧を取得する
	 * @return object authority_object(authority_module_link_object)
	 * @access	public
	 */
	function &getAuthoritiesModulesLink($where_params=array(), $order_params=array(), $func=null, $func_param=null)
	{
		$db_params = array();
		$sql = $this->_db->getSelectSQL("authorities_modules_link", $db_params, $where_params, $order_params);
		$result = $this->_db->execute($sql ,$db_params, null, null, true, $func, $func_param);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * authority_object(authority_module_link_object)の一覧を取得する
	 * @return object authority_object(authority_module_link_object)
	 * @access	public
	 */
	function &getAuthoritiesModulesLinkByModuleId($module_id, $where_params=null, $order_params=null, $func=null, $func_params=null)
	{
		$db_params = array();
        $sql_where = "";
        if (!empty($module_id)) {
        	$db_params[] = $module_id;
        }
        if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	$db_params[] = $item;
	        	$sql_where .= " AND ".$key."=?";
	        }
        }
        $sql_order = "";
        if (!isset($order_params)) {
        	$order_params = array("{authorities}.role_authority_id"=>"ASC");	
        }
        if (!empty($order_params)) {
	        foreach ($order_params as $key=>$item) {
	        	$sql_order .= ",".$key." ".(empty($item) ? "ASC" : $item);
	        }
        }

		$sql = "";
		$sql .= "SELECT {authorities}.*,{authorities_modules_link}.module_id,{authorities_modules_link}.authority_id".
				" FROM {authorities} LEFT JOIN {authorities_modules_link} ON ({authorities}.role_authority_id={authorities_modules_link}.role_authority_id" . (isset($module_id) ? " AND {authorities_modules_link}.module_id=?)" : ")");
		$sql .= ($sql_where ? " WHERE ".substr($sql_where,5) : "");
		$sql .= ($sql_order ? " ORDER BY ".substr($sql_order,1) : "");
		$result = $this->_db->execute($sql ,$db_params, null, null, true, $func, $func_params);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * module_object(authority_module_link_object)の一覧を取得する
	 * @return object module_object(authority_module_link_object)
	 * @access	public
	 */
	function &getAuthoritiesModulesLinkByAuthorityId($role_authority_id, $where_params=null, $order_params=null, $func=null, $func_param= null)
	{
		$db_params = array();
        $sql_where = "";
        if (isset($role_authority_id)) {
        	$db_params[] = $role_authority_id;
        }
        if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	$db_params[] = $item;
	        	$sql_where .= " AND ".$key."=?";
	        }
        }
        $sql_order = "";
        if (!isset($order_params)) {
        	$order_params = array("{modules}.display_sequence"=>"ASC");	
        }
        if (!empty($order_params)) {
	        foreach ($order_params as $key=>$item) {
	        	$sql_order .= ",".$key." ".(empty($item) ? "ASC" : $item);
	        }
        }

		$sql = "";
		$sql .= "SELECT {modules}.*,{authorities_modules_link}.authority_id".
				" FROM {modules}".
				" LEFT JOIN {authorities_modules_link} ON ({modules}.module_id={authorities_modules_link}.module_id" . (isset($role_authority_id) ? " AND {authorities_modules_link}.role_authority_id=?)" : ")");
		$sql .= " WHERE {modules}.disposition_flag="._ON;
		$sql .= ($sql_where ? " AND ".substr($sql_where,5) : "");
		$sql .= ($sql_order ? " ORDER BY ".substr($sql_order,1) : "");
		
		$result = $this->_db->execute($sql ,$db_params, null, null, true, $func, $func_param);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
}
?>
