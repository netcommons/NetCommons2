<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 採点チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_Mark extends Validator
{
    /**
     * 採点チェックバリデータ
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
        $request =& $container->getComponent("Request");
        $filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

        $allotments = $quizView->getAllotment();
		$question = $quizView->getQuestion($allotments[0]["question_id"]);
    	$request->setParameter("question", $question);
    	$answers = $quizView->getQuestionaryAnswer();

		$scores = array();
		$answerFlags = array();
		$summaryIDs = array();
		$users = array();

		foreach ($answers as $answer) {
			$users[$answer["answer_id"]] = array("insert_user_name" => $answer["insert_user_name"],
											"answer_number" => $answer["answer_number"]);
		}

		foreach (array_keys($allotments) as $index) {
			$answerID = $allotments[$index]["answer_id"];
			if (!empty($attributes["score"]) && array_key_exists($answerID,$attributes["score"])) {
				$scores[$answerID] = intval($attributes["score"][$answerID]);
				if ($scores[$answerID] <= 0) {
					$errors[] = sprintf($smartyAssign->getLang("quiz_score_minus"), $allotments[$index]["question_sequence"],
											$users[$answerID]["insert_user_name"],$users[$answerID]["answer_number"]);
					continue;
				}
				if ($scores[$answerID] > $allotments[$index]["allotment"]) {
					$errors[] = sprintf($errStr, $allotments[$index]["question_sequence"],
											$users[$answerID]["insert_user_name"],$users[$answerID]["answer_number"]);
					continue;
				}
			}else{
				$scores[$answerID] = 0;
			}

			$answerFlags[$answerID] = $attributes["answer_flag"][$answerID];

			if (!in_array($allotments[$index]["summary_id"], $summaryIDs)) {
				$summaryIDs[] = $allotments[$index]["summary_id"];
			}
		}

		if (empty($errors)) {
			$request =& $container->getComponent("Request");
			$request->setParameter("scores", $scores);
			$request->setParameter("answerFlags", $answerFlags);
			$request->setParameter("summaryIDs", $summaryIDs);

			return;
		}

		$errStr = implode("<br />", $errors);

		return $errStr;
    }
}
?>