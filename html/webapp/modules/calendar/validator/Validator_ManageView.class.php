<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 管理データチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_ManageView extends Validator
{
    /**
     * validate実行
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
 		$calendarView =& $container->getComponent("calendarView");

		$manage_list = $calendarView->getManage();
     	if ($manage_list === false) {
    		return $errStr;
    	}
		$request->setParameter("manage_list", $manage_list);

    	$allow_plan_flag = $calendarView->getAllowPlanList();
		$request->setParameter("allow_plan_flag", $allow_plan_flag);
    }
}
?>
