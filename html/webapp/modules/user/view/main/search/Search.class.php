<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員管理>>会員検索画面表示
 * 		自分が見える項目を検索対象とする
 * 		自分自身が非公開の項目で、他人が公開できるものがあっても現状、検索させない
 * 		厳密には、under_public_flag,over_public_flagのうち１つでも公開可能(USER_PUBLIC or USER_Edit)なものがあれば
 * 		検索対象にするほうがよい？
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Main_Search extends Action
{
	// リクエストパラメータを受け取るため
	var $room_top_id_name = null;		//ルーム管理-会員絞込み
	var $room_parent_id = null;	//ルーム管理-会員絞込み
	var $room_current_id = null;	//ルーム管理-会員絞込み
	var $js_class_name = null;	//ルーム管理-会員絞込み
	
    // 使用コンポーネントを受け取るため
    var $usersView = null;
    var $session = null;
    
    // フィルタによりセット
    var $room_list = null;
    
    // 値をセットするため
    var $items = null;
    var $user = null;
    var $user_id = null;
    var $user_auth_id = null;
    var $dialog_name = "";

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$this->user_id = $this->session->getParameter("_user_id");
		$this->user_auth_id = $this->session->getParameter('_user_auth_id');

    	//$this->user_auth_id = $this->session->getParameter("_user_auth_id");
    	$this->user =& $this->usersView->getUserById($this->user_id);
    	if($this->user === false) return 'error';
    	$this->items =& $this->usersView->getShowItems($this->user_id, $this->user_auth_id);
    	if($this->items === false) return 'error';
    	// ルーム管理-会員絞込み
    	if($this->room_top_id_name) {
    		$this->dialog_name = USER_SEARCH_DIALOG_TITLE;
    		$this->room_parent_id = intval($this->room_parent_id);
    		$this->room_current_id = ($this->room_current_id === null) ? 0 : intval($this->room_current_id);
    	} else {
    		$this->room_current_id = -1;
    	}
    	return 'success';
    }
}
?>
