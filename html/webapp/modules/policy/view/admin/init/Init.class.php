<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 個人情報管理メイン表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Policy_View_Admin_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $user_auth_id = null;
	
	// コンポーネントを使用するため
	var $usersView = null;
	var $session = null;
	
	// 値をセットするため
	var $items = null;
	var $system_items = null;
	var $user_auth_name = null;
	
    /**
     * 個人情報管理メイン表示
     *
     * @access  public
     */
    function execute()
    {	
    	$this->user_auth_id = ($this->user_auth_id == null) ? _AUTH_ADMIN : intval($this->user_auth_id);
    	switch($this->user_auth_id) {
    		case _AUTH_ADMIN:
    			$this->user_auth_name = _AUTH_ADMIN_NAME;
    			break;
    		case _AUTH_CHIEF:
    			$this->user_auth_name = _AUTH_CHIEF_NAME;
    			break;
    		case _AUTH_MODERATE:
    			$this->user_auth_name = _AUTH_MODERATE_NAME;
    			break;
    		case _AUTH_GENERAL:
    			$this->user_auth_name = _AUTH_GENERAL_NAME;
    			break;
    		default:
    			$this->user_auth_name = _AUTH_GUEST_NAME;
    			break;
		}
    	// Valueは使用したいため、user_idはなんでもよい
    	$user_id = $this->session->getParameter("_user_id");
    	
    	$this->items =& $this->usersView->getShowItems($user_id, $this->user_auth_id);
    	if($this->items === false) return 'error';
    	
    	$where_params = array(
    						"user_authority_id" => $this->user_auth_id,
    						"type" => USER_TYPE_SYSTEM
    					);
    	$this->system_items =& $this->usersView->getItems($where_params);
    	if($this->system_items === false) return 'error';
    	
    	return 'success';
    }
}
?>
