<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限設定
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Module_Validator_Selectauth extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	//$actionChain =& $container->getComponent("ActionChain");
    	$session =& $container->getComponent("Session");
    	$authoritiesView =& $container->getComponent("authoritiesView");
    	
		//$action =& $actionChain->getCurAction();
		
		//
		// インストールされているかどうか
		//
        $modulesView =& $container->getComponent("modulesView");
        $module_obj =& $modulesView->getModulesById(intval($attributes[0]));
        if(!isset($module_obj) || count($module_obj) == 0) {
        	return $errStr;	
        }
        
        // 管理系は、権限設定において権限毎に使えるかどうかを細かく設定しているため、ここでは変更を許さない
        if($module_obj['system_flag'] == _ON) {
        	return $errStr;	
        }
        
        //
        // 権限管理が使用できるかどうかのチェック
        //
        $role_authority_id =$session->getParameter("_role_auth_id");
        $authorities_module_link =& $authoritiesView->getAuthoritiesModulesLinkByAuthorityId($role_authority_id, null, null, array($this, "_fetchcallbackAuthorityModuleLink"));
        //if(!isset($authorities_module_link['authority_id']) || $authorities_module_link['authority_id'] < _AUTH_CHIEF) {
        if(!isset($authorities_module_link['authority_id'])) {
        	return $errStr;		
        }
	}

	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @return array configs
	 * @access	private
	 */
	function _fetchcallbackAuthorityModuleLink($result) 
	{
		$authorities_module_link = array();
		while ($row = $result->fetchRow()) {
			$pathList = explode("_", $row["action_name"]);
			if($row["authority_id"] != null && $pathList[0] == "authority") {
				$authorities_module_link = $row;
			}
		}
		return $authorities_module_link;
	}
}
?>
