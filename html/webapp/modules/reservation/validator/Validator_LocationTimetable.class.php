<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 使用可能時間チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_LocationTimetable extends Validator
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

		if (!empty($attributes["allday_flag"])) {
			$attributes["start_hour"] = "00";
			$attributes["start_minute"] = "00";
			$attributes["end_hour"] = "24";
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
		   	$request->setParameter("end_minute", $attributes["end_minute"]);
    	}
    	
    	if (empty($attributes["end_minute"]) || !is_numeric($attributes["end_minute"])) {
    		return $errStr;
    	}
    	if (intval($attributes["end_minute"]) < 0 || intval($attributes["end_minute"]) >=60) {
    		return $errStr;
    	}
		
		$reservationView =& $container->getComponent("reservationView");
		
		$today = $reservationView->dateFormat(null, $attributes["timezone_offset"], false, "Ymd");
		$start_time = $today.$attributes["start_hour"].$attributes["start_minute"]."00";
		$end_time = $today.$attributes["end_hour"].$attributes["end_minute"]."00";

		if ($start_time >= $end_time) {
			return RESERVATION_ERR_FROM_TO_DATE;
		}
		
		if (empty($attributes["rrule_byday"])) {
			return RESERVATION_ERR_WDAY;
		}
		
		$time_table = "";
		$wday_arr = explode("|", RESERVATION_WDAY);
		foreach ($attributes["rrule_byday"] as $i=>$wday) {
			if (in_array($wday, $wday_arr)) {
				$time_table .= $wday.",";
			}
		}
		$time_table = substr($time_table, 0, -1);
		if (empty($time_table)) {
			return RESERVATION_ERR_WDAY;
		}
		
		$request->setParameter("start_time", $start_time);
		$request->setParameter("end_time", $end_time);
		$request->setParameter("time_table", $time_table);
    }
}
?>
