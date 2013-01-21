<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員管理>>項目設定画面画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Setting extends Action
{
    // 使用コンポーネントを受け取るため
    var $usersView = null;
    var $session = null;
    var $db = null;
    
    // 値をセットするため
    var $items = null;
    var $user = null;
    var $user_id = null;
    var $user_auth_id = null;
    
    var $max_row = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$this->user_id = $this->session->getParameter("_user_id");
    	$this->user_auth_id = $this->session->getParameter("_user_auth_id");
    	$this->user =& $this->usersView->getUserById($this->user_id);
    	if($this->user === false) return 'error';
    	$this->items =& $this->usersView->getShowItems($this->user_id, $this->user_auth_id, null);
    	if($this->items === false) return 'error';
    	
    	$this->max_row = $this->db->maxExecute("items", "row_num");
    	
        return 'success';
    }
}
?>
