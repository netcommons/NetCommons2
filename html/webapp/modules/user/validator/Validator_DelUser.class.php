<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 自分自身の削除-システム管理者の削除は不可
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_DelUser extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数(items,item_id=0を許すかどうか)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$usersView =& $container->getComponent("usersView");
		
    	// user_id取得
    	$user_id = $attributes;
    	
    	$login_user_id = $session->getParameter("_user_id");
    	if($user_id == $login_user_id) {
    		// 自分自身
    		return $errStr;
    	}
    	$_system_user_id = $session->getParameter("_system_user_id");
    	if($user_id == $_system_user_id) {
    		// システム管理者
    		return $errStr;
    	}
    	
    	return;
    }
}
?>
