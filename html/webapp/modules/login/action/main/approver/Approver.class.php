<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 自動登録-承認処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Login_Action_Main_Approver extends Action
{
	// リクエストパラメータを受け取るため
	var $user_id = null;
	var $activate_key = null;
	var $op = null;	//null or none_redirect(会員管理から承認された場合)
	
	// 使用コンポーネントを受け取るため
	var $configView = null;
	var $usersView = null;
	var $usersAction = null;
	var $mailMain = null;
	
	// 値をセットするため
	var $redirect_url = null;
	var $redirect_message = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$return_str = ($this->op == "none_redirect") ? $this->op : "success";
		
		$this->redirect_url = BASE_URL . INDEX_FILE_NAME;
		
		$renderer =& SmartyTemplate::getInstance();
		$renderer->assign('header_field',$this->configView->getMetaHeader());
		
		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID);
		if($config['autoregist_use']['conf_value'] != _ON) {
			$this->redirect_message = LOGIN_ERR_AUTOREGIST_USE;
    		return $return_str;
    	}
    	$user = $this->usersView->getUserById($this->user_id);
    	if($user === false) {
    		// 会員が削除されている
    		$this->redirect_message = LOGIN_ERR_NONE_EXISTS_USER;
			return $return_str;
    	}
    	if($user['active_flag'] == _USER_ACTIVE_FLAG_ON || $user['active_flag'] == _USER_ACTIVE_FLAG_OFF) {
			//既に承認済
			$this->redirect_message = LOGIN_MES_APPROVED;
			return $return_str;
		}
		
    	if($user['activate_key'] != "" && $user['activate_key'] != $this->activate_key) {
			$this->redirect_message = LOGIN_ERR_ACTIVATION;
			return $return_str;
		}
    	
    	if($config['autoregist_approver']['conf_value'] == _AUTOREGIST_ADMIN && ($user['active_flag'] == _USER_ACTIVE_FLAG_PENDING || $this->op == "none_redirect")) {
    		//管理者の承認が必要
    		// activate_key変更し、登録会員へメール送信
    		$activate_key = substr(md5(uniqid(mt_rand(), 1)), 0, LOGIN_ACTIVATE_KYE_LEN);
    		$user_params = array(
								"activate_key" => $activate_key,
								"active_flag" => _USER_ACTIVE_FLAG_MAILED
							);
    		$where_params = array("user_id" => $this->user_id);
    		
    		$result = $this->usersAction->updUsers($user_params, $where_params);
    		if ($result === false) {
    			return 'error';
    		}
    		// ----------------------------------------------------------------------
			// --- メール送信処理            		                              ---
			// ----------------------------------------------------------------------
			$mail_autoregist_subject = $config['mail_approval_subject']['conf_value'];
			$mail_autoregist_body = $config['mail_approval_body']['conf_value'];
			
			$where_params = array(
								"{users_items_link}.user_id" => $this->user_id,
								"(type='mobile_email' OR type='email')" => null
								//"tag_name " => "email"
							);
							
			$items = $this->usersView->getItems($where_params);
			if ($items === false) return 'error';
			if(!isset($items[0]['content'])) {
				$this->redirect_message = LOGIN_ERR_ACTIVATION;
				return $return_str;
			}
			$email_arr = array();
			foreach($items as $item) {
				$email_arr[] = $item['content'];
			}
			
			//$email = $items[0]['content'];
			
			// ユーザ自身の確認が必要
			$mail_autoregist_body .= "<br />".BASE_URL. INDEX_FILE_NAME.
						"?action=login_action_main_approver" .
						"&user_id=" . $this->user_id . "&activate_key=". $activate_key."&_header="._OFF. "<br />";
			
			foreach($email_arr as $email) {
				$user['email'] = $email;
				$user['type'] = "text";	// Text固定(html or text)
				$this->mailMain->addToUser($user);
			}			
			$this->mailMain->setSubject($mail_autoregist_subject);
			$this->mailMain->setBody($mail_autoregist_body);

			$this->mailMain->send();
			
			$this->redirect_message = LOGIN_MES_MAIL_ANNOUNCE;
			return $return_str;
    		
    	} else {
    		//会員自身の確認が必要
    		//自動的にアカウントを有効にする
    		if($user['active_flag'] != _USER_ACTIVE_FLAG_MAILED && $user['active_flag'] != _USER_ACTIVE_FLAG_PENDING) {
    			$this->redirect_message = LOGIN_ERR_ACTIVATION;
				return $return_str;
    		}
    		
    		$user_params = array(
								"activate_key" => "",
								"active_flag" => _USER_ACTIVE_FLAG_ON
							);
    		$where_params = array("user_id" => $this->user_id);
    		
    		$result = $this->usersAction->updUsers($user_params, $where_params);
			if($result === false) return 'error';
			
			$this->redirect_message = LOGIN_MES_ACTIVATION;
			return $return_str;
    	}
	}
}
?>