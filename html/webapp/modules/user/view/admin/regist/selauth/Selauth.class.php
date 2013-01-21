<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 参加会員権限設定画面表示
 * 参加ルーム選択 >>　次へボタン押下時
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Regist_Selauth extends Action
{
    // リクエストパラメータを受け取るため
    var $user_id = null;
    var $enroll_room = null;
    var $not_enroll_room = null;

    // 使用コンポーネントを受け取るため
    var $pagesView = null;
    var $session = null;
    var $authoritiesView = null;
    var $configView = null;
    
    // 値をセットするため
    var $edit_flag = _ON;
    var $enroll_room_list = null;
    var $count = 0;
    var $user_auth_id = null;	//_DEFAULT_ENTRY_AUTH;	//_AUTH_GENERAL;	default:一般
    var $role_auth_id = null;
    
    var $current_authority = null;
    var $authorities = null;
    var $authorities_count = 0;
    
    var $default_entry_role_auth_public = null;
    var $default_entry_role_auth_group = null;
    var $default_entry_role_auth_private = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	if($this->user_id == null || $this->user_id == "0") {
			$this->user_id = "0";
			$this->edit_flag = _OFF;
			$user_id = $this->session->getParameter("_system_user_id");;
		} else {
			$user_id = $this->user_id;
			
			//$user =& $this->usersView->getUserById($this->user_id);
    		//if($user === false) return $errStr;	
			//$this->user_auth_id = $user['user_authority_id'];
    	}
    	$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return 'error';
		$this->default_entry_role_auth_public = $config['default_entry_role_auth_public']['conf_value'];
    	$this->default_entry_role_auth_group = $config['default_entry_role_auth_group']['conf_value'];
    	$this->default_entry_role_auth_private = $config['default_entry_role_auth_private']['conf_value'];
		
    	if($this->session->getParameter(array("user", "regist_confirm", $this->user_id)) === "") {
    		return 'selroom';
    	} else if($this->enroll_room == null && $this->session->getParameter(array("user", "regist_confirm", $this->user_id)) === null) {
    		// 参加ルームなし->確認画面へ
    		$this->session->removeParameter(array("user", "selroom", $this->user_id));
    		$this->session->removeParameter(array("user", "selauth", $this->user_id));
	    	return 'confirm';
    	}
    	//if($this->enroll_room == null && $this->session->getParameter(array("user", "selroom", $this->user_id)) == null) {
    	//	if($this->session->getParameter(array("user", "regist_confirm", $this->user_id)) === null) {
	    //		// 参加ルームなし->確認画面へ
	    //		return 'confirm';
    	//	} else {
    	//		return 'selroom';
    	//	}
    	//}
    	
    	// 初期化
    	$this->session->removeParameter(array("user", "regist_confirm", $this->user_id));
    	
    	$this->user_auth_id = $this->session->getParameter(array("user", "regist_auth", $this->user_id));
    	$this->role_auth_id = $this->session->getParameter(array("user", "regist_role_auth", $this->user_id));
    	$this->current_authority = $this->authoritiesView->getAuthorityById($this->role_auth_id);
    	if($this->current_authority === false) {
    		return 'error';	
    	}
    	
    	// 参加ルームセッション保存
    	if($this->enroll_room != null || $this->not_enroll_room != null) {
    		$this->session->removeParameter(array("user", "selroom", $this->user_id));
    		$this->session->setParameter(array("user", "selroom", $this->user_id, "enroll_room"), $this->enroll_room);
    		$this->session->setParameter(array("user", "selroom", $this->user_id, "not_enroll_room"), $this->not_enroll_room);
    	}
    	
		$where_params = array(
							"user_id" => $user_id,
							"user_authority_id" => _AUTH_ADMIN,
							"role_authority_id" => 1,	//1固定
							"{pages}.page_id={pages}.room_id" => null
						);
		$order_params = null;
		$result =& $this->pagesView->getShowPagesList($where_params, $order_params, 0, 0, array($this, '_showpages_fetchcallback'));
		if($result === false) {
			return 'error';
		}
		list($this->enroll_room_list, $this->count) = $result;
		
		//
		// モデレータの細分化された一覧を取得
		//
		$where_params = array("user_authority_id" => _AUTH_MODERATE);
		$order_params = array("hierarchy" => "DESC");
		$this->authorities = $this->authoritiesView->getAuthorities($where_params, $order_params);
		if($this->authorities === false) {
			return 'error';
		}
		$this->authorities_count = count($this->authorities);
    	
        return 'success';
    }
    
    
	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function _showpages_fetchcallback($result) {
		$count = 0;
		$enroll_ret = array();
		$selroom =& $this->session->getParameter(array("user", "selroom", $this->user_id));
		if(isset($selroom)) {
			//
			// sessionデータから振り分ける
			//
			$enroll_sess_room_list = array();
			if(isset($selroom['enroll_room'])) {
				foreach($selroom['enroll_room'] as $enroll_room) {
					$enroll_room_list = explode("_", $enroll_room);
					$enroll_sess_room_list[$enroll_room_list[0]] = $enroll_room_list[0];
				}
			}
			while ($row = $result->fetchRow()) {
				if(isset($enroll_sess_room_list[$row['page_id']]) || 
					($row['thread_num'] == 0 && $row['space_type'] == _SPACE_TYPE_GROUP && $row['private_flag'] == _OFF) ) {
					// 参加
					$enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
					$count++;
				}
			}
		}
		$count--;	// グループスペース分引く
		return array($enroll_ret, $count);
	}
}
?>