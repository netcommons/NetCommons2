<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート期限切れチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_PeriodOver extends Validator
{
    /**
     * アンケート期限切れチェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["questionnaire"]["period"])) {
			return;
		}
		
		$gmt = timezone_date();
		
		if ($gmt < $attributes["questionnaire"]["period"]) {
			return;
		}

		$container =& DIContainerFactory::getContainer();
		$questionnaireAction =& $container->getComponent("questionnaireAction");
		
		$params = array(
			"questionnaire_id" => $attributes["questionnaire"]["questionnaire_id"],
			"status" => QUESTIONNAIRE_STATUS_END_VALUE
		);
		if (!$questionnaireAction->updateQuestionnaire($params)) {
			return $errStr;
		}
		$attributes["questionnaire"]["status"] = QUESTIONNAIRE_STATUS_END_VALUE;

		$request =& $container->getComponent("Request");
		$request->setParameter("questionnaire", $attributes["questionnaire"]);
		
        return;
    }
}
?>