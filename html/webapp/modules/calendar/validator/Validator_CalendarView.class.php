<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレンダー取得
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_CalendarView extends Validator
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
 		$db =& $container->getComponent("DbObject");

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if (!empty($attributes["plan_id"]) && $actionName == "calendar_view_main_init") {
			$params = array("plan_id"=>$attributes["plan_id"]);
			$attributes["calendar_id"] = $db->minExecute("calendar_plan", "calendar_id", $params);
			$request->setParameter("calendar_id", $attributes["calendar_id"]);
		}
		if (empty($attributes["calendar_id"]) && $actionName == "calendar_view_main_init") {
			return;
		}
		
		$calendar_id = $attributes["calendar_id"];

		$calendar_obj = $calendarView->getCalendar($calendar_id);
		if (empty($calendar_obj) && $actionName == "calendar_view_main_init") {
			return;
		}
    	if (empty($calendar_obj)) {
    		return $errStr;
    	}
		$request->setParameter("date", timezone_date($calendar_obj["db_start_date"].$calendar_obj["db_start_time"], false, "Ymd"));

		$calendar_obj["rrule_str"] = $calendarView->stringRRule($calendar_obj["rrule"]);
		$calendar_obj["rrule_arr"] = $calendarView->parseRRule($calendar_obj["rrule"], true);

		$request->setParameter("calendar_obj", $calendar_obj);
		if ($actionName == "calendar_view_main_init") {
			$request->setParameter("display_type", CALENDAR_DAILY);
		} elseif ($actionName == "calendar_view_main_plan_details") {
			$params = array("plan_id"=>$calendar_obj["plan_id"]);
			$rrule_calendar_id = $db->minExecute("calendar_plan", "calendar_id", $params);
			$request->setParameter("rrule_calendar_id", $rrule_calendar_id);

		}
    }
}
?>
