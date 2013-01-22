<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック(login_id, password,handle, email)
 * 必須チェック
 * リクエストパラメータ
 * var $user_id = null;
 * var $items = null;
 * var $items_public = null;
 * var $items_reception = null;
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_ItemsInputs extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値(user_id, items, items_public, items_reception)
	 *
	 * @param   string  $errStr エラー文字列(未使用：エラーメッセージ固定)
	 * @param   array   $params オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		// container取得
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$usersView =& $container->getComponent("usersView");
		$_system_user_id = $session->getParameter("_system_user_id");
		
		if(!isset($attributes['user_id'])) $attributes['user_id'] = "0";
		if($attributes['user_id'] != "0") {
			$user =& $usersView->getUserById($attributes['user_id']);
			if($user === false) return $errStr;	
			$edit_flag = true;
		} else {
			$attributes['user_id'] = "0";	
			$edit_flag = false;
		}
		if($session->getParameter(array("user", "regist", $attributes['user_id'])) !== null) {
			// 既に取得済み
			// 権限設定から戻る場合なのでスルー
			// すでにセッション情報が登録されているのに、itemsパラメータがくるとエラー
			if(isset($attributes['items'])) return $errStr;
			return;	
		}
		
		// システム管理者がシステム管理者以外で編集されそうになっている場合、エラー
		if($attributes['user_id'] == $_system_user_id && $session->getParameter("_user_id") != $_system_user_id) {
			return _INVALID_INPUT;
		}
		
		$where_params = array(
							"user_authority_id" => _AUTH_ADMIN		// 管理者固定
						);
		$show_items =& $usersView->getItems($where_params, null, null, null, array($this, "_getItemsFetchcallback"));
		if($show_items === false) return $errStr;
		foreach($show_items as $items) {
			$err_prefix = $items['item_id'].":";
			if(isset($attributes['items']) && isset($attributes['items'][$items['item_id']])) {
				$content = $attributes['items'][$items['item_id']];
			} else {
				$content = "";
			}
			
			if($items['define_flag'] == _ON && defined($items['item_name'])) $items['item_name'] = constant($items['item_name']);
			// 必須入力チェック
			if($items['require_flag'] == _ON && !($edit_flag == _ON && $items['tag_name'] == "password")) {
				// 必須項目
				if($content == "") {
					return $err_prefix.sprintf(_REQUIRED, $items['item_name']);
				}
			}
			
			if($items['tag_name'] == "login_id") {
				// 入力文字チェック
				$login_id = $content;
				$login_len = strlen($content);
				if($login_len < USER_LOGIN_ID_MINSIZE || $login_len > USER_LOGIN_ID_MAXSIZE) {
					return $err_prefix.sprintf(_MAXRANGE_ERROR, USER_ITEM_LOGIN, USER_LOGIN_ID_MINSIZE, USER_LOGIN_ID_MAXSIZE);
				}
				
				// 半角英数または、記号
				if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $login_id)) {
					return $err_prefix.sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_LOGIN);
				}
				
				// 重複チェック
				$where_params = array("login_id" => $login_id);
				$users =& $usersView->getUsers($where_params);
				$count = count($users);
				if($count >= 1 && $users[0]['user_id'] != $attributes['user_id']) {
					return $err_prefix.sprintf(USER_MES_ERROR_DUPLICATE, USER_ITEM_LOGIN, USER_ITEM_LOGIN);
				}
			} else if($items['tag_name'] == "password" && $content != "") {
				$new_password = $content;
				// 入力文字チェック
				$pass_len = strlen($new_password);
				if($pass_len < USER_PASSWORD_MINSIZE || $pass_len > USER_PASSWORD_MAXSIZE) {
					return $err_prefix.sprintf(_MAXRANGE_ERROR, USER_ITEM_PASSWORD, USER_PASSWORD_MINSIZE, USER_PASSWORD_MAXSIZE);
				}
				// 半角英数または、記号
				if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $new_password)) {
					return $err_prefix.sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_PASSWORD);
				}
			} else if($items['tag_name'] == "handle") {
				// 重複チェック
				$handle = $content;
				$where_params = array("handle" => $handle);
				$users =& $usersView->getUsers($where_params);
				$count = count($users);
				if($count >= 1 && $users[0]['user_id'] != $attributes['user_id']) {
					return $err_prefix.sprintf(USER_MES_ERROR_DUPLICATE, USER_ITEM_HANDLE, USER_ITEM_HANDLE);
				}
			} else if($items['tag_name'] == "active_flag_lang") {
				//システム管理者の場合、使用不可にはできない
				if($attributes['user_id'] == $_system_user_id && $content == _OFF) {
					return $err_prefix._INVALID_INPUT;
				}
			} else if($items['tag_name'] == "role_authority_name") {
				//システム管理者の場合、変更不可
				if($attributes['user_id'] == $_system_user_id && $content != _SYSTEM_ROLE_AUTH_ID) {
					return $err_prefix._INVALID_INPUT;
				}
			}
			if($items['type'] == "email" || $items['type'] == "mobile_email") {
				$email = $content;
				
				// 入力文字チェック
				if ( $email != "" && !strpos($email, "@") ) {
					return  $err_prefix.sprintf(_FORMAT_WRONG_ERROR, $items['item_name']);
				}
				// 重複チェック
				$userIdByMail = $usersView->getUserIdByMail($email);
				if (!empty($userIdByMail)
						&& $userIdByMail != $attributes['user_id']) {
					$errorMessage = $err_prefix
									. sprintf(USER_MES_ERROR_DUPLICATE, $items['item_name'], $items['item_name']);
					return $errorMessage;
				}
				// メール受信可否
				if(isset($attributes['items_reception']) && isset($attributes['items_reception'][$items['item_id']])) {
					if($items['allow_email_reception_flag'] == _OFF || 
						!($attributes['items_reception'][$items['item_id']] == _ON || 
							$attributes['items_reception'][$items['item_id']] == _OFF)) {
						return  $err_prefix._INVALID_INPUT;
					}
				}
				
			}
			
			// 公開設定
			if(isset($attributes['items_public']) && isset($attributes['items_public'][$items['item_id']])) {
				if($items['allow_public_flag'] == _OFF || 
					!($attributes['items_public'][$items['item_id']] == _ON || 
						$attributes['items_public'][$items['item_id']] == _OFF)) {
					return  $err_prefix._INVALID_INPUT;
				}
			}
		}
		// actionChain取得
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		if(isset($params[0])) {
			BeanUtils::setAttributes($action, array($params[0]=>$show_items));
		} else {
			BeanUtils::setAttributes($action, array("show_items"=>$show_items));
		}
		if(isset($user)) {
			//会員情報
			if(isset($params[1])) {
				BeanUtils::setAttributes($action, array($params[1]=>$user));
			} else {
				BeanUtils::setAttributes($action, array("user"=>$user));
			}
		}
		return;
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}
}
?>
