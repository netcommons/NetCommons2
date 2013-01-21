<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報-退会処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Action_Main_Withdraw extends Action
{
	// リクエストパラメータを受け取るため
	var $user_id = null;

	// 使用コンポーネントを受け取るため
	var $configView = null;
	var $session = null;
	var $preexecuteMain = null;
	var $mailMain = null;
	var $usersAction = null;
	var $usersView = null;

	// 値をセットするため
	var $redirect_url = null;
	var $redirect_message = null;
	var $time = 5;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID);
		if($config === false) return 'error';

		//$config_sitename = $configView->getConfigByConfname(_SYS_CONF_MODID, "sitename");
		//if($config_sitename === false) return 'error';
		//$sitename = $config_sitename['conf_value'];
		$meta =& $this->session->getParameter("_meta");

		//自動的に退会させる
    	$params = array(
									"user_id" => $this->user_id,
									"_header" => "0",
									"_output" => "0"
								);
    	$html = $this->preexecuteMain->preExecute("user_action_admin_delete", $params);
    	$error_prefix = ERROR_MESSAGE_PREFIX;

    	if(strlen($html) != strlen($error_prefix) && preg_match("/^".$error_prefix."/i", $html)) {
    		// エラー
    		return 'error';
    	}


		// ----------------------------------------------------------------------
		// --- 管理者へのメール通知処理　　　　　            		          ---
		// ----------------------------------------------------------------------
		if($config['withdraw_membership_send_admin']['conf_value'] == _ON) {
			// 管理者取得
			$users = $this->usersView->getSendMailUsers(null, _AUTH_ADMIN, "text");
			$this->mailMain->setToUsers($users);

			$subject = str_replace("{X-SITE_NAME}", $meta['sitename'], $config['mail_withdraw_membership_subject']['conf_value']);
			$subject = str_replace("{X-HANDLE}", $this->session->getParameter("_handle"), $subject);
			$body = str_replace("{X-SITE_NAME}", htmlspecialchars($meta['sitename']), $config['mail_withdraw_membership_body']['conf_value']);
			$body = str_replace("{X-HANDLE}", htmlspecialchars($this->session->getParameter("_handle")), $body);

			$this->mailMain->setSubject($subject);
			$this->mailMain->setBody($body);

			//$tags["X-SITE_NAME"] = $meta['sitename'];
			//$this->mailMain->assign($tags);
			$this->mailMain->send();
		}

		//
    	// リダイレクト
    	//
    	$this->redirect_url = BASE_URL . INDEX_FILE_NAME;

		$renderer =& SmartyTemplate::getInstance();
		$renderer->assign('header_field',$this->configView->getMetaHeader());
		$this->redirect_message = USERINF_MES_WITHDRAW_REDIRECT;

		$this->session->close();

		return 'success';
	}
}
?>
