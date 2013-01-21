<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * バックアップチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Validator_Backup extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値		user_id
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
    	
    	$user_auth_id = $session->getParameter("_user_auth_id");
    	
    	$page_id = intval($attributes);
    	
    	if($page_id == 0) {
    		if($user_auth_id != _AUTH_ADMIN) return $errStr;
    		return;
    	}
    	
    	$page = $pagesView->getPageById($page_id);
    	if($page === false || !isset($page['page_id']) || $page['page_id'] != $page['room_id'] || 
    		$page['authority_id'] < _AUTH_CHIEF || $page['shortcut_flag'] == _ON ||
    		$page["display_flag"] == _PAGES_DISPLAY_FLAG_DISABLED) {
    		return $errStr;
    	}
    	// パブリックスペース直下ならば、管理者以外はバックアップできない
    	if($user_auth_id != _AUTH_ADMIN && $page['thread_num'] == 0 && $page['space_type'] == _SPACE_TYPE_PUBLIC && $page['private_flag'] == _OFF) {
    		return $errStr;
    	}
    	// サブグループでサブグループ作成権限がなければエラー
    	if($page['thread_num'] == 2 && $page['space_type'] == _SPACE_TYPE_GROUP && $page['private_flag'] == _OFF) {
    		$parent_page = $pagesView->getPageById($page['parent_id']);
    		if($parent_page === false || !isset($parent_page['page_id'])) {
    			return $errStr;
    		}
    		if($parent_page['createroom_flag'] == _OFF) { 
    			return $errStr;
    		}
    	}
    	$actionChain =& $container->getComponent("ActionChain");
        $action =& $actionChain->getCurAction();
    	BeanUtils::setAttributes($action, array("page"=>$page));
    	return;
    }
}
?>
