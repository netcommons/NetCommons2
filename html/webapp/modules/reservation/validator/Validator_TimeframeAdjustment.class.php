<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 時間枠←→実時間
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_TimeframeAdjustment extends Validator
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

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$block = $request->getParameter('reserve_block');
		if($block['display_timeframe'] == _OFF) {
			return;
		}

		if ($actionName == "reservation_view_main_reserve_add") {
			if(!isset($attributes['timeframe_id'])) {
				return;
			}
			$timeframe_id = $attributes['timeframe_id'];
			$timeframe = $reservationView->getTimeframe($timeframe_id);
			if($timeframe && is_array($timeframe)) {
				$request->setParameter('start_hour', $timeframe['start_time_view_hour']);
				$request->setParameter('start_minute', $timeframe['start_time_view_min']);
				$request->setParameter('end_hour', $timeframe['end_time_view_hour']);
				$request->setParameter('end_minute', $timeframe['end_time_view_min']);
				$request->setParameter('start_timeframe_id', $timeframe['timeframe_id']);
				$request->setParameter('end_timeframe_id', $timeframe['timeframe_id']);
				$request->setParameter('start_timeframe', $timeframe);
				$request->setParameter('end_timeframe', $timeframe);
			}
		}
		elseif($actionName == "reservation_view_main_reserve_modify" ||
				$actionName == "reservation_view_main_reserve_details") {
			$reserve = $request->getParameter("reserve");
			if($reserve) {
				$start_timeframe = $reservationView->getTimeframeByStartTime($reserve['start_time']);
				if($start_timeframe) {
					$request->setParameter('start_timeframe_id', $start_timeframe['timeframe_id']);
					$request->setParameter('start_timeframe', $start_timeframe);
				}
				$end_timeframe = $reservationView->getTimeframeByEndTime($reserve['end_time']);
				if($end_timeframe) {
					$request->setParameter('end_timeframe_id', $end_timeframe['timeframe_id']);
					$request->setParameter('end_timeframe', $end_timeframe);
				}
			}
		}
		return;
    }
}
?>