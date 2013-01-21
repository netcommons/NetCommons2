<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員の存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_Validator_RoleAuthExistence extends Validator
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

		$role_authority_id = intval($attributes);
		if ($role_authority_id == 0) { return; }

		// 会員データ取得
		$usersView =& $container->getComponent("usersView");
		$where_params = array(
								"{users}.active_flag IN ("._USER_ACTIVE_FLAG_OFF.","._USER_ACTIVE_FLAG_ON.","._USER_ACTIVE_FLAG_PENDING.","._USER_ACTIVE_FLAG_MAILED.")" => null,
								"{users}.system_flag IN ("._ON.","._OFF.")" => null,
								"{users}.role_authority_id" => $role_authority_id
							);
		$users =& $usersView->getUsers($where_params);
		if($users === false || isset($users[0])) {
			return $errStr;
		}
		$authoritiesView =& $container->getComponent("authoritiesView");
		$auth =& $authoritiesView->getAuthorityById($role_authority_id);
		if($auth['user_authority_id'] == _AUTH_MODERATE) {
			// モデレータならば
			// pages_users_linkテーブルで
			// role_auth_idが使用されていないこともチェック
			$pagesView =& $container->getComponent("pagesView");
			$pages_users_link =& $pagesView->getPageUsersLink(array("role_authority_id" => $role_authority_id));
			if($pages_users_link === false || isset($pages_users_link[0])) {
				return $errStr;
			}
		}

    	return;
    }
}
?>
