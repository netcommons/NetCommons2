<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブロック配置チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_Block extends Validator
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
 		$session =& $container->getComponent("Session");
 		$calendarView =& $container->getComponent("calendarView");

		$request->setParameter("today", timezone_date(null, false, "Ymd"));

    	$mobile_flag = $session->getParameter("_mobile_flag");
		if ($mobile_flag == _ON) {
			return;
		}

 		$block_id = $attributes["block_id"];
		$display_type = isset($attributes["display_type"]) ? intval($attributes["display_type"]) : null;

		$calendar_block = $calendarView->getBlock($display_type);
		if ($calendar_block === false) {
			return $errStr;
		}
		$request->setParameter("calendar_block", $calendar_block);
    }
}
?>
