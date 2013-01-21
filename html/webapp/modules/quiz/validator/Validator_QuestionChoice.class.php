<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 選択肢の存在・文字数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_QuestionChoice extends Validator
{
	/**
	 * 選択肢の存在・文字数チェックバリデータ
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr エラー文字列
	 * @param array $params オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */
	function validate($attributes, $errStr, $params)
	{
		if ($attributes['question_type'] == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
			return;
		}

		$quiz = $attributes['quiz'];
		if ($quiz['status'] != QUIZ_STATUS_INACTIVE_VALUE) {
			return;
		}

		if ($attributes['question_type'] == QUIZ_QUESTION_TYPE_WORD_VALUE && $attributes['choice_word_id'] == null ||
			$attributes['question_type'] != QUIZ_QUESTION_TYPE_WORD_VALUE && $attributes['choice_id'] == null) {

			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent('FilterChain');
		$smartyAssign =& $filterChain->getFilterByName('SmartyAssign');

		if ($attributes['question_type'] == QUIZ_QUESTION_TYPE_WORD_VALUE) {
			foreach($attributes['choice_word_value'] as $value) {
				if ($value == null) {
					$errStr = sprintf($smartyAssign->getLang('_required'),$smartyAssign->getLang('quiz_choice_word_value'));
					return $errStr;
				}
				if (strlen(bin2hex($value)) / 2 > _VALIDATOR_TITLE_LEN) {
					$errStr = sprintf(_MAXLENGTH_ERROR, $smartyAssign->getLang('quiz_choice_word_value'), _VALIDATOR_TITLE_LEN);
					return $errStr;
				}
			}
			$request =& $container->getComponent('Request');
			$request->setParameter("choice_id", $attributes['choice_word_id']);
			$request->setParameter("choice_value", $attributes['choice_word_value']);
			$request->setParameter("choice_word_id", null);
			$request->setParameter("choice_word_value", null);
		} else {
			foreach($attributes['choice_value'] as $value) {
				if ($value == null) {
					$errStr = sprintf($smartyAssign->getLang('_required'),$smartyAssign->getLang('quiz_choice_value'));
					return $errStr;
				}
				if (strlen(bin2hex($value)) / 2 > _VALIDATOR_TEXTAREA_LEN) {
					$errStr = sprintf(_MAXLENGTH_ERROR, $smartyAssign->getLang('quiz_choice_value'), _VALIDATOR_TEXTAREA_LEN);
					return $errStr;
				}
			}
		}


		return;
	}
}
?>