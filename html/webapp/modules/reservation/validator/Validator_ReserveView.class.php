<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予約の取得
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_ReserveView extends Validator
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
		$reservationView =& $container->getComponent("reservationView");

		$request =& $container->getComponent("Request");
		
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($actionName == "reservation_view_main_reserve_details" || $actionName == "reservation_view_main_reserve_modify") {
			$reserve = $reservationView->getReserve($attributes["reserve_id"]);
			if ($reserve === false) {
				return $errStr;
			}
			if ($reserve["rrule"] != "") {
				$reserve_id = $reservationView->getReserveIdByFirstReserve($reserve["reserve_details_id"]);
				if ($reserve_id === false) {
					return $errStr;
				}
			} else {
				$reserve_id = $reserve["reserve_id"];
			}
			$request->setParameter("rrule_reserve_id", $reserve_id);
			$request->setParameter("reserve", $reserve);
			$request->setParameter("location_id", $reserve["location_id"]);

		} elseif ($actionName == "reservation_action_main_reserve_modify" || $actionName == "reservation_action_main_reserve_delete") {
			$reserve = $reservationView->getReserve($attributes["reserve_id"]);
			if ($reserve === false) {
				return $errStr;
			}
			$request->setParameter("reserve", $reserve);
			
			if ($actionName == "reservation_action_main_reserve_delete") {
				$request->setParameter("location_id", $reserve["location_id"]);
			}
		
		} else {
			$reserve_data = $reservationView->getReserveByDate($attributes["start_date"], $attributes["end_date"]);
			if ($reserve_data === false) {
				return $errStr;
			}
			$request->setParameter("reserve_data", $reserve_data);
		}
    }
}
?>
