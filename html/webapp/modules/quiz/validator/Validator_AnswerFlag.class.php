<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答フラグチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_AnswerFlag extends Validator
{
    /**
     * 解答フラグチェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$answerFlagArr = array();
		foreach ($attributes["answer_flag"] as $answerId => $answerValue) {
			if (!is_numeric($answerId) ||
				($answerValue !== QUIZ_ANSWER_NOT_MARK_VALUE &&
				$answerValue !== QUIZ_ANSWER_CORRECT_VALUE &&
				$answerValue !== QUIZ_ANSWER_WRONG_VALUE)
			) {
				return $errStr;
			}
			$answerFlagArr[(int)$answerId] = $answerValue;
		}

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent('Request');
		$request->setParameter('answer_flag', $answerFlagArr);

		return;
    }
}
?>