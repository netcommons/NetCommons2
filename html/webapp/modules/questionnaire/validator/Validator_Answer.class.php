<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 回答チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_Answer extends Validator
{
    /**
     * 回答チェックバリデータ
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

        if ($attributes["questionnaire"]["repeat_flag"] == _OFF &&
        		$questionnaireView->isAnswered()) {
        	return $errStr;
    	}

        return;
    }
}
?>