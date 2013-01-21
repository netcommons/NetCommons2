<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

 /**
 * 会員登録を受け付ける場合の自動登録の前の規約承諾画面表示
 * 
 * @package     NetCommons Action
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Login_View_Mobile_Disclaimer extends Action
{
	// コンポーネントを使用するため
	var $configView = null;
	var $usersView = null;
	var $session = null;

	/**
	 * 規約確認画面表示
	 *
	 * @access  public
	 */
	function execute()
	{
		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID);
		
		if($config['autoregist_use']['conf_value'] != _ON) {
			$errorList =& $this->actionChain->getCurErrorList();
			$errorList->add(get_class($this), LOGIN_MES_ERR_AUTOREGIST);
			return 'error';	
		}
		$_system_user_id = $this->session->getParameter("_system_user_id");
		$this->items =& $this->usersView->getShowItems($_system_user_id, _AUTH_ADMIN);
		if($this->items === false) return 'error';
		
		$this->autoregist_disclaimer = $config['autoregist_disclaimer']['conf_value'];

		if( $this->autoregist_disclaimer == FALSE ) {
			return 'gotonext';
		}
		else {
			return 'success';
		}
	}
}
?>