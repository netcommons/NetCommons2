<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 画像アップロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Upload_Image extends Action
{
	// リクエストパラメータを受け取るため
	var $unique_id = null;
	var $item_id = null;
	
	// バリデートによりセット
	var $items = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;
	var $session = null;
	var $usersView = null;
	var $usersAction = null;
	
	/**
     * 画像アップロードクラス
     *
     * @access  public
     */
    function execute()
    {
    	$garbage_flag = _ON;
    	$filelist = $this->uploadsAction->uploads($garbage_flag, "", array(_UPLOAD_THUMBNAIL_MAX_WIDTH_IMAGE, _UPLOAD_THUMBNAIL_MAX_HEIGHT_IMAGE));
    	
    	$user_id = $this->unique_id;
    	if($user_id == "0") return true;
    	$users_item_links =& $this->usersView->getUserItemLinkById($user_id, $this->items['item_id']);
    	if(isset($users_item_links['user_id'])) {
    		//以前のアバターの画像削除
			$upload_path = $users_item_links['content'];
			$pathList = explode("&", $upload_path);
			if(isset($pathList[1])) {
				$upload_id = intval(str_replace("upload_id=","", $pathList[1]));
				$result = $this->uploadsAction->delUploadsById($upload_id);
				if ($result === false) return false;
			}
    	}
    	
    	return true;
    }
}
?>
