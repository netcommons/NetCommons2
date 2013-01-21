<?php

/**
 * 選択肢の存在・文字数チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_CircularChoice extends Validator
{
	/**
	 * 選択肢の存在・文字数チェックバリデータ
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr	 エラー文字列
	 * @param   array   $params	 オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		if ($attributes["reply_type"] == null) {
			return;
		}

		if ($attributes["reply_type"] == CIRCULAR_REPLY_TYPE_TEXTAREA_VALUE) {
			return;
		}

		if ($attributes["choice_id"] == null) {
			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

		if (count($attributes["choice_id"]) < 2) {
			$errStr = $smartyAssign->getLang("circular_choice_err");
			return $errStr;
		}

		foreach($attributes["choice_value"] as $value) {
			if ($value == null) {
				$errStr = sprintf($smartyAssign->getLang("_required"),$smartyAssign->getLang("circular_choice_value"));
				return $errStr;
			}
			if (strlen(bin2hex($value)) / 2 > _VALIDATOR_TEXTAREA_LEN) {
				$errStr = sprintf(_MAXLENGTH_ERROR,$smartyAssign->getLang("circular_choice_value"),_VALIDATOR_TEXTAREA_LEN);
				return $errStr;
			}
		}
		return;
	}
}
?>
