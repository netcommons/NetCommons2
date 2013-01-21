<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_QuizExists extends Validator
{
    /**
     * 小テスト存在チェックバリデータ
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
		
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();		
		if (empty($attributes["quiz_id"]) &&
				($actionName == "quiz_view_edit_quiz_entry" ||
					$actionName == "quiz_action_edit_quiz_entry")) {
			return;
		}

        $quizView =& $container->getComponent("quizView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["quiz_id"])) {
			$session =& $container->getComponent("Session");
			$session->removeParameter("quiz_edit". $attributes["block_id"]);

        	$attributes["quiz_id"] = $quizView->getCurrentQuizID();
        	$request->setParameter("quiz_id", $attributes["quiz_id"]);
		}

		if (empty($attributes["quiz_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $quizView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$quizView->quizExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>