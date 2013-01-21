<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索条件数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_NumberOfSearchConditions extends Validator
{
	/**
	 * 検索条件数チェック
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr エラー文字列
	 * @param array $params オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */
	function validate($attributes, $errStr, $params)
	{
		if (empty($attributes['items'])) {
			return;
		}
		
		$conditions = array_filter($attributes['items']);
		$number = 0;
		foreach ($conditions as $condition) {
			if (is_array($condition)) {
				$conditionSet = array_filter($condition);
				$number += count($conditionSet);
			} else {
				$number++;
			}
		}
		if ($number * 3 > USER_LIMIT_NUMBER_OF_ITEMS) {
			return $errStr;
		}
	}
}
?>