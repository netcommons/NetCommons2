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
class Reservation_Validator_RepeatReserveTime extends Validator
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
	   	$reservationView =& $container->getComponent("reservationView");
		
		if (empty($attributes["repeat_time"])) {
			return;
		}
		$error = array();
		foreach ($attributes["repeat_time"] as $i=>$time) {
			if (substr($time["start_time_full"], 0, 4) < RESERVATION_SELECT_MIN_YEAR || substr($time["start_time_full"], 0, 4) > RESERVATION_SELECT_MAX_YEAR) {
				return $errStr;
			}
			$result = $reservationView->checkReserveTime($time["start_time_full"], $time["end_time_full"]);
			if ($result == false) {
				$start_time_full = timezone_date($time["start_time_full"], true);
				$error[] = timezone_date($start_time_full, false, _DATE_FORMAT);
			}
		}
		if (!empty($error)) {
			$errStr .= "<br />";
			$errStr .= implode("<br />", $error);
			return $errStr;
		}
    }
}
?>
