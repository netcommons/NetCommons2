<?php

/**
 * 権限の値チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_AuthorityValue extends Validator
{
	/**
	 * 権限の値チェックバリデータ
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr エラー文字列
	 * @param   array   $params オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		if (!is_array($attributes["create_authority"])) {
			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		$postAuthority = min(array_keys($attributes["create_authority"]));
		$request->setParameter("create_authority", $postAuthority);

		return;
	}
}
?>