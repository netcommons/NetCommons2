<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員項目数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_NumberOfItems extends Validator
{
	/**
	 * 会員項目数チェック
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr エラー文字列
	 * @param array $params オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */
	function validate($attributes, $errStr, $params)
	{
		if (!empty($attributes['item_id'])) {
			return;
		}

		$container =& DIContainerFactory::getContainer();
		$db =& $container->getComponent('DbObject');
		$count = $db->countExecute('items');
		if($count > USER_LIMIT_NUMBER_OF_ITEMS) {
			return sprintf($errStr, USER_LIMIT_NUMBER_OF_ITEMS);
		}

		return;
	}
}
?>