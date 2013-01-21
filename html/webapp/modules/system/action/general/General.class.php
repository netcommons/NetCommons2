<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システムConfig登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class System_Action_General extends Action
{
	
	// リクエストパラメータを受け取るため
	var $sitename = null;
	var $language = null;
	var $server_TZ = null;
	var $default_TZ = null;
	var $first_choice_startpage = null;
	var $upload_max_capacity_group = null;
	
	var $default_entry_role_auth_public = null;
	var $default_entry_role_auth_group = null;
	
	var $session_gc_maxlifetime = null;
	var $add_private_space_name = null;
	// var $open_private_space = null;
	
	var $autologin_use = null;
	var $autologin_login_cookie_name = null;
	var $autologin_pass_cookie_name = null;
	var $login_autocomplete = null;
	var $use_ssl = null;
	
	var $closesite = null;
	var $closesite_text = null;
	
	//使用コンポーネント
	var $config = null;
	
    /**
     * DB登録
     *
     * @access  public
     */
    function execute()
    { 
        // sanity check for null value
        $value = ($this->sitename) ? $this->sitename : SYSTEM_DEFAULT_SITE_NAME;
		if (!$this->_update('sitename', $value)) return 'error';

		$value = $this->language;
		if (!$this->_update('language', $value)) return 'error';
		
		// intval returns 0 for null
		if (!$this->_update('server_TZ', intval($this->server_TZ))) return 'error';
		if (!$this->_update('default_TZ', intval($this->default_TZ))) return 'error';
		
		$value = ($this->first_choice_startpage) ? $this->first_choice_startpage : SYSTEM_DEFAULT_START_PAGE_ID;
		if (!$this->_update('first_choice_startpage', $value)) return 'error';
		
		$value = isset($this->upload_max_capacity_group) ? intval($this->upload_max_capacity_group) : SYSTEM_DEFAULT_UPLOAD_CAPACITY;
		if (!$this->_update('upload_max_capacity_group', $value)) return 'error';
		
		$value = ($this->default_entry_role_auth_public) ? intval($this->default_entry_role_auth_public) : _ROLE_AUTH_GUEST;
		if (!$this->_update('default_entry_role_auth_public', $value)) return 'error';
		
		$value = ($this->default_entry_role_auth_group) ? intval($this->default_entry_role_auth_group) : _ROLE_AUTH_GENERAL;
		if (!$this->_update('default_entry_role_auth_group', $value)) return 'error';
		
		$value = ($this->add_private_space_name) ? $this->add_private_space_name : SYSTEM_DEFAULT_PRIVATE_SPACE_NAME;
		if (!$this->_update('add_private_space_name', $value)) return 'error';
		
		$value = ($this->autologin_use) ? intval($this->autologin_use) : _OFF;
		if (!$this->_update('autologin_use', $value)) return 'error';
		
		$value = ($this->autologin_login_cookie_name) ? $this->autologin_login_cookie_name : SYSTEM_DEFAULT_AUTOLOGIN_LOGIN_COOKIE_NAME;
		if (!$this->_update('autologin_login_cookie_name', $value)) return 'error';
		
		$value = ($this->autologin_pass_cookie_name) ? $this->autologin_pass_cookie_name : SYSTEM_DEFAULT_AUTOLOGIN_PASS_COOKIE_NAME;
		if (!$this->_update('autologin_pass_cookie_name', $value)) return 'error';
		
		// null or zero to default value
		$value = ($this->session_gc_maxlifetime) ? $this->session_gc_maxlifetime : SYSTEM_DEFAULT_SESSION_GC_MAXLIFETIME;
		if (!$this->_update('session_gc_maxlifetime', $value)) return 'error';

		$value = ($this->login_autocomplete) ? _ON : _OFF;
		if (!$this->_update('login_autocomplete', $value)) return 'error';

		$value = ($this->use_ssl) ? _ON : _OFF;
		if (!$this->_update('use_ssl', $value)) return 'error';
		
		$value = ($this->closesite) ? _ON : _OFF;
		if (!$this->_update('closesite', $value)) return 'error';
		
		$value = ($this->closesite_text) ? $this->closesite_text : SYSTEM_DEFAULT_CLOSESITE_TEXT;
		if (!$this->_update('closesite_text', $value)) return 'error';

    	return 'success';
    }
    
    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _GENERAL_CONF_CATID);
    	return $status;
    }
}
?>
