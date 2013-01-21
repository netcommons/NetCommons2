<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 確認画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_View_Admin_Confirm extends Action
{
	
	//リクエストパラメータ
	var $role_authority_id = null;
	var $role_authority_name = null;
	var $user_authority_id = null;
	
	var $enroll_modules = null;
	var $not_enroll_modules = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $authorityCompmain = null;
	var $authoritiesView = null;
		
	// バリデートによりセット
	var $authority = null;
	
	// 値をセットするため
	var $config = null;
	var $sys_modules = null;
	var $site_modules = null;
	var $modules_obj = null;
	
    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if($this->enroll_modules != null || $this->not_enroll_modules != null) {
			if($this->enroll_modules == null) $this->enroll_modules = array();
			// $enroll_modulesをセッションにセット
			$this->session->setParameter(array("authority", $this->role_authority_id, "enroll_modules"), $this->enroll_modules);
		}
		
    	//
		// config.iniの値を取得
		//
		$this->config = $this->authorityCompmain->getConfig($this->role_authority_id, $this->user_authority_id);
		
		//
		// 管理系モジュール取得
		//
		$func = array($this->authorityCompmain, "setModules");
		$result = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($this->role_authority_id, array("system_flag"=>_ON), null, $func, array($this->config['sys_modules']));
		if($result === false) {
			return 'error';	
		}
		
		list($this->sys_modules, $this->site_modules) = $result;
		$func = array($this->authorityCompmain,"setAuthoritiesModules");
		$this->modules_obj = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId(intval($this->role_authority_id), array("system_flag"=>_OFF), null, $func, array($this->role_authority_id));
			
		
        return 'success';
    }
}
?>
