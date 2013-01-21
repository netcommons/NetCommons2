<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_QuizView extends Validator
{
    /**
     * 小テスト参照権限チェックバリデータ
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

		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		$edit = $session->getParameter("quiz_edit". $attributes["block_id"]);
		if ($authID < _AUTH_CHIEF &&
				$edit == _ON) {
			return $errStr;
		}

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		$quizView =& $container->getComponent("quizView");
		if (empty($attributes["quiz_id"])) {
			$quiz = $quizView->getDefaultQuiz();
		} elseif ($edit == _ON) { 
			$quiz = $quizView->getQuiz();
		} else {
			$quiz = $quizView->getCurrentQuiz();
		}

		if (empty($quiz)) {
        	return $errStr;
        }
	
		$request =& $container->getComponent("Request");
		$request->setParameter("quiz", $quiz);
 
        return;
    }
}
?>
