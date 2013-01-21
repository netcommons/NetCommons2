<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 評価バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_SetGradeValue extends Validator
{
    /**
     * validate処理
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["grade_value"])) {
			return;
		}

		$attributes["grade_value"] = array_unique($attributes["grade_value"]);

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$request->setParameter("grade_value", $attributes["grade_value"]);

		return;
    }
}
?>
