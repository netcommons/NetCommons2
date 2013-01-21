<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 質問番号チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_QuestionSequence extends Validator
{
    /**
     * 質問番号チェックバリデータ
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
        $sequences = $questionnaireView->getQuestionSequence();
		if (!$sequences) {
			return $errStr;	
		}
		
		$dragQuestionID = $attributes["drag_question_id"];
		$dropQuestionID = $attributes["drop_question_id"];

		if ($attributes["position"] == "top") {
			$sequences[$dropQuestionID]--;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("drag_sequence", $sequences[$dragQuestionID]);
		$request->setParameter("drop_sequence", $sequences[$dropQuestionID]);
		
        return;
    }
}
?>