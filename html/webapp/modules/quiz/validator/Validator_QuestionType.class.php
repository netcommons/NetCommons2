<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 問題タイプの値チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_QuestionType extends Validator
{
    /**
     * 問題タイプの値チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if ($attributes["question_type"] != QUIZ_QUESTION_TYPE_RADIO_VALUE &&
				$attributes["question_type"] != QUIZ_QUESTION_TYPE_CHECKBOX_VALUE &&
				$attributes["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE &&
				$attributes["question_type"] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
			return $errStr;
		}

        return;
    }
}
?>
