<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索実行(XML取得)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Main_Searchresult extends Action
{
	// リクエストパラメータを受け取るため
	var $limit = null;
    var $offset = null;
    var $sort_col = null;
    var $sort_dir = null;
    
    var $select_user = null;
    
    var $module_id = null;
    
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $usersView = null;
	var $authoritiesView = null;
	
	// 値をセットするため
	var $users = null;
	var $items = null;
	var $user_id = null;
	var $user_auth_id = null;
	
    //
    // サイト運営モジュールのみ実行可能な管理者は、システムコントロールモジュールを実行可能な管理者に変更できない
    //
    var $not_chg_role_id_arr = array();
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		//初期化
		$this->sort_col = ($this->sort_col == null) ? $this->sort_col ="user_authority_id" : $this->sort_col;
		$this->sort_dir = ($this->sort_dir == null) ? $this->sort_dir ="DESC" : $this->sort_dir;
		$this->user_id = $this->session->getParameter("_user_id");
		//$module_link_where_params = array(
		//	"role_authority_id" => $this->session->getParameter("_role_auth_id"),
		//	"module_id" => intval($this->module_id)
		//);
		//$module_links = $this->authoritiesView->getAuthoritiesModulesLink($module_link_where_params);
		//if($module_links === false || !isset($module_links[0])) {
			$this->user_auth_id = $this->session->getParameter("_user_auth_id");
		//} else {
		//	$this->user_auth_id = $module_links[0]['authority_id'];
		//}
		// 管理者が見れる項目を表示する
    	//$user_id = $this->session->getParameter("_user_id");
    	$user_auth_id = $this->user_auth_id;
    	$role_auth_id = $this->session->getParameter("_role_auth_id");
    	if($user_auth_id == _AUTH_ADMIN) {
    		//
    		// 管理者ならば、システムコントロールモジュール、サイト運営モジュールの選択の有無で上下を判断
    		//
    		$func = array($this, "_getSysModulesFetchcallback");
    		$authority = $this->authoritiesView->getAuthorityById($role_auth_id);
    		$system_user_flag = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($role_auth_id, array("system_flag"=>_ON), null, $func);
			if($system_user_flag === null) {
				$where_params = array("user_authority_id" => _AUTH_ADMIN, "role_authority_id !=".$role_auth_id => null);
				$authorities = $this->authoritiesView->getAuthorities($where_params);
				foreach($authorities as $buf_authority) {
					$buf_system_user_flag = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($buf_authority['role_authority_id'], array("system_flag"=>_ON), null, $func);
					if($buf_system_user_flag === true) {
						$this->not_chg_role_id_arr[$buf_authority['role_authority_id']] = true;
					}
				}
			}
    	}
    	
    	$where_params = array(
    						"user_authority_id" => $this->user_auth_id
    					);
    	$this->items =& $this->usersView->getItems($where_params, null, null, null, array($this, "_getItemsFetchcallback"));
		if($this->items === false) return 'error';
		
		$order_params = array(
			$this->sort_col => $this->sort_dir,
			"{users}.system_flag" => "DESC",
			"{users}.handle" => "ASC"
		);
		$order_str = " ".$this->db->getOrderSQL($order_params);
		//
		// 検索条件をセッションから取得
		//
		$params =& $this->session->getParameter(array("user", "selected_params"));
		$sql = $this->session->getParameter(array("user", "selected_select_str")).
				$this->session->getParameter(array("user", "selected_where_str")) . $order_str;
		$this->users =& $this->db->execute($sql, $params, intval($this->limit), intval($this->offset), true, array($this, "_SearchFetchcallback"));
		if($this->users === false) {
			$this->db->addError();
			return 'error';
		}
		return 'success';
	}
	
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_SearchFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(isset($row['auth_system_flag']) && $row['auth_system_flag'] == _ON &&
				defined($row['role_authority_name'])) {
					$row['role_authority_name'] = constant($row['role_authority_name']);
			}
			$ret[] = $row;
		}
		return $ret;
	}
	
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(isset($row['tag_name']) && $row['tag_name'] !="") {
				switch ($row['tag_name']) {
					case "active_flag_lang":
						$tag_name = "active_flag";
						break;
					case "timezone_offset_lang":
						$tag_name = "timezone_offset";
						break;
					default :
						$tag_name = $row['tag_name'];
				}
				$ret[$tag_name] = $row;
			}
		}
		return $ret;
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return boolean
	 * @access	private
	 */
	function _getSysModulesFetchcallback($result) {
		$site_modules_dir_arr = explode("|", AUTHORITY_SYS_DEFAULT_MODULES_ADMIN);	
		while ($obj = $result->fetchRow()) {
			if($obj["authority_id"] === null) continue;
			$module_id = $obj["module_id"];
			
			$pathList = explode("_", $obj["action_name"]);
			if(!in_array($pathList[0], $site_modules_dir_arr)) {
				return true;	
			}
		}
		return null;
	}
}
?>