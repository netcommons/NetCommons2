<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限管理-詳細設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_View_Admin_Detail extends Action
{
	//リクエストパラメータ
	var $role_authority_id = null;
	var $role_authority_name = null;
	var $user_authority_id = null;
	var $level_flag = null;
	
	var $hierarchy = null;
	
	var $enroll_modules = null;
	var $not_enroll_modules = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $authorityCompmain = null;
	var $authoritiesView = null;
	var $modulesView = null;
	var $request = null;
		
	// バリデートによりセット
	var $authority = null;

	// 値をセットするため
	var $config = array();
	var $sys_modules = null;
	var $site_modules = null;
	
	var $usermodule_auth = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$myroom_use_flag = $this->session->getParameter(array("authority", $this->role_authority_id, "detail", "myroom_use_flag"));
		
		// ベース権限の変更があれば、バリデートによりセットしたテーブルの値を初期化
		$format_flag = false;
		if(isset($this->authority['user_authority_id']) && $this->user_authority_id != null && $this->user_authority_id != $this->authority['user_authority_id'] && !isset($myroom_use_flag)) {
			$this->authorityCompmain->formatAuth($this->authority, $this->user_authority_id);
			$format_flag = true;
		}
		
		// 一般設定をセッションに保存
		if($this->role_authority_name != null) {
			$this->session->setParameter(array("authority", $this->role_authority_id, "general", "role_authority_name"), $this->role_authority_name);
			if($this->user_authority_id == null) {
				$this->user_authority_id = $this->authority['user_authority_id'];
				// レベル設定に値を渡すため
				$this->request->setParameter('user_authority_id', $this->user_authority_id);
			}
			$old_user_authority_id = $this->session->getParameter(array("authority", $this->role_authority_id, "general", "user_authority_id"));
			if(isset($old_user_authority_id) && $old_user_authority_id !=$this->user_authority_id) {
				// detailセッションデータクリア
				$this->session->removeParameter(array("authority", $this->role_authority_id, "detail"));
				
				$this->session->setParameter(array("authority", $this->role_authority_id, "detail", "myroom_use_flag"), $myroom_use_flag);
		
			}
			$this->session->setParameter(array("authority", $this->role_authority_id, "general", "user_authority_id"), $this->user_authority_id);
		}
		if($this->level_flag == _ON || ($this->level_flag === null && $this->hierarchy == null && $this->user_authority_id == _AUTH_MODERATE)) {
			// レベル設定
			return 'level_success';
		} else if($this->hierarchy != null) {
			// $hierarchyをセッションにセット
			$this->session->setParameter(array("authority", $this->role_authority_id, "level", "hierarchy"), $this->hierarchy);
		}
		if($this->enroll_modules != null || $this->not_enroll_modules != null) {
			if($this->enroll_modules == null) $this->enroll_modules = array();
			// $enroll_modulesをセッションにセット
			$this->session->setParameter(array("authority", $this->role_authority_id, "enroll_modules"), $this->enroll_modules);
		}
		
		//
		// config.iniの値を取得
		//
		$first_flag = false;
		if($this->authority != null && !isset($myroom_use_flag)) {
			$first_flag = true;
			$this->authorityCompmain->setSessionDetail($this->authority, false);
		}
		
		// 会員管理の権限を取得
		if($this->role_authority_name != null && $format_flag == false) {
			$module = $this->modulesView->getModuleByDirname("user");
			if($module === false) {
				return 'error';	
			}
			
			$auth_module_link = $this->authoritiesView->getAuthoritiesModulesLink(array("role_authority_id" => $this->role_authority_id,"module_id" => $module['module_id']));
			if($auth_module_link === false) {
				return 'error';	
			}
			if(isset($auth_module_link[0])) {
				$this->usermodule_auth = $auth_module_link[0]['authority_id'];
			}
		} else {
			$this->usermodule_auth = $this->user_authority_id;
		}
		/*
		$first_flag = false;
		if($this->authority != null && !isset($myroom_use_flag)) {
			// DBから初期値セット
			$this->authority['usermodule_auth'] = $this->user_authority_id;
			
			// 会員管理の権限を取得
			$module = $this->modulesView->getModuleByDirname("user");
			if($module === false) {
				return 'error';	
			}
			
			$auth_module_link = $this->authoritiesView->getAuthoritiesModulesLink(array("role_authority_id" => $this->role_authority_id,"module_id" => $module['module_id']));
			if($auth_module_link === false) {
				return 'error';	
			}
			if(isset($auth_module_link[0])) {
				$this->authority['usermodule_auth'] = $auth_module_link[0]['authority_id'];
			}
			$first_flag = true;
			
			$this->authorityCompmain->setSessionDetail($this->authority, false);
		}
		*/
		$this->config = $this->authorityCompmain->getConfig($this->role_authority_id, $this->user_authority_id);
		
		//
		// 管理系モジュール取得
		//
		$func = array($this->authorityCompmain, "setModules");
		$result = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($this->role_authority_id, array("system_flag"=>_ON), null, $func, array($this->config['sys_modules'], $first_flag, $format_flag));
		if($result === false) {
			return 'error';	
		}
		list($this->sys_modules, $this->site_modules) = $result;
		
		return 'success';
	}
}
?>
