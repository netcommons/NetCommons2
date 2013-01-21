<?php

/**
 * 回覧操作権限チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_CheckCreateAuthority extends Validator
{
	/**
	 * 回覧作成権限チェックバリデータ
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
		$circularView =& $container->getComponent("circularView");

		$config = $circularView->getConfig();
		if ($config === false) {
			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			return $smartyAssign->getLang("_invalid_input");
		}

		$session =& $container->getComponent("Session");
		if ($config["create_authority"] > $session->getParameter("_auth_id")) {
			return $errStr;
		}

		return;
	}
}
?>
