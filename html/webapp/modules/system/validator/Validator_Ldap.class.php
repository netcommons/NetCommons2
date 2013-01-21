<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * サーバLDAPチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_Validator_Ldap extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値(emai, code_date)
     *
     * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	if(isset($attributes['ldap_uses']) && intval($attributes['ldap_uses']) == _ON) {

    		$container =& DIContainerFactory::getContainer();
        	$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

    		// ONならば必須チェック
    	    if (strlen($attributes['ldap_server']) == 0) {
    	    	return sprintf(_REQUIRED, $smartyAssign->getLang('system_ldap_server_name'));
            }
            if (strlen($attributes['ldap_domain']) == 0) {
            	return sprintf(_REQUIRED, $smartyAssign->getLang('system_ldap_domain_name'));
            }
    	}
    	return;
    }
}
?>
