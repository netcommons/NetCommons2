<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム管理メイン表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_Init extends Action
{
	// コンポーネントを使用するため
	var $pagesView = null;
	var $session = null;
	var $authoritiesView = null;
	
	// 値をセットするため
	var $pages = null;
	
    /**
     * ルーム管理メイン表示
     *
     * @access  public
     */
    function execute()
    {
    	// Sessionクリア
		$this->session->removeParameter(array("room"));
		
		//
		// プライベートスペース使用不可フラグ取得のため
		//
		$this->authority =& $this->authoritiesView->getAuthorityById($this->session->getParameter("_role_auth_id"));
		if($this->authority === false) {
			return 'error';
		}
		
    	$user_id = $this->session->getParameter("_user_id");
    	$where_params = array(
    						"thread_num" => 0,
    						"(private_flag="._OFF." OR ({pages_users_link}.user_id = '".$user_id."' AND {pages}.insert_user_id = '".$user_id."' AND  private_flag="._ON."))" => null
    					);
    	$order_params = array("display_sequence" => "ASC");
    	
    	$this->pages =& $this->pagesView->getShowPagesList($where_params, $order_params);
    	if($this->pages === false) return 'error';
    	return 'success';
    }
}
?>
