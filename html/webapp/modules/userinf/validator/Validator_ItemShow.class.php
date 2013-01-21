<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 参加ルーム or アクセス状況 or レポート　表示チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Validator_ItemShow extends Validator
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
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$usersView =& $container->getComponent("usersView");
		$login_user_id = $session->getParameter("_user_id");
		$login_user_auth_id = $session->getParameter("_user_auth_id");
		
		// $params[0] = item_name (def_USER_ITEM_ENTRY_ROOM or def_USER_ITEM_MONTHLY_NUM or def_USER_ITEM_MODULES_INFO)
		if(!isset($params[0])) return $errStr;
		$item_name = preg_replace("/^def_/", "", $params[0]);
		
		
		$where_params = array(
    						"user_authority_id" => $login_user_auth_id,
    						"item_name" => $item_name,
    						"type" => USER_TYPE_SYSTEM
    					);
    	$items =& $usersView->getItems($where_params);
    	if($items === false || !isset($items[0])) return $errStr;
    	
    	$user_id = $attributes;
		if($user_id == "0")  return $errStr;
    	$user =& $usersView->getUserById($user_id);
    	if($user === false || !isset($user['user_id'])) return $errStr;	
    	
    	if($login_user_id == $user_id) {
    		// Self	
    		if($items[0]['self_public_flag'] != USER_PUBLIC)  return $errStr;
    	} else if($user['user_authority_id'] >= $login_user_auth_id) {
    		// Over
    		if($items[0]['over_public_flag'] != USER_PUBLIC)  return $errStr;	
    	} else {
    		// Under	
    		if($items[0]['under_public_flag'] != USER_PUBLIC)  return $errStr;
    	}
    	
    	return;
    }
}
?>
