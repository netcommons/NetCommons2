<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * emailチェック
 * 3回まで再入力を許し、3回目以降は同一セッションでは、弾く
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Login_Validator_Forgetpass extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値(emai, code_date)
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
		$db =& $container->getComponent("DbObject");

		$email = preg_replace("/[　\s]+/u", "", $attributes[0]);
		$code_date = $attributes[1];
		$login_forgetpass_count = $session->getParameter("login_forgetpass_count");
		if(isset($login_forgetpass_count) && $login_forgetpass_count >= 3) {
			// 入力を受け付けない
			return LOGIN_MISS_EMAIL;
		}

		// 入力文字チェック
		if ( $email != "" && !strpos($email, "@") ) {
			return  sprintf(_FORMAT_WRONG_ERROR, "e-mail");
		}
		// 存在チェック
		$userIdByMail = $usersView->getUserIdByMail($email, true);
		if (empty($userIdByMail)) {
			if(!isset($login_forgetpass_count) || !is_int($login_forgetpass_count)) {
				$login_forgetpass_count = 1;
			} else {
				$login_forgetpass_count++;
			}
			$session->setParameter("login_forgetpass_count", $login_forgetpass_count);
			if($login_forgetpass_count >= 3) {
				//3回目以降
				return LOGIN_MISS_EMAIL;
			} else {
				return LOGIN_INCORRECT_EMAIL;
			}
		}

		// 存在しているものでも何度も送信されるのを防ぐ必要があるかも
		// 現状、処理しない
		$user =& $usersView->getUserById($userIdByMail);
		if($user === false || !isset($user['user_id'])) return sprintf(_INVALID_SELECTDB, "users");

		if(isset($code_date)) {
			if($code_date != substr($user['password'], 0, 10)) {
				// 正しくないcode_dataがきた場合
				return LOGIN_MISS_CODEDATA;
			}
		}

		// Actionにデータセット
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("user"=>$user));
		BeanUtils::setAttributes($action, array("send_email"=>$email));

		return;
	}
}
?>
