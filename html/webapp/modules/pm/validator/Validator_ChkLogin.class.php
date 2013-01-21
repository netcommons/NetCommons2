<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ログイン/権限チェックバリデータクラス
 *
 * @package	 NetCommons.validator
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Pm_Validator_ChkLogin extends Validator
{
	/**
	 * ログイン/権限チェックバリデータ
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr	 エラー文字列
	 * @param   array   $params	 オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent('Session');
		$user_auth_id = $session->getParameter('_user_auth_id');

		$authoritiesView =& $container->getComponent('authoritiesView');
		$where_params = array(
			'{modules}.module_id' => $attributes['module_id']
		);
		$authorities = $authoritiesView->getAuthoritiesModulesLinkByAuthorityId($session->getParameter('_role_auth_id'), $where_params);
		if($user_auth_id > _AUTH_GUEST && isset($authorities[0]['authority_id'])) {
			return;
		}

		if ($user_auth_id > _AUTH_OTHER) {
			$errStr = _INVALID_AUTH;
		}

		$request =& $container->getComponent('Request');
		$active_center = $request->getParameter('active_center');
		if(isset($active_center)) {
			$parameters =& $request->getParameters();
			$redirect_url = "?_sub_action=" . DEFAULT_ACTION;
			foreach($parameters as $key => $parameter) {
				if($key != "page_id" && $key != "block_id" && $key != "room_id" && $key != "action" && !is_array($parameter) && !preg_match("/^_/", $key)) {
					$redirect_url .= "@".$key."=". urlencode($parameter);
				}
			}
			$url = BASE_URL.INDEX_FILE_NAME."?action=login_view_main_init&error_mes="._ON."&_redirect_url=".$redirect_url;
			$commonMain =& $container->getComponent('commonMain');
			$commonMain->redirectHeader($url, 2, $errStr);
			exit;
		} else {
			return $errStr;
		}
	}
}
?>