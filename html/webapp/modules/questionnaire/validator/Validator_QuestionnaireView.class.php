<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_QuestionnaireView extends Validator
{
    /**
     * アンケート参照権限チェックバリデータ
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
		$edit = $session->getParameter("questionnaire_edit". $attributes["block_id"]);
		if ($authID < _AUTH_CHIEF &&
				$edit == _ON) {
			return $errStr;
		}

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		$questionnaireView =& $container->getComponent("questionnaireView");
		if (empty($attributes["questionnaire_id"])) {
			$questionnaire = $questionnaireView->getDefaultQuestionnaire();
		} elseif ($edit == _ON) { 
			$questionnaire = $questionnaireView->getQuestionnaire();
		} else {
			$questionnaire = $questionnaireView->getCurrentQuestionnaire();
		}

		if (empty($questionnaire)) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("questionnaire", $questionnaire);
 
        return;
    }
}
?>
