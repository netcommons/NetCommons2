<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * パスワード紛失、メール送信処理
 *
 * @package     NetCommons Action
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Login_Action_Main_Forgetpass extends Action
{
	// リクエストパラメータを受け取るため
	var $email = null;
	var $code_date = null;
	
	// バリデートによりセット
	var $user = null;
	var $send_email = null;

	// 使用コンポーネントを受け取るため
	var $mailMain = null;
	var $usersAction = null;
	var $configView = null;
	
	// 値をセットするため
	var $redirect_url = null;
	var $redirect_message = null;
	
    /**
     * パスワード紛失、メール送信処理
     *
     * @access  public
     */
    function execute()
    {
    	$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID);
		$userInfo = LOGIN_HANDLE . _SEPARATOR2 . $this->user['handle'] . "\n"
					. LOGIN_NAME . _SEPARATOR2 . $this->user['login_id'] . "\n";

		if($this->code_date == null) {
    		$new_code_date = substr($this->user['password'], 0, 10);
    		
			$mail_get_password_subject = $config['mail_get_password_subject']['conf_value'];
			$mail_get_password_body = $config['mail_get_password_body']['conf_value'];
			$mail_get_password_body .= "<br /><br />";

			$mail_get_password_body .= htmlspecialchars($userInfo)
										. '<br />'
										. BASE_URL. INDEX_FILE_NAME
											. '?action=login_action_main_forgetpass'
											. '&email=' . $this->send_email
											. '&code_date=' . $new_code_date
											. '&_header=' . _OFF
										. '<br />';

    		$this->mailMain->setSubject($mail_get_password_subject);
			$this->mailMain->setBody($mail_get_password_body);
    	} else {
    		// 新規パスワード発行処理
    		$newpass = $this->makePassword();
    		$this->redirect_url = BASE_URL. INDEX_FILE_NAME;
			$this->redirect_message = LOGIN_MES_NEW_PASS;
    		
    		$mail_new_password_subject = $config['mail_new_password_subject']['conf_value'];
			$mail_new_password_body = $config['mail_new_password_body']['conf_value'];
			$mail_new_password_body .= "<br /><br />";

			$userInfo .= LOGIN_PASSWORD . _SEPARATOR2 . $newpass . "\n";

			$mail_new_password_body .= htmlspecialchars($userInfo)
										. '<br />'
										. '<br />'
										. $this->redirect_url
										. '<br />';

			$this->mailMain->setSubject($mail_new_password_subject);
			$this->mailMain->setBody($mail_new_password_body);
			
			// パスワード変更日時更新
					$params = array(
						"password"=> md5($newpass),
						"password_regist_time" => timezone_date()
					);
			$where_params = array("user_id" => $this->user['user_id']);
			$result = $this->usersAction->updUsers($params, $where_params);
			if ($result === false) return 'error';
    	}

		$this->user['email'] = $this->send_email;
		$this->user['type'] = "text";	// Text固定(html or text)

		$this->mailMain->addToUser($this->user);
		$this->mailMain->send();
		if($this->code_date != null) {
			$renderer =& SmartyTemplate::getInstance();
			$renderer->assign('header_field',$this->configView->getMetaHeader());
    		return 'success_new_pass';
		}
		return 'success';
    }
    
    /**
	 * パスワード生成関数
	 * TODO:他でも使用する場合、共通関数にする必要あり
	 * @param  int	    $digit	桁数
	 * @return	string	パスワード文字列
	 **/
	function makePassword($digit=10) {
		$makepass = '';
		$syllables = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		srand((double)microtime()*1000000);
		for ($count = 1; $count <= $digit; $count++) {
			$makepass .= substr($syllables, (rand() % 62), 1);
		}
		return $makepass;
	}
}
?>
