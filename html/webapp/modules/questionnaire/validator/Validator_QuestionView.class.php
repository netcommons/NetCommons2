<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 質問参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_QuestionView extends Validator
{
    /**
     * 質問参照権限チェックバリデータ
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
        $questionnaireView =& $container->getComponent("questionnaireView");

		if (empty($attributes["question_id"])) {
			$question = $questionnaireView->getDefaultQuestion();
		} else {
			$question = $questionnaireView->getQuestion();
		}
		if (empty($question)) {
        	return $errStr;
        }
		
		if (!empty($attributes["question_id"])
        		&& $question["questionnaire_id"] != $attributes["questionnaire_id"]) {
        	return $errStr;
        }
        		        
		$request =& $container->getComponent("Request");
    	$request->setParameter("question", $question);

        return;
    }
}
?>