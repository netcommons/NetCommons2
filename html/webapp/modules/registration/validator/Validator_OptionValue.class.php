<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 選択肢チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_OptionValue extends Validator
{
    /**
     * 選択肢チェックバリデータ
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
		$request =& $container->getComponent("Request");

		if ($attributes["item_type"] != REGISTRATION_TYPE_CHECKBOX
				&& $attributes["item_type"] != REGISTRATION_TYPE_RADIO
				&& $attributes["item_type"] != REGISTRATION_TYPE_SELECT) {
			$request->setParameter("option_value", "");
			return;
		}

		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

		if (empty($attributes["option_value"])) {
			$errStr = $smartyAssign->getLang("registration_option_short");
			return $errStr;
		}

		if (count($attributes["option_value"]) != count(array_unique($attributes["option_value"])) ) {
			$errStr = $smartyAssign->getLang("registration_option_duplicate");
			return $errStr;
		}

		$invalidCharacters = preg_grep("/\\". REGISTRATION_OPTION_SEPARATOR. "/", $attributes["option_value"]);
		if (!empty($invalidCharacters)) {
			$errStr = $smartyAssign->getLang("registration_option_character_invalid");
			return $errStr;
		}

		$optionValue = implode(REGISTRATION_OPTION_SEPARATOR, $attributes["option_value"]);
		if (strlen(bin2hex($optionValue)) > _VALIDATOR_TEXTAREA_LEN) {
			$errStr = $smartyAssign->getLang("registration_option_over");
			return $errStr;
		}

		if (empty($optionValue)
				|| substr($optionValue, 0, 1) == REGISTRATION_OPTION_SEPARATOR
				|| substr($optionValue, -1, 1) == REGISTRATION_OPTION_SEPARATOR
				|| strpos($optionValue, REGISTRATION_OPTION_SEPARATOR. REGISTRATION_OPTION_SEPARATOR)) {
			$errStr = $smartyAssign->getLang("registration_option_empty");
			return $errStr;
		}

		$request->setParameter("option_value", $optionValue);

        return;
    }
}
?>
