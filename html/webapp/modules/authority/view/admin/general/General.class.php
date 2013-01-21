<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限管理-権限追加(編集)(一般設定)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_View_Admin_General extends Action
{
	//リクエストパラメータ
	var $role_authority_id = null;
	//var $role_authority_name = null;
	//var $user_authority_id = null;
	
	var $detail = null;		// 詳細情報配列
	
	var $hierarchy = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $authorityCompmain = null;
	
	
	// バリデートによりセット
	var $authority = null;

	// 値をセットするため
	

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if($this->role_authority_id == 0) {
			// 新規追加-初期値セット
			$this->authority['user_authority_id'] = _AUTH_GENERAL;
		}
		//
		// Sessionの値があればセット
		//
		$user_authority_id = $this->session->getParameter(array("authority", $this->role_authority_id, "general", "user_authority_id"));
		$role_authority_name = $this->session->getParameter(array("authority", $this->role_authority_id, "general", "role_authority_name"));
		if(isset($role_authority_name)) {
			$this->authority['role_authority_name'] = $role_authority_name;
		}
		if(isset($user_authority_id)) {
			$this->authority['user_authority_id'] = $user_authority_id;
		}
		
		//
		// Detailの値があればSessionへセット
		// 
		$this->authorityCompmain->setSessionDetail($this->detail);
		
		if($this->hierarchy != null) {
			// $hierarchyをセッションにセット
			$this->session->setParameter(array("authority", $this->role_authority_id, "level", "hierarchy"), $this->hierarchy);
		}
		return 'success';
	}
}
?>
