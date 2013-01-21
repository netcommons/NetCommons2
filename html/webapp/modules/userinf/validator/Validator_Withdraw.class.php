<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 退会機能チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Validator_Withdraw extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値		user_id
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$session =& $container->getComponent("Session");
    	$configView =& $container->getComponent("configView");
    	
    	//
        // 自分(Self)
        //
    	$user_id = $session->getParameter("_user_id");
    	if($user_id == "0") return $errStr;
    	$user_auth_id = $session->getParameter("_user_auth_id");
    	
    	//
        // 編集先
        //
        $edit_user_id = $attributes;
        if($edit_user_id == "0") {
        	$edit_user_id = $session->getParameter("_user_id");
        }
        
        //
    	// 退会機能
    	//
    	$withdraw_membership_use = $configView->getConfigByConfname(_SYS_CONF_MODID, "withdraw_membership_use");
    	if($withdraw_membership_use === false) {
			return $errStr;
		}
    	if($user_id != $edit_user_id || $user_auth_id == _AUTH_ADMIN || $withdraw_membership_use['conf_value'] == _OFF) {
    		// 自分自身以外、管理者は退会できない	
    		return $errStr;
    	}
    	return;
    }
}
?>
