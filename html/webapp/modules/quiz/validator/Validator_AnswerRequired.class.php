<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答必須チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_AnswerRequired extends Validator
{
    /**
     * 解答必須チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
        $container =& DIContainerFactory::getContainer();
        $quizView =& $container->getComponent("quizView");

		if ($attributes["quiz"]["quiz_type"] == QUIZ_TYPE_LIST_VALUE) {
			$questions = $quizView->getQuestions();
		} else {
			$questionID = $attributes["question_id"];
			$questions[$questionID] = $quizView->getQuestion();
		}

		$request =& $container->getComponent("Request");
		$session =& $container->getComponent("Session");
		$confirmDatas = $session->getParameter('quiz_confirm' . $attributes["block_id"]);
		if (!empty($confirmDatas)
			&& $attributes["quiz"]["quiz_type"] == QUIZ_TYPE_LIST_VALUE) {
			$request->setParameter('questions', $questions);
			$request->setParameter('answer_value', $confirmDatas['answer_value']);
			$request->setParameter('answerChoiceIDs', $confirmDatas['answerChoiceIDs']);

			return;
		}

		$errors = array();
		$answerChoiceIDs = array();
		foreach (array_keys($questions) as $question_id) {
			if ($questions[$question_id]["require_flag"] == _ON &&
					empty($attributes["answer_value"][$question_id])) {
				$errors[] = sprintf($errStr, $questions[$question_id]["question_sequence"]);
				continue;
			}

			if ($questions[$question_id]["question_type"] == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
				//記述式文字数チェック
				if(strlen(bin2hex($attributes["answer_value"][$question_id])) / 2 > _VALIDATOR_TEXTAREA_LEN) {
					$filterChain =& $container->getComponent("FilterChain");
					$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
					$errStr = sprintf(_MAXLENGTH_ERROR,$smartyAssign->getLang("quiz_answer_textarea"),_VALIDATOR_TEXTAREA_LEN);
					return $errStr;
				} else {
					continue;
				}
			}

			if ($questions[$question_id]["question_type"] == QUIZ_QUESTION_TYPE_WORD_VALUE) {
				//単語文字数チェック
				if(strlen(bin2hex($attributes["answer_value"][$question_id])) / 2 > _VALIDATOR_TITLE_LEN) {
					$filterChain =& $container->getComponent("FilterChain");
					$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
					$errStr = sprintf(_MAXLENGTH_ERROR,$smartyAssign->getLang("quiz_answer_word"),_VALIDATOR_TITLE_LEN);
					return $errStr;
				} else {
					continue;
				}
			}

			$answerChecks = array();
			foreach ($attributes["choice_id"][$question_id] as $choice_id) {
				if (($questions[$question_id]["question_type"] == QUIZ_QUESTION_TYPE_RADIO_VALUE
						&& !empty($attributes["answer_value"][$question_id])
							&& $attributes["answer_value"][$question_id] == $choice_id) ||
						($questions[$question_id]["question_type"] == QUIZ_QUESTION_TYPE_CHECKBOX_VALUE &&
						!empty($attributes["answer_value"][$question_id][$choice_id]))) {
					$answerChecks[] = _ON;
					$answerChoiceIDs[] = $choice_id;
				} else {
					$answerChecks[] = _OFF;
				}
			}
			$attributes["answer_value"][$question_id] = implode("|", $answerChecks);
		}

		if (empty($errors)) {
			$request->setParameter("questions", $questions);
			$request->setParameter("answer_value", $attributes["answer_value"]);
			$request->setParameter("answerChoiceIDs", $answerChoiceIDs);

			return;
		}

		if ($attributes["quiz"]["quiz_type"] != QUIZ_TYPE_LIST_VALUE) {
			$errStr = sprintf($errStr, $session->getParameter("quiz_current_sequence". $attributes["block_id"]));

			return $errStr;
		}

		$errStr = implode("<br />", $errors);
		return $errStr;
    }
}
?>