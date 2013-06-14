<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 時間枠チェック（かぶり、最短時間） 
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_TimeframeTimetable extends Validator
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

		// このValidatorの前にlocationTimetable Validatorが実行されていることが前提

		// 指定された時刻をGMTに修正したうえでチェック
    	$start_time = timezone_date($request->getParameter('start_time'), true, 'His');
    	$end_time = timezone_date($request->getParameter('end_time'), true, 'His');

		// 0:00-24:00 の場合だけあり得る
		if($start_time == $end_time) {
			$end_time = strval(intval($end_time)+240000);
		}

		$ret = $reservationView->getTimeframeDuplicate($attributes['timeframe_id'], $start_time, $end_time);
		if($ret == false) {
			return $errStr;
		}

        // 差分チェックは入力データで行う
        $diff = $reservationView->TimeDiff($request->getParameter('start_time'), $request->getParameter('end_time'));
        if ($diff < RESERVATION_SELECT_MIN_TIME) {
            return RESERVATION_ERR_TIMEFRAME_MIN_TIME;
        }

		return;
    }
}
?>