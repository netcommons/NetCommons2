<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 選択肢の文字数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_QuestionChoiceLength extends Validator
{
    /**
     * 選択肢の文字数チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {

		foreach($attributes as $value) {
			if (strlen(bin2hex($value)) / 2 > _VALIDATOR_TEXTAREA_LEN) {
				return $errStr;
			}
		}


        return;
    }
}
?>
