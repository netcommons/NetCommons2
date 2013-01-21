<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報-アバターの削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Action_Main_Delimage extends Action
{
	// リクエストパラメータを受け取るため
	var $item_id = null;
	var $user_id = null;

	// バリデートによりセット
	var $items = null;
	var $user = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $usersAction = null;
	var $usersView = null;
	var $uploadsAction = null;
	
	// 値をセットするため

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if(!isset($this->user_id) || $this->user_id == "0") {
			$user_id = $this->session->getParameter("_user_id");
		} else {
			$user_id = $this->user_id;
		}
		if($this->items['type'] != "file") {
			// アバター以外
			return 'error';	
		}
		
		$user_item_link = $this->usersView->getUserItemLinkById($user_id, $this->item_id);
		if($user_item_link === false || !isset($user_item_link['user_id'])) {
			return 'error';	
		}
		$upload_id_arr = $this->uploadsAction->getUploadId($user_item_link['content'], "common_download_user&upload_id=", array("module_id"=>null));
		if(count($upload_id_arr) == 0) {
			return 'error';	
		}
		foreach($upload_id_arr as $upload_id) {
			$result = $this->uploadsAction->delUploadsById(intval($upload_id));
			if($result === false) {
				return 'error';
			}
		}
		// 削除
		$result = $this->usersAction->delUsersItemsLinkById($this->item_id, $this->user_id);
		if($result === false) {
			return 'error';
		}
		return 'success';
	}
}
?>
