<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 選択肢の存在・文字数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_QuestionChoice extends Validator
{
    /**
     * 選択肢の存在・文字数チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	if ($attributes["questionnaire"]["status"] != QUESTIONNAIRE_STATUS_INACTIVE_VALUE) {
    		return;
    	}

    	if ($attributes["question_type"] == null) {
    		return;
    	}

		if ($attributes["question_type"] == QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
			return;
		}

		if ($attributes["choice_id"] == null) {
			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

    	foreach($attributes["choice_value"] as $value) {
    	    if ($value == null) {
        		$errStr = sprintf($smartyAssign->getLang("_required"),$smartyAssign->getLang("questionnaire_choice_value"));
        		return $errStr;
            }
			if (strlen(bin2hex($value)) / 2 > _VALIDATOR_TEXTAREA_LEN) {
				$errStr = sprintf(_MAXLENGTH_ERROR,$smartyAssign->getLang("questionnaire_choice_value"),_VALIDATOR_TEXTAREA_LEN);
				return $errStr;
			}
		}
        return;
    }
}
?>
