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
		$db =& $container->getComponent("DbObject");

		//$user_id = $session->getParameter("_user_id");

    	$email = $attributes[0];
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
		if($email != "") {
			$sql = "SELECT item_id, type FROM {items}".
				" WHERE ({items}.type='email' OR {items}.type='mobile_email')";
			$email_items = $db->execute($sql);
			if(count($email_items) > 0) {
				$sql = "SELECT {users_items_link}.user_id, {users_items_link}.item_id, {users_items_link}.content".
						" FROM {users_items_link}".
						" INNER JOIN {users} ON {users}.user_id={users_items_link}.user_id AND {users}.active_flag="._USER_ACTIVE_FLAG_ON.
						" WHERE {users_items_link}.item_id IN (";
 				$first = true;
 				$types = array();
				foreach($email_items as $email_item) {
					if($first == false)
						$sql .= ",";
					$sql .= $email_item['item_id'];
					$types[$email_item['item_id']] = $email_item['type'];
					$first = false;
				}
				$where_params = array();
				if (mb_strlen($email) < _MYSQL_FT_MIN_WORD_LEN) {
					$sql .= ")".
								" AND {users_items_link}.content=? ";
					$where_params = array(
	 									"{users_items_link}.content" => $email
									);
				} else {
					//$sql .= ")".
					//			" AND MATCH({users_items_link}.content) AGAINST ('\"".$db->stringMatchAgainst($email)."\"' IN BOOLEAN MODE)";
					$sql .= ")".
						" AND MATCH({users_items_link}.content) AGAINST (? IN BOOLEAN MODE)";
					$where_params = array(
	 									"{users_items_link}.content" => '"'.$email.'"'
									);
				}
				$chk_items =& $db->execute($sql, $where_params);
				$count = !empty($chk_items) ? count($chk_items) : 0;
			}

			if(count($email_items) == 0 || !isset($chk_items[0]['user_id'])) {
 				// 存在しない
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
 			$user =& $usersView->getUserById($chk_items[0]['user_id']);
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
			BeanUtils::setAttributes($action, array("send_email"=>$chk_items[0]['content']));
		}

    	return;
    }
}
?>
