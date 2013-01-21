<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの存在チェック（会員詳細）
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Validator_ItemEdit extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$usersView =& $container->getComponent("usersView");
		
		//編集するuser情報
		$user_id = $session->getParameter("_user_id");
		if($user_id == "0")  return $errStr;
    	$_user_auth_id = $session->getParameter("_user_auth_id");
    	$user =& $usersView->getUserById($user_id);
    	if($user === false) return $errStr;	
    		
    	// item_id取得
    	if(is_array($attributes) && $attributes[1] != "0") {
    		$item_id = intval($attributes[0]);
    		
    		//編集先user情報
    		$edit_user_id = $attributes[1];
    		$edit_users =& $usersView->getUserById($edit_user_id);
    		if($edit_users === false) return $errStr;	
    		$edit_user_auth_id = $edit_users['user_authority_id'];
    	} else {
    		if(is_array($attributes)) {
				$item_id = intval($attributes[0]);
    		} else {
    			$item_id = intval($attributes);
    		}
    		//編集先user情報(自分自身)
    		$edit_user_id = $user_id;
    		$edit_user_auth_id = $_user_auth_id;
    		$edit_users =& $user;
    	}
 		$items = $usersView->getItemById($item_id);
 		if($items === false) return $errStr;
 		if(!isset($items['item_id'])) {
 			return USERINF_ERR_NONEEXISTS;	
 		}
 		
 		//
 		// display_flag
 		//
 		if($items['display_flag'] == _OFF) {
 			//非表示項目
 			return $errStr;
 		}		
 		//
 		// 権限チェック
 		// 
 		//if($users['system_flag'] == _ON && $_user_auth_id == _AUTH_ADMIN) {
 		if($user['system_flag'] == _ON) {
 			// システム管理者
 			// すべて許す
 		//} else if($_user_auth_id < $edit_user_auth_id) {
 		//	// 自分より権限が上のものは編集できない
 		//	return $errStr;
 		} else {
 			if($user_id != $edit_user_id) {
 				// 他人
 				if($_user_auth_id <= $edit_user_auth_id && $items['over_public_flag'] != USER_EDIT) {
 					// 自分の権限と同じか、自分の権限より大きいものを編集しようとしている
 					return $errStr;
 				}else if($_user_auth_id > $edit_user_auth_id && $items['under_public_flag'] != USER_EDIT) {
 					// 小さいものを編集しようとしている
 					return $errStr;
 				}
 			} else {
 				//自分自身(self)
 				if($items['self_public_flag'] != USER_EDIT) {
 					return $errStr;
 				}
 			}
 			/*
 			if($_user_auth_id == _AUTH_ADMIN) {
	 			// 管理者はすべて許す
	 		} else if($_user_auth_id == _AUTH_CHIEF) {
	 			// chief_public_flag
	 			if($user_id != $edit_user_id && $items['other_chief_public_flag'] != USER_EDIT) {
	 				// 他人
	 				return $errStr;
				} else if($user_id == $edit_user_id && $items['self_chief_public_flag'] != USER_EDIT) {
					// 自分自身
					return $errStr;
	 			}
	 		} else {
	 			// self_public_flag  other_public_flag
	 			if($user_id != $edit_user_id && $items['other_public_flag'] != USER_EDIT) {
	 				// 他人
	 				return $errStr;
				} else if($user_id == $edit_user_id && $items['self_public_flag'] != USER_EDIT) {
					// 自分自身
					return $errStr;
	 			}
	 		}
	 		*/
 		}
 		
 		//
 		// Actionにデータセット
 		//

		// actionChain取得
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		if(isset($params[0])) {
			BeanUtils::setAttributes($action, array($params[0]=>$items));
		} else {
			BeanUtils::setAttributes($action, array("items"=>$items));
		}
		if(isset($params[1])) {
			BeanUtils::setAttributes($action, array($params[1]=>$edit_users));
		} else {
			BeanUtils::setAttributes($action, array("user"=>$edit_users));
		}
		
    	return;
    }
}
?>
