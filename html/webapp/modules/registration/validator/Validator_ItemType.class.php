<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目タイプの値チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_ItemType extends Validator
{
    /**
     * 項目タイプの値チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if ($attributes["item_type"] != REGISTRATION_TYPE_TEXT
				&& $attributes["item_type"] != REGISTRATION_TYPE_CHECKBOX
				&& $attributes["item_type"] != REGISTRATION_TYPE_RADIO
				&& $attributes["item_type"] != REGISTRATION_TYPE_SELECT
				&& $attributes["item_type"] != REGISTRATION_TYPE_TEXTAREA
				&& $attributes["item_type"] != REGISTRATION_TYPE_EMAIL
				&& $attributes["item_type"] != REGISTRATION_TYPE_FILE) {
			return $errStr;
		}

        return;
    }
}
?>
