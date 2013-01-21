<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

 /**
 * 会員登録を受け付ける場合の自動登録画面表示
 * 
 * @package     NetCommons Action
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Login_View_Mobile_Autoregist extends Action
{
	// コンポーネントを使用するため
	var $configView = null;
	var $usersView = null;
	var $actionChain = null;
	var $session = null;
	
	// 値をセットするため
	var $items_errmsg = array();
	var $main_errmsg = null;
	var $error_exists = null;
	var $confItems = null;
	var $autoregist_use_items = array();
	var $autoregist_use_items_req = array();
	var $autoregist_disclaimer = "";
	var $autoregist_input_key = "";

	var $autoregist_disclaimer_ok = null;

	var $block_id = null;
	var $session_name = null;
	var $session_id = null;

	/**
	 * 会員登録を受け付ける場合の自動登録画面表示
	 *
	 * @access  public
	 */
	function execute()
	{
		$items_errList =& $this->actionChain->getErrorListByName( "login_action_main_autoregist" );
		if( $items_errList != null ) {
			if( $items_errList->isExists() ) {
				$this->error_exists = _ON;
				$errmsg_arr = $items_errList->getSelfMessages();
				foreach( $errmsg_arr as $errmsg ) {
					$tmp_errmsg = explode( ":", $errmsg );
					if( count( $tmp_errmsg ) == 2 ) {
						$this->items_errmsg[ $tmp_errmsg[0] ] = $tmp_errmsg[1];
					}
					else {
						$this->main_errmsg = $tmp_errmsg[0];
					}
				}
			}
		}

		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID);
		
		if($config['autoregist_use']['conf_value'] != _ON) {
			$errorList =& $this->actionChain->getCurErrorList();
			$errorList->add(get_class($this), LOGIN_MES_ERR_AUTOREGIST);
			return 'error';	
		}
		$_system_user_id = $this->session->getParameter("_system_user_id");
		$this->confItems =& $this->usersView->getShowItems($_system_user_id, _AUTH_ADMIN, array('display_flag'=>_ON), true);
		if($this->confItems === false) return 'error';
		
		$autoregist_use_items = explode("|", $config['autoregist_use_items']['conf_value']);
		foreach($autoregist_use_items as $autoregist_use_item) {
			$buf_arr = explode(":", $autoregist_use_item);
			if(isset($buf_arr[0]) && $buf_arr[0] != "") {
				$this->autoregist_use_items[$buf_arr[0]] = $buf_arr[0];
				$this->autoregist_use_items_req[$buf_arr[0]] = $buf_arr[1];
			}
		}
		
		$this->autoregist_disclaimer = $config['autoregist_disclaimer']['conf_value'];
		if( $this->autoregist_disclaimer == "" ) {
			$this->autoregist_disclaimer_ok = _ON;
		}
		$this->autoregist_use_input_key = $config['autoregist_use_input_key']['conf_value'];

		$this->session_name = session_name();
		$this->session_id = session_id();
		
		return 'success';
	}
}
?>