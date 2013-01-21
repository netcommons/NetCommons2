<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 状態チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_Status extends Validator
{
    /**
     * 状態チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if ($attributes["questionnaire"] == QUESTIONNAIRE_STATUS_END_VALUE) {
			return $errStr;
		}
		
		if ($attributes["questionnaire"] == QUESTIONNAIRE_STATUS_INACTIVE_VALUE &&
			$attributes["status"] == QUESTIONNAIRE_STATUS_END_VALUE) {
			return $errStr;
		}
		
		if ($attributes["questionnaire"] == QUESTIONNAIRE_STATUS_ACTIVE_VALUE &&
			$attributes["status"] == QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
			return $errStr;
		}
		
        return;
    }
}
?>