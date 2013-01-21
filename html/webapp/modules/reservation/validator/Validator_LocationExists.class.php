<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設の存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_LocationExists extends Validator
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
		
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		
		if ($actionName == "reservation_view_edit_style_init") {
			if (isset($attributes["reserve_block"])) {
				if ($attributes["reserve_block"]["category_id"] > 0) {
					$count = $reservationView->getCountLocation($attributes["reserve_block"]["category_id"]);
				} else {
					$count = $attributes["location_count"];
				}
			} else {
				$count = $reservationView->getCountLocation();
			}
			if ($count == 0) {
				//return $errStr;
			}
			$request =& $container->getComponent("Request");
			$request->setParameter("location_count", $count);
			
		} elseif ($actionName == "reservation_action_edit_location_sequence") {
			if (!$reservationView->locationExists($attributes["drag_location_id"])) {
				return $errStr;
			}
			if (isset($attributes["drop_location_id"]) && 
					!$reservationView->locationExists($attributes["drop_location_id"])) {
				return $errStr;
			}

		} elseif ($actionName == "reservation_action_edit_style") {
			$request =& $container->getComponent("Request");
			if ($attributes["display_type"] == RESERVATION_DEF_LOCATION) {
				$request->setParameter("location_id", 0);
				return;
			}
			if (!$reservationView->locationExists($attributes["location_id"])) {
				return $errStr;
			}
		} elseif ($actionName == "reservation_view_edit_location_modify" && !isset($attributes["location_id"])) {
			return;

		} elseif ($actionName == "reservation_view_main_reserve_switch_category" && $attributes["location_id"] == "0") {
			if ($attributes["category_id"] > 0) {
				$location = $reservationView->getFirstLocation($attributes["category_id"]);
			} else {
				$location = $reservationView->getFirstLocation();
			}
			if ($location === false) {
				return $errStr;
			}
			$request =& $container->getComponent("Request");
			$request->setParameter("location_id", $location["location_id"]);
			return;
		
		} else {
			if (!$reservationView->locationExists($attributes["location_id"])) {
				return $errStr;
			}
		}
    }
}
?>
