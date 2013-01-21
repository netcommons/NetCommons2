<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ログイン会員よりベース権限が管理者かそれ未満のものしか編集できないようにする
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_AuthUser extends Validator
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
	
    	// user_id取得
    	$user_id = $attributes;
    	
    	if($user_id == null || $user_id == "0") {
    		return;
    	}
    	$user = $usersView->getUserById($user_id);
    	if($user === false) {
    		return $errStr;
    	}
    	$_user_auth_id = $session->getParameter("_user_auth_id");
    	if($_user_auth_id == _AUTH_ADMIN) {
    		// 管理者
    		//
    		// 管理者ならば、システムコントロールモジュール、サイト運営モジュールの選択の有無で上下を判断
    		//
    		$authoritiesView =& $container->getComponent("authoritiesView");
    		$_role_auth_id = $session->getParameter("_role_auth_id");
    		$func = array($this, "_getSysModulesFetchcallback");
    		$system_user_flag = $authoritiesView->getAuthoritiesModulesLinkByAuthorityId($_role_auth_id, array("system_flag"=>_ON), null, $func);
			if($system_user_flag === null) {
				$buf_system_user_flag = $authoritiesView->getAuthoritiesModulesLinkByAuthorityId($user['role_authority_id'], array("system_flag"=>_ON), null, $func);
				if($buf_system_user_flag === true) {
					return $errStr;
				}
			}
    		return;	
    	}
    	if($user['user_authority_id'] >= $_user_auth_id) {
    		return $errStr;
    	}
    	return;
    }
    
    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return true or null
	 * @access	private
	 */
	function _getSysModulesFetchcallback($result) {
		$site_modules_dir_arr = explode("|", AUTHORITY_SYS_DEFAULT_MODULES_ADMIN);	
		while ($obj = $result->fetchRow()) {
			if($obj["authority_id"] === null) continue;
			$module_id = $obj["module_id"];
			
			$pathList = explode("_", $obj["action_name"]);
			if(!in_array($pathList[0], $site_modules_dir_arr)) {
				return true;	
			}
		}
		return null;
	}
}
?>
