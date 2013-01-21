<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 正解チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_Correct extends Validator
{
    /**
     * 正解チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
        if ($attributes["quiz"]["status"] != QUIZ_STATUS_INACTIVE_VALUE) {
        	return;
        }

        if ($attributes["question_type"] == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
        	return;
        }

		ksort($attributes["choice_id"]);
		$correctChecks = array();
		foreach (array_keys($attributes["choice_id"]) as $choiceIteration) {
			if (($attributes["question_type"] == QUIZ_QUESTION_TYPE_RADIO_VALUE &&
				$attributes["correct_radio"] == $choiceIteration) ||
				($attributes["question_type"] == QUIZ_QUESTION_TYPE_CHECKBOX_VALUE &&
				!empty($attributes["correct"][$choiceIteration])) ||
				$attributes["question_type"] == QUIZ_QUESTION_TYPE_WORD_VALUE) {
				$correctChecks[] = _ON;
	        } else {
				$correctChecks[] = _OFF;
			}
		}

		if ($attributes["require_flag"] && !in_array(_ON, $correctChecks)) {
			return $errStr;
		}
		$correct = implode("|", $correctChecks);

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$request->setParameter("correct", $correct);

        return;
    }
}
?>