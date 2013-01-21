<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック(login_id, password,handle, email)
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Validator_ItemInputs extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値(item_id,content,confirm_content,current_content)
     *                  
     * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
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
		//$user_id = $session->getParameter("_user_id");
    	$user_auth_id = $session->getParameter("_user_auth_id");
    	
    	if(isset($attributes['user_id'])) {
    		$edit_user_id = $attributes['user_id'];
    	} else {
    		$edit_user_id = $session->getParameter("_user_id");
    	}
    	
		// システム管理者がシステム管理者以外で編集されそうになっている場合、エラー
		if($edit_user_id == $session->getParameter("_system_user_id") && $session->getParameter("_user_id") != $session->getParameter("_system_user_id")) {
			return _INVALID_INPUT;
		}
    	
    	$item_id = intval($attributes['item_id']);
    	$items =& $usersView->getItemById($item_id);
 		if($items === false) return _INVALID_INPUT;
 		if(!isset($items['item_id'])) return;
 		
 		if($items['define_flag'] == _ON && defined($items['item_name'])) $items['item_name'] = constant($items['item_name']);
 		
 		$content = $attributes['content'];
 		
 		// 必須入力チェック
 		if($items['tag_name'] != "password" && $items['require_flag'] == _ON && (!isset($content) || $content == "")) {
 			return sprintf(_REQUIRED, $items['item_name']);
 		}
 		
 		if($items['tag_name'] == "login_id") {
 			// 入力文字チェック
 			$login_id = $content;
 			$login_len = strlen($content);
	    	if($login_len < USER_LOGIN_ID_MINSIZE || $login_len > USER_LOGIN_ID_MAXSIZE) {
	    		return sprintf(_MAXRANGE_ERROR, USER_ITEM_LOGIN, USER_LOGIN_ID_MINSIZE, USER_LOGIN_ID_MAXSIZE);
	    	}
	    	
	    	// 半角英数または、記号
	    	if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $login_id)) {
	    		return sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_LOGIN);
	    	}
 			
 			// 重複チェック
 			$where_params = array("login_id" => $login_id);
 			$users =& $usersView->getUsers($where_params);
 			$count = count($users);
 			if($count >= 1 && $users[0]['user_id'] != $edit_user_id) {
 				return sprintf(USERINF_MES_ERROR_DUPLICATE, USER_ITEM_LOGIN, USER_ITEM_LOGIN);
 			}
 		} else if($items['tag_name'] == "password") {
	    	$new_password = $content;
	    	$confirm_password = $attributes['confirm_content'];
	    	$current_password = $attributes['current_content'];
	    	
	    	$err_mes  = "";
	    	//必須チェック
	    	//管理者の場合、現在のパスワードのチェックは行わない
	    	$edit_user =& $usersView->getUserById($edit_user_id);
	    	//if($session->getParameter("_system_user_id") != $edit_user['user_id'] && $edit_user['user_authority_id'] >= $user_auth_id) {
	    	
	    	if($session->getParameter("_system_user_id") != $session->getParameter("_user_id") && $edit_user['user_authority_id'] >= $user_auth_id) {
	    		//if($items['require_flag'] == _ON && (!isset($content) || $content == "")) {
 				//	if($err_mes != "") $err_mes .= "<br />";
	    		//	$err_mes .= sprintf(_REQUIRED, USERINF_CURRENT_PASS);
 				//}
		    	if((!isset($current_password) || $current_password == "")) {
		    		$err_mes .= sprintf(_REQUIRED, USERINF_CURRENT_PASS);
		    	}
	    	}
	    	if(!isset($new_password) || $new_password == "") {
	    		if($err_mes != "") $err_mes .= "<br />";
	    		$err_mes .= sprintf(_REQUIRED, USERINF_NEW_PASS);
	    	}
	    	if(!isset($confirm_password) || $confirm_password == "") {
	    		if($err_mes != "") $err_mes .= "<br />";
	    		$err_mes .= sprintf(_REQUIRED, USERINF_CONFIRM_NEW_PASS);
	    	}
	    	if($err_mes != "") return $err_mes;
	    	
	    	//管理者の場合、現在のパスワードのチェックは行わない
	    	if($session->getParameter("_system_user_id") != $session->getParameter("_user_id") && $edit_user['user_authority_id'] >= $user_auth_id) {
		    	if($edit_user['password'] != md5($current_password)) {
		    		return USERINF_ERR_CURRENT_PASS_DISACCORD;
		    	}
	    	}
	    	if($new_password != $confirm_password) {
	    		return USERINF_ERR_PASS_DISACCORD;
	    	}
	    	// 入力文字チェック
	    	$pass_len = strlen($new_password);
	    	if($pass_len < USER_PASSWORD_MINSIZE || $pass_len > USER_PASSWORD_MAXSIZE) {
	    		return sprintf(_MAXRANGE_ERROR, USER_ITEM_PASSWORD, USER_PASSWORD_MINSIZE, USER_PASSWORD_MAXSIZE);
	    	}
	    	// 半角英数または、記号
	    	if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $new_password)) {
	    		return sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_PASSWORD);
	    	}
 		} else if($items['tag_name'] == "handle") {
 			// 重複チェック
 			$handle = $content;
 			$where_params = array("handle" => $handle);
 			$users =& $usersView->getUsers($where_params);
 			$count = count($users);
 			if($count >= 1 && $users[0]['user_id'] != $edit_user_id) {
 				return sprintf(USERINF_MES_ERROR_DUPLICATE, USER_ITEM_HANDLE, USER_ITEM_HANDLE);
 			}
 		} else if($items['tag_name'] == "role_authority_name") {
 			//システム管理者の場合、権限変更できない
 			if($session->getParameter("_system_user_id") == $edit_user_id) {
 				return _INVALID_INPUT;
 			}
 		} else if($items['tag_name'] == "active_flag_lang") {
 			//システム管理者の場合、使用不可にはできない
 			//$where_params = array("user_id" => $edit_user_id);
 			//$users =& $usersView->getUsers($where_params);
 			//if($users[0]['system_flag'] == _ON) {
 			if($session->getParameter("_system_user_id") == $edit_user_id) {
 				return _INVALID_INPUT;
 			}
 		} 
 		if($items['type'] == "email" || $items['type'] == "mobile_email") {
 			$email = $content;
 			
 			// 入力文字チェック
 			if ( $email != "" && !strpos($email, "@") ) {
    			return  sprintf(_FORMAT_WRONG_ERROR, $items['item_name']);
 			}
 			// 重複チェック
 			if($email != "") {
	 			$where_param = array(
	 									"({items}.type = 'email' OR {items}.type = 'mobile_email')  " => null,
										"{users_items_link}.content" => $email
									);
	 			$chk_items =& $usersView->getItems($where_param);
	 			$count = count($chk_items);
	 			if($count >= 1 && $chk_items[0]['user_id'] != $edit_user_id) {
	 				return sprintf(USERINF_MES_ERROR_DUPLICATE, $items['item_name'] , $items['item_name'] );
	 			}
 			}
 			// メール受信可否
 			if(!isset($attributes['email_reception_flag']) || !($attributes['email_reception_flag'] == _ON || $attributes['email_reception_flag'] == _OFF)) {
 				return  _INVALID_INPUT;
 			}
 			if($attributes['email_reception_flag'] == _OFF && $items['allow_email_reception_flag'] == _OFF) {
 				//受信可否を設定できないにも関わらず、受信拒否をしようとした
 				return  sprintf(USERINF_ERR_RECEPTION, $items['item_name']);
 			}
 		}
 		
 		// 公開設定
		if(!isset($attributes['public_flag']) || !($attributes['public_flag'] == _ON || $attributes['public_flag'] == _OFF)) {
			return  _INVALID_INPUT;
		}
		if($attributes['public_flag'] == _OFF && $items['allow_public_flag'] == _OFF) {
			//公開可否を設定できないにも関わらず、公開拒否をしようとした
			return  sprintf(USERINF_ERR_PUBLIC, $items['item_name']);
		}
 		
    	return;
    }
}
?>
