<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限の削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_Action_Admin_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $role_authority_id = null;

	// 使用コンポーネントを受け取るため
	var $authoritiesAction = null;
	var $authoritiesView = null;
	var $configView = null;
	var $configAction = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->role_authority_id = intval($this->role_authority_id);
		$where_params = array(
			"role_authority_id" => $this->role_authority_id
		);
		$result = $this->authoritiesAction->delAuthorityModuleLink($where_params);
		if ($result === false) {
			return 'error';
		}
		$result = $this->authoritiesAction->delAuthorityById($this->role_authority_id);
		if ($result === false) {
			return 'error';
		}
		//
		// autoregist_authorが削除された権限になった場合、autoregist_author更新
		//
		$autoregist_author = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "autoregist_author");
    	if($autoregist_author === false) {
			return 'error';
		}
		if($this->role_authority_id == $autoregist_author['conf_value']) {
			$where_params = array(
	        	"user_authority_id" => _AUTH_GENERAL,
	        	"system_flag" => _ON
	        );
	        //1件取得
			$authorities = $this->authoritiesView->getAuthorities($where_params, null, 1);
			if ($authorities === false) {
	        	return 'error';
	        }
	        if(isset($authorities[0])) {
				$this->configAction->updConfigValue(_SYS_CONF_MODID, "autoregist_author", $authorities[0]['role_authority_id'], _ENTER_EXIT_CONF_CATID);
	        }
		}
		
		return 'success';
	}
}
?>
