<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限名称重複チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_Validator_Duplication extends Validator
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
    	$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();

        $authoritiesView =& $container->getComponent("authoritiesView");
        //定義名への変換
        $role_authority_name = $attributes["role_authority_name"];
        
        if($attributes["role_authority_name"] == _AUTH_SYSADMIN_NAME) {
        	$role_authority_name = "_AUTH_SYSADMIN_NAME";
        } else if($attributes["role_authority_name"] == _AUTH_ADMIN_NAME) {
        	$role_authority_name = "_AUTH_ADMIN_NAME";
        } else if ($attributes["role_authority_name"] == _AUTH_CHIEF_NAME) {
        	$role_authority_name = "_AUTH_CHIEF_NAME";
        } else if ($attributes["role_authority_name"] == _AUTH_MODERATE_NAME) {
        	$role_authority_name = "_AUTH_MODERATE_NAME";
        } else if ($attributes["role_authority_name"] == _AUTH_GENERAL_NAME) {
        	$role_authority_name = "_AUTH_GENERAL_NAME";
        } else if ($attributes["role_authority_name"] == _AUTH_GUEST_NAME) {
        	$role_authority_name = "_AUTH_GUEST_NAME";
        }

		$count = $authoritiesView->getCountAuthorityByName($attributes["role_authority_id"], $role_authority_name);
		if ($count > 0) {
			return $errStr;	
		}
	}
}
?>
