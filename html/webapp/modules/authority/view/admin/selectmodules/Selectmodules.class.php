<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限管理(配置可能なモジュール)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_View_Admin_Selectmodules extends Action
{
	//リクエストパラメータ
	var $role_authority_id = null;
	var $role_authority_name = null;
	var $user_authority_id = null;
	
	//var $not_enroll_modules_id = null;
	//var $not_enroll_modules_name = null;
	//var $enroll_modules_id = null;
	//var $enroll_modules_name = null;
	
	var $detail = null;		// 詳細情報配列

	// 使用コンポーネントを受け取るため
	var $authoritiesView = null;
	var $authorityCompmain = null;

	// 値をセットするため
	var $modules_obj = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	//
		// Detailの値があればSessionへセット
		//
		$this->authorityCompmain->setSessionDetail($this->detail);
		
		if(isset($this->detail['myroom_use_flag']) && $this->detail['myroom_use_flag'] == _OFF) {
			return 'success_confirm';
		}
    	//if (!is_array($this->not_enroll_modules_id) && !is_array($this->enroll_modules_id)) {
			$func = array($this->authorityCompmain,"setAuthoritiesModules");
			$this->modules_obj = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId(intval($this->role_authority_id), array("system_flag"=>_OFF), null, $func, array($this->role_authority_id));
		//} else {
		//	$this->modules_obj["not_enroll_id"] = $this->not_enroll_modules_id;
		//	$this->modules_obj["not_enroll_name"] = $this->not_enroll_modules_name;
		//	$this->modules_obj["enroll_id"] = $this->enroll_modules_id;
		//	$this->modules_obj["enroll_name"] = $this->enroll_modules_name;
    	//}
        return 'success';
    }

}
?>
