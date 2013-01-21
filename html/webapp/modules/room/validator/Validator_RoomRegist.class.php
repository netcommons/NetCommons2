<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム作成時チェック
 * サブグループ作成権限チェック
 * ルーム削除時チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Validator_RoomRegist extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値array(parent_page_id,current_page_id)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$session =& $container->getComponent("Session");
    	$pagesView =& $container->getComponent("pagesView");
    	$authCheck =& $container->getComponent("authCheck");
    	//$request =& $container->getComponent("Request");
    	
    	$parent_page_id = intval($attributes[0]);
        $current_page_id = intval($attributes[1]);
        // mode inf or create or delete or chgdisplay
        $mode = "create";
        if(isset($params[0])) {
        	$mode = $params[0];
        }
        
        $user_id = $session->getParameter('_user_id');
        $page = null;
        if($parent_page_id != 0) {
        	$page =& $pagesView->getPageById($parent_page_id);
	        if($page === false || !isset($page['page_id'])) {
	        	//親ページなし
	        	return $errStr;	
	        }
	        
	        if($mode != "inf" && $session->getParameter("_open_private_space") == _OFF && $page['private_flag']) {
	        	//プライベートスペース
	        	return $errStr;	
	        } else if($page['thread_num'] > 1) {
	        	//サブグループは一階層のみ
	        	return $errStr;	
	        }
        }
        $current_page = null;
        if(isset($current_page_id) && $current_page_id != 0) {
        	//
        	// ルーム編集時チェック
        	//
	        //カレントページ権限チェック
        	
        	$current_page =& $pagesView->getPageById($current_page_id);
        	if($current_page === false || !isset($current_page['page_id'])) {
        		//カレントページなし
        		return $errStr;
        	}
	        $_user_auth_id = $session->getParameter("_user_auth_id");
	        if($current_page['thread_num'] != 0 && $parent_page_id == 0) {
	        	//親ページ指定なし
	        	return $errStr;	
	        } else if($parent_page_id != 0 && $current_page['parent_id'] != 0 && $current_page['parent_id'] != $parent_page_id) {
	        	//親子関係相違あり
	        	return $errStr;	
	        }
	        // 編集権限チェック
	        if($mode != "inf" && $session->getParameter("_open_private_space") == _OFF && $current_page['private_flag']) {
	        	//プライベートスペース
	        	return $errStr;	
	        }else if($current_page['thread_num'] == 0  && ($mode == "delete" || $mode == "chgdisplay")) {
	        	return $errStr;	
	        }else if($current_page['thread_num'] == 0 && $_user_auth_id != _AUTH_ADMIN &&
	        		 !($mode == "inf"  && $current_page['private_flag']) && $current_page['private_flag'] == _OFF) {
	        	// 深さが0の編集する権限は、管理者のみ
	        	// 深さ0のものは削除不可
	        	return $errStr;	
	        }else if($current_page['thread_num'] == 1) {
	        	// ルーム	
	        	$auth_id = $authCheck->getPageAuthId($user_id, $current_page_id);
	        	if($auth_id < _AUTH_CHIEF) {
		        	//ルーム編集権限なし
		        	return $errStr;	
		        }
	        } else if($current_page['thread_num'] == 2) {
	        	// サブグループ
	        	//if($mode != "inf") {		        	
		        	// 作った本人であれば、チェックしない
        			if($current_page['insert_user_id'] != $user_id) {
		        		$auth_id = $authCheck->getPageAuthId($user_id, $parent_page_id);
		        		if($auth_id < _AUTH_CHIEF) {
			        		//ルーム編集権限なし
			        		return $errStr;	
			        	}
        			}
			        $auth_id = $authCheck->getPageAuthId($user_id, $current_page_id);
		        	if($auth_id < _AUTH_CHIEF) {
			        	//ルーム編集権限なし
			        	return $errStr;	
			        }
	        	//}
	        } else if($current_page['thread_num'] > 2) {
	        	return $errStr	;	
	        }
	        
	        // 削除する場合は、常に親のルーム作成権限＋そのルームで主担である必要あり
	        if($mode == "delete") {
	        	$createroom_flag = $authCheck->getPageCreateroomFlag($user_id, $parent_page_id);
	        	if($createroom_flag == _OFF) {
		        	//ルーム作成権限なし
		        	return $errStr;	
		        }
	        }
        } else {
        	//
        	// ルーム新規作成時チェック	
        	//
        	//ルーム作成時チェック
        	$current_page = null;
	        $createroom_flag = $authCheck->getPageCreateroomFlag($user_id, $parent_page_id);
	        if(!$createroom_flag) {
	        	//ルーム作成権限なし
	        	return $errStr;	
	        }
        }
        $actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
	    BeanUtils::setAttributes($action, array("parent_page"=>$page));
	    BeanUtils::setAttributes($action, array("page"=>$current_page));
    }
}
?>
