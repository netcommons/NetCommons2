<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム作成-編集 確認画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_Regist_Confirm extends Action
{
	// リクエストパラメータを受け取るため
	var $edit_current_page_id = null;
	var $limit = null;
    var $offset = null;
    var $xml_flag = null;
    
    var $edit_flag = null;
    
	// コンポーネントを使用するため
	var $db = null;
	var $pagesView = null;
	var $authoritiesView = null;
	var $request = null;
	
	// 値をセットするため
	var $page = array();
	var $parent_page = array();
	var $count = 0;
	var $users = null;
	var $authorities_count = null;
	
    /**
     * ルーム作成-編集 確認画面
     *
     * @access  public
     */
    function execute()
    {
    	$this->edit_current_page_id = ($this->edit_current_page_id == null) ? 0 : intval($this->edit_current_page_id);
    	$this->page =& $this->pagesView->getPageById(intval($this->edit_current_page_id));
    	if($this->page['parent_id'] != 0) {
    		$this->parent_page =& $this->pagesView->getPageById(intval($this->page['parent_id']));
    	}
    	//
		// モデレータの細分化された一覧を取得
		//
		$mod_where_params = array("user_authority_id" => _AUTH_MODERATE);
		$mod_order_params = array("hierarchy" => "DESC");
		$authorities = $this->authoritiesView->getAuthorities($mod_where_params, $mod_order_params);
		if($authorities === false) {
			return 'error';
		}
		$this->authorities_count = count($authorities);
		
		//
		// 参加会員取得
		//
    	$sql_params = array();
    	$select_str = "SELECT {users}.*, ".
								"{authorities}.user_authority_id,".
								//"{authorities}.role_authority_name,".
								//"{authorities}.public_createroom_flag,".
								//"{authorities}.group_createroom_flag,".
								//"{authorities}.private_createroom_flag,".
								"{authorities}.hierarchy,".
								//"{authorities}.system_flag AS auth_system_flag,".
								"{pages_users_link}.role_authority_id AS authority_id,".
								"{pages_users_link}.createroom_flag";
		$select_page_id = $this->edit_current_page_id;
		if($this->page['default_entry_flag'] == _ON) {
    		$from_str = " FROM {authorities}, {users}".
				" LEFT JOIN {pages_users_link} ON {pages_users_link}.room_id=".$select_page_id." ".
				" AND {users}.user_id={pages_users_link}.user_id ";
    	} else {
    		$from_str = " FROM {authorities}, {users}".
				" INNER JOIN {pages_users_link} ON {pages_users_link}.room_id=".$select_page_id." ".
				" AND {users}.user_id={pages_users_link}.user_id ";
    	}
    	$where_str = " WHERE {users}.role_authority_id={authorities}.role_authority_id".
					" AND ({pages_users_link}.role_authority_id!= " . _ROLE_AUTH_OTHER ." OR {pages_users_link}.role_authority_id IS NULL)";
    	if($this->xml_flag) {
    		$order_str = " ORDER BY {users}.system_flag DESC,hierarchy DESC,user_authority_id DESC, handle ASC ";
    		$sql = $select_str.$from_str.$where_str.$order_str;
    		$result = $this->db->execute($sql, $sql_params, $this->limit,  $this->offset, true, array($this, "_fetchcallback"),array($this->page));
			if ($result === false) {
		       	$this->db->addError();
		       	return 'error';
			}
			$this->users =& $result;
			$this->request->setParameter("_noscript", _ON);
    		return 'xml_success';
    	} else {
			// 登録数取得
			// 管理者の数を足す
			// 不参加のものも表示させる
	    	$sql = "SELECT COUNT(*) AS count ".$from_str.$where_str;
			$result = $this->db->execute($sql, $sql_params, null, null, false);
			if ($result === false) {
		       	$this->db->addError();
		       	return 'error';
			}
			$this->count = $result[0][0];
			
    		return 'success';
    	}
    }
    
    
    
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_fetchcallback($result, $func_params) {
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		
		$config = $configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return false;
		$default_entry_role_auth_public = $config['default_entry_role_auth_public']['conf_value'];
    	$default_entry_role_auth_group = $config['default_entry_role_auth_group']['conf_value'];
    	$default_entry_role_auth_private = $config['default_entry_role_auth_private']['conf_value'];
    	
		$ret = array();
		$page =& $func_params[0];
		$authorities = $this->authoritiesView->getAuthorities(null, null, null, null, true);
		
		while ($row = $result->fetchRow()) {
			//if($row['auth_system_flag'] == _ON && defined($row['role_authority_name'])) {
			//	$row['role_authority_name'] = constant($row['role_authority_name']);
			//}
			if($row['authority_id'] === null && $page['default_entry_flag'] == _ON) {
				if($page['private_flag'] == _ON) {
					$row['authority_id'] = $default_entry_role_auth_private;
				} else if($page['space_type'] == _SPACE_TYPE_GROUP) {
					$row['authority_id'] = $default_entry_role_auth_group;
				} else {
					$row['authority_id'] = $default_entry_role_auth_public;
				}
			}
			$row['role_authority_name'] = $authorities[$row['authority_id']];
			//if($authorities[$row['authority_id']]['system_flag'] == _ON && defined($authorities[$row['authority_id']]['role_authority_name'])) {
			//	$row['role_authority_name'] = constant($authorities[$row['authority_id']]['role_authority_name']);
			//} else {
			//	$row['role_authority_name'] = $authorities[$row['authority_id']]['role_authority_name']."_".$row['authority_id'];
			//}
			$ret[] = $row;
		}
		return $ret;
	}
}
?>