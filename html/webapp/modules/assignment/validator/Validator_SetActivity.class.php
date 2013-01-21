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
class Assignment_Validator_SetActivity extends Validator
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
		if ($attributes["activity"] == _OFF) {
			return;
		}

		$container =& DIContainerFactory::getContainer();
		
		$assignmentView =& $container->getComponent("assignmentView");
		$assignment = $assignmentView->getAssignment();

		if (empty($assignment)) {
        	return _INVALID_INPUT;
        }

		if (empty($assignment["period"])) {
			return;
		}

		$gmt = timezone_date();
		if ($assignment["period"] < $gmt) {
			return $errStr;
		}

		return;
    }
}
?>
