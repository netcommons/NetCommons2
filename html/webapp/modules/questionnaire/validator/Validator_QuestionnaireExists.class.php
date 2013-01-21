<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_QuestionnaireExists extends Validator
{
    /**
     * アンケート存在チェックバリデータ
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
		if (empty($attributes["questionnaire_id"]) &&
				($actionName == "questionnaire_view_edit_questionnaire_entry" ||
					$actionName == "questionnaire_action_edit_questionnaire_entry")) {
			return;
		}

        $questionnaireView =& $container->getComponent("questionnaireView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["questionnaire_id"])) {
			$session =& $container->getComponent("Session");
			$session->removeParameter("questionnaire_edit". $attributes["block_id"]);

        	$attributes["questionnaire_id"] = $questionnaireView->getCurrentQuestionnaireID();
        	$request->setParameter("questionnaire_id", $attributes["questionnaire_id"]);
		}

		if (empty($attributes["questionnaire_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $questionnaireView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$questionnaireView->questionnaireExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>