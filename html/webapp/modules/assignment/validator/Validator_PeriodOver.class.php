<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 期限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_PeriodOver extends Validator
{
    /**
     * 期限チェックバリデータ
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
		$assignmentView =& $container->getComponent("assignmentView");

		if ($assignmentView->existsPeriodOver()) {
			$commonMain =& $container->getComponent("commonMain");
			$assignmentAction =& $commonMain->registerClass(WEBAPP_DIR.'/modules/assignment/components/Action.class.php', "Assignment_Components_Action", "assignmentAction");
			if (!$assignmentAction->setPeriodOver()) {
				return $errStr;
			}
		}

        return;
    }
}
?>