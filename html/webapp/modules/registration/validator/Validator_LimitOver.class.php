<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録フォーム制限数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_LimitOver extends Validator
{
	/**
	 * 登録フォーム制限数チェックバリデータ
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr	 エラー文字列
	 * @param array $params	 オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */
	function validate($attributes, $errStr, $params)
	{
		if (empty($attributes['registration']['limit_number'])) {
			return;
		}

		if ($attributes['registration']['limit_number'] <= $attributes['registration']['data_count']) {
			return $errStr;
		}

		return;
	}
}
?>