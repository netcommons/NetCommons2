<?php

/**
 * 閲覧権限チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_UserAuthCheck extends Validator
{
	/**
	 * validate実行
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

		if (!$session->getParameter('_user_id')) {
			return $errStr;
		}

		$actionChain =& $container->getComponent('ActionChain');
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "circular_view_admin_search") {
			return;
		}

		if ($session->getParameter('_auth_id') < _AUTH_GENERAL) {
			$filterChain =& $container->getComponent('FilterChain');
			$smartyAssign =& $filterChain->getFilterByName('SmartyAssign');
			return $smartyAssign->getLang('circular_display_auth_invalid');
		}

		return;
	}
}
?>