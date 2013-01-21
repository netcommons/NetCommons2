<?php

/**
 * 期限チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_Period extends Validator
{
	/**
	 * 期限チェックバリデータ
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
		$request =& $container->getComponent("Request");

		if (empty($attributes["period_checkbox"])) {
			$request->setParameter("period", "");
			return;
		}

		$period = $attributes["period"]."240000";
		$period = timezone_date($period, true);
		$gmt = timezone_date();

		if ($period < $gmt) {
			return $errStr;
		}

		$request->setParameter("period", $period);

		return;
	}
}
?>