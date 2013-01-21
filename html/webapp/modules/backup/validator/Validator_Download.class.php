<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * バックアップファイル　ダウンロードチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Validator_Download extends Validator
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
    	// Backup_View_Main_Initと同等のチェック
    	// Viewフィルタを呼んでいないのでエラーリストに追加はされても画面に表示はされないが
    	// 不正にファイルをダウンロードしようとしたときのみエラーとなるので処理しない
    	$upload_id = $attributes;
    	$container =& DIContainerFactory::getContainer();
    	$session =& $container->getComponent("Session");
    	$authoritiesView =& $container->getComponent("authoritiesView");
    	$pagesView =& $container->getComponent("pagesView");
    	$db =& $container->getComponent("DbObject");
    	
    	$user_id = $session->getParameter("_user_id");
		$role_auth_id = $session->getParameter("_role_auth_id");
		$authority = $authoritiesView->getAuthorityById($role_auth_id);
		$user_auth_id = $session->getParameter("_user_auth_id");
		$site_id = $session->getParameter("_site_id");
		
		$where_str = " ({backup_uploads}.private_flag="._OFF." OR {backup_uploads}.insert_user_id='" . $user_id . "') ";
		if($authority['public_createroom_flag'] == _OFF) {
    		// パブリックスペース
    		$where_str .= " AND ({backup_uploads}.space_type != "._SPACE_TYPE_PUBLIC.") " ;
    	}
    	if($authority['group_createroom_flag'] == _OFF) {
    		// グループスペース以外か、プライベートスペース
    		$where_str .= " AND ({backup_uploads}.space_type != "._SPACE_TYPE_GROUP." OR {backup_uploads}.private_flag="._ON." ) " ;
    	}
    	
		$uploads = $db->selectExecute("backup_uploads", array("upload_id" => $upload_id, $where_str => null));
		if($uploads === false || !isset($uploads[0])) {
			return $errStr;	
		}
		$upload =& $uploads[0];
		$list_show_flag = false;
		if($upload['room_id'] == 0) {
			// フルバックアップ
			if($user_auth_id != _AUTH_ADMIN) return $errStr;
			$list_show_flag = true;
    	} else if($upload['thread_num'] == 0 && $upload['space_type'] == _SPACE_TYPE_PUBLIC) {
			// パブリックスペース直下
			$page = $pagesView->getPageById(intval($upload['room_id']));
			if(isset($page['authority_id']) && 
				$page['authority_id'] >= _AUTH_CHIEF) {
				// ルームの主担かどうか
				$list_show_flag = true;
			}
		} else if($upload['thread_num'] == 2) {
			// サブグループ
			$page = $pagesView->getPageById(intval($upload['parent_id']));
			if(isset($page['authority_id']) && 
				$page['authority_id'] >= _AUTH_CHIEF) {
				// ルームの主担かどうか
				$list_show_flag = true;
			}
		} else {
			// グループルーム　パブリックルーム
			if($upload['site_id'] == $site_id) {
				// 自サイト
				$page = $pagesView->getPageById(intval($upload['room_id']));
				if(isset($page['authority_id']) && 
					$page['authority_id'] >= _AUTH_CHIEF) {
					// そのルームの主担かどうかもチェック
					$list_show_flag = true;
				} else {
					// 自サイトだが、存在していないルームならば無条件で表示
					$list_show_flag = true;
				}
			} else {
				// 他サイトの場合、sitesテーブルに登録されているかどうかチェック
				$sites_where_param = array("(site_id = '".$upload['site_id']."' OR url = '".$upload['url']."')" => null);
				$sites = $db->selectExecute("sites", $sites_where_param);
				if($sites === false || !isset($sites[0]) || !($sites[0]['self_flag'] == _ON || $sites[0]['certify_flag'] == _ON))  {
					$list_show_flag = false;
				} else {
					$list_show_flag = true;
				}
			}
		}
		if($list_show_flag === false) {
			return $errStr;	
		}
    	return;
    }
}
?>
