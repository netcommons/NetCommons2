<?php

/**
 * 回答チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_Reply extends Validator
{
	/**
	 * 回答チェックバリデータ
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
		$circularView =& $container->getComponent('circularView');

		$circular = $circularView->getCircularInfo();
		if (empty($circular['periodClassName'])) {
			return;
		}
		if ($circular['periodClassName'] == CIRCULAR_PERIOD_OVER) {
			return $errStr;
		}

		return;
	}
}
?>