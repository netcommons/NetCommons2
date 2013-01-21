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
class Circular_Validator_CheckOperationAuthority extends Validator
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
		if (!$attributes) {
			return;
		}
		$container =& DIContainerFactory::getContainer();
		$circularView =& $container->getComponent("circularView");

		$circularId = $attributes;
		if (!$circularView->hasAuthority($circularId)) {
			return $errStr;
		}

		return;
	}
}
?>
