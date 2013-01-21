<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_Whatsnew extends Validator
{
    /**
     * 新着チェックバリデータ
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

    	$questionnaireID = $questionnaireView->getCurrentQuestionnaireID();
        if ($attributes["questionnaire_id"] != $questionnaireID) {
			return $errStr;
		}
		
        return;
    }
}
?>