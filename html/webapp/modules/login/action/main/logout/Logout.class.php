<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

 /**
 * loginモジュール:ログアウトボタン押下時
 *
 * @package     NetCommons Action
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Login_Action_Main_Logout extends Action
{
	// コンポーネントを使用するため
	var $session = null;
	var $configView = null;
	var $mobileAction = null;
	
    /**
     * ログイン画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
    	if ($mobile_flag == _OFF) {
	    	$config =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
	    	$autologin_login_cookie_name = $config['autologin_login_cookie_name']['conf_value'];
	    	$autologin_pass_cookie_name = $config['autologin_pass_cookie_name']['conf_value'];
	    	$path = ini_get("session.cookie_path");
			$domain = ini_get("session.cookie_domain");
			$secure = ini_get("session.cookie_secure");
	    	
	    	if($config['autologin_use']['conf_value'] == _AUTOLOGIN_NO &&
	    		$autologin_login_cookie_name != null && $autologin_login_cookie_name != "") {
	    		// ログインIDを残す設定になっているならば、ログアウトしてもログインIDだけは残す
	    		setcookie($autologin_login_cookie_name, '', time()- 3600, $path, $domain, $secure);
	    	}
	    	if($autologin_login_cookie_name != null && $autologin_login_cookie_name != "") {
	    		setcookie($autologin_pass_cookie_name, '', time()- 3600, $path, $domain, $secure);
	    	}
	    	//if ($config['use_mysession']['conf_value'] && 
			//	$config['session_name']['conf_value'] != '') {
			//	setcookie($config['session_name']['conf_value'], '', time()- 3600);
			//}
			
	    	//if ($config['use_mysession']['conf_value'] && 
			//	$config['session_name']['conf_value'] != '') {
			//	setcookie($config['session_name']['conf_value'], '', time()- 3600);
			//}
    	} else {
    		$this->mobileAction->setLogout();
    	}
        $this->session->close();
    	return 'success';
    }
}
?>