<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 時間チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_PlanTime extends Validator
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
		$attributes["allday_flag"] = intval($attributes["allday_flag"]);
		if ($attributes["allday_flag"] == _ON) {
    		$attributes["start_hour"] = "00";
    		$attributes["end_hour"] = "24";
    		$attributes["start_minute"] = "00";
    		$attributes["end_minute"] = "00";
		}
    	if (empty($attributes["start_hour"]) || !is_numeric($attributes["start_hour"])) {
    		return $errStr;
    	}
    	if (intval($attributes["start_hour"]) < 0 || intval($attributes["start_hour"]) >= 24) {
    		return $errStr;
    	}
    	if (empty($attributes["start_minute"]) || !is_numeric($attributes["start_minute"])) {
    		return $errStr;
    	}
    	if (intval($attributes["start_minute"]) < 0 || intval($attributes["start_minute"]) >= 60) {
    		return $errStr;
    	}
    	if (empty($attributes["end_hour"]) || !is_numeric($attributes["end_hour"])) {
    		return $errStr;
    	}
    	if (intval($attributes["end_hour"]) < 0 || intval($attributes["end_hour"]) > 24) {
    		return $errStr;
    	}
    	if (intval($attributes["end_hour"]) == 24) {
    		$attributes["end_minute"] = "00";
    	}
    	
    	if (empty($attributes["end_minute"]) || !is_numeric($attributes["end_minute"])) {
    		return $errStr;
    	}
    	if (intval($attributes["end_minute"]) < 0 || intval($attributes["end_minute"]) >=60) {
    		return $errStr;
    	}

		$start_time = $attributes["start_date"].$attributes["start_hour"].$attributes["start_minute"]."00";
		$end_time = $attributes["end_date"].$attributes["end_hour"].$attributes["end_minute"]."00";
		if ($start_time > $end_time) {
			return CALENDAR_ERR_FROM_TO_DATE;
		}

    	$container =& DIContainerFactory::getContainer();
	   	$request =& $container->getComponent("Request");
	   	$calendarView =& $container->getComponent("calendarView");

		$time_full = $calendarView->dateFormat($attributes["start_date"].$attributes["start_hour"].$attributes["start_minute"]."00", $attributes["timezone_offset"], true);
		$request->setParameter("start_time_full", $time_full);

		$time_full = $calendarView->dateFormat($attributes["end_date"].$attributes["end_hour"].$attributes["end_minute"]."00", $attributes["timezone_offset"], true, "YmdHis", true);
		$request->setParameter("end_time_full", $time_full);
    }
}
?>
