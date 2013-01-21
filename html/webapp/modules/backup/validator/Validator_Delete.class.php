<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * バックアップファイル削除チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Validator_Delete extends Validator
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
    	$db =& $container->getComponent("DbObject");
    	$authoritiesView =& $container->getComponent("authoritiesView");
    	$user_id = $session->getParameter("_user_id");
    	$user_auth_id = $session->getParameter("_user_auth_id");
    	
    	$upload_id = intval($attributes[0]);
    	$page_id = intval($attributes[1]);
    	$role_auth_id = $session->getParameter("_role_auth_id");
    	$authority = $authoritiesView->getAuthorityById($role_auth_id);
    	
    	$backup_uploads = $db->selectExecute("backup_uploads", array("upload_id" => $upload_id));
		if($backup_uploads === false || !isset($backup_uploads[0]))  {
			return $errStr;
		}
		
    	if($page_id == 0 && $backup_uploads[0]['url'] == '') {
    		if($user_auth_id != _AUTH_ADMIN) return $errStr;
    		return;
    	}
    	
		if($backup_uploads[0]['room_id'] != $page_id) {
			return $errStr;
		}
		$page =array();
		if($backup_uploads[0]['url'] == '') {
    		$page = $pagesView->getPageById($page_id);
    		if($page === false) {
    			return $errStr;
    		}
		}
		
		if(isset($page['page_id'])) {
			// pageあり
			if($page['page_id'] != $page['room_id'] ||
				$page['authority_id'] < _AUTH_CHIEF || $page['shortcut_flag'] == _ON ||
    			$page["display_flag"] == _PAGES_DISPLAY_FLAG_DISABLED) {
    			return $errStr;
    		}
		} else if(isset($params[0]) && $params[0] == "restore") {
			// リストアならば、共有設定に指定されているかどうかチェック
			$sites_where_param = array("(site_id = '".$backup_uploads[0]['site_id']."' OR url = '".$backup_uploads[0]['url']."')" => null);
			$sites = $db->selectExecute("sites", $sites_where_param);
			if($sites === false || !isset($sites[0]))  {
				return $errStr;
			}
			if(!($sites[0]['self_flag'] == _ON || $sites[0]['certify_flag'] == _ON)) {
				return $errStr;
			}
		}
		if($backup_uploads[0]['private_flag'] == _ON) {
			if($backup_uploads[0]['insert_user_id'] != $user_id) {
				return $errStr;
			}
		} else if($backup_uploads[0]['space_type'] == _SPACE_TYPE_PUBLIC && $backup_uploads[0]['thread_num'] == 1) {
			if($authority['public_createroom_flag'] == _OFF) {
				return $errStr;
			}
		} else if($backup_uploads[0]['space_type'] == _SPACE_TYPE_GROUP && $backup_uploads[0]['thread_num'] == 1) {
			if($authority['group_createroom_flag'] == _OFF) {
				return $errStr;
			}
		} else if($backup_uploads[0]['thread_num'] == 2) {
			$parent_page = $pagesView->getPageById(intval($backup_uploads[0]['parent_id']));
			if($parent_page === false) {
	    		return $errStr;
	    	}
			if(!isset($parent_page['page_id'])) {
				if($authority['group_createroom_flag'] == _OFF) {
					return $errStr;
				}
			} else {
				if($parent_page['authority_id'] < _AUTH_CHIEF) {
					// ルームの主担かどうか
					return $errStr;
				}	
			}
			
		}
		
    	$actionChain =& $container->getComponent("ActionChain");
        $action =& $actionChain->getCurAction();
    	BeanUtils::setAttributes($action, array("page"=>$page));
    	return;
    }
}
?>
