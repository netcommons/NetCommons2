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
class Reservation_Validator_ReserveTime extends Validator
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
		if (empty($attributes["repeat_time"]) && empty($attributes["reserve_date"])) {
			return;
		}

    	$container =& DIContainerFactory::getContainer();

	   	$request =& $container->getComponent("Request");
	   	$reservationView =& $container->getComponent("reservationView");

		if (!isset($attributes["timezone_offset"])) {
			$session =& $container->getComponent("Session");
			$attributes["timezone_offset"] = $session->getParameter("_timezone_offset");
			$request->setParameter("timezone_offset", $attributes["timezone_offset"]);
		}
		
		$attributes["allday_flag"] = intval($attributes["allday_flag"]);
		if ($attributes["allday_flag"] == _ON) {
			$reserve_date = $reservationView->dateFormat($attributes["reserve_date"]."000000", $attributes["timezone_offset"], true);
			$reserve_date = $reservationView->dateFormat($reserve_date, $attributes["location"]["timezone_offset"], false, "Ymd");

			$l_base_start_time = $reserve_date. substr($attributes["location"]["start_time"], 8);
			$l_base_end_time = $reserve_date. substr($attributes["location"]["end_time"], 8);

			$server_reserve_time = $reservationView->dateFormat($l_base_start_time, $attributes["location"]["timezone_offset"], true);
			$attributes["start_time"] = $reservationView->dateFormat($server_reserve_time, $attributes["timezone_offset"], false, "His");
			
			$server_reserve_time = $reservationView->dateFormat($l_base_end_time, $attributes["location"]["timezone_offset"], true);
			$attributes["end_time"] = $reservationView->dateFormat($server_reserve_time, $attributes["timezone_offset"], false, "His", true);
		}
		
		if ($attributes["start_time"] > $attributes["end_time"]) {
			return $errStr;
		}
		if ($attributes["start_time"] == $attributes["end_time"]) {
			return $errStr;
		}

		$diff = $reservationView->TimeDiff($attributes["start_time"], $attributes["end_time"]);
		if ($diff < RESERVATION_SELECT_MIN_TIME) {
			return RESERVATION_ERR_RESERVE_MIN_TIME;
		}
		$result = $reservationView->checkReserveTime($attributes["reserve_date"].$attributes["start_time"], $attributes["reserve_date"].$attributes["end_time"]);
		if ($result == false) {
			return $errStr;
		}
		$request->setParameter("start_time_full", $attributes["reserve_date"].$attributes["start_time"]);
		$request->setParameter("end_time_full", $attributes["reserve_date"].$attributes["end_time"]);
    }
}
?>
