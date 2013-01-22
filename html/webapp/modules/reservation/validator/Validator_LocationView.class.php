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
class Reservation_Validator_LocationView extends Validator
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

		if ($actionName == "reservation_view_edit_location_init" || $actionName == "reservation_view_main_movedate" ||
				$actionName == "reservation_view_admin_search") {

			$location_list = $reservationView->getLocations();
			$request->setParameter("location_list", $location_list);

 	   	} elseif ($actionName == "reservation_view_edit_style_init") {
			$location_list = $reservationView->getLocations();
			if ($location_list === false) {
				return $errStr;
			}
			$request->setParameter("location_list", $location_list);
			$request->setParameter("location_count", count($location_list));

 	   	} elseif ($actionName == "reservation_view_edit_style_switchcate") {
			if ($attributes["category_id"] > 0) {
				$location_list = $reservationView->getLocations($attributes["category_id"]);
			} else {
				$location_list = $reservationView->getLocations();
			}
			if ($location_list === false) {
				return $errStr;
			}
			$request->setParameter("location_list", $location_list);
			$request->setParameter("location_count", count($location_list));

		} elseif ($actionName == "reservation_view_main_init") {
			if ($attributes["category_id"] > 0) {
				$location_list = $reservationView->getLocations($attributes["category_id"]);
			} else {
				$location_list = $reservationView->getLocations();
			}
			if ($location_list === false) {
				return $errStr;
			}
			$request->setParameter("location_list", $location_list);
			$request->setParameter("location_count", count($location_list));

		} elseif ($actionName == "reservation_view_main_reserve_add" || $actionName == "reservation_view_main_reserve_modify" 
				|| $actionName == "reservation_view_main_reserve_switch_category" || $actionName == "reservation_view_main_reserve_switch_location"
				|| $actionName == "reservation_action_main_reserve_add" || $actionName == "reservation_action_main_reserve_modify"
				|| $actionName == "reservation_action_edit_import"
				) {
			$location = $reservationView->getLocation($attributes["location_id"]);
			if (empty($location)) {
				return $errStr;
			}
			$allow_add_rooms = $reservationView->getAddLocationRoom($location);
			if (empty($allow_add_rooms)) {
				$allow_add_rooms = array();
			}
			$request->setParameter("location", $location);
			$request->setParameter("allow_add_rooms", $allow_add_rooms);

			if ($actionName == "reservation_view_main_reserve_add" || $actionName == "reservation_view_main_reserve_modify"
				|| $actionName == "reservation_view_main_reserve_switch_category"
				) {
				if ($attributes["category_id"] > 0) {
					$location_list = $reservationView->getLocations($attributes["category_id"]);
				} else {
					$location_list = $reservationView->getLocations();
				}
				if ($location_list === false) {
					return $errStr;
				}
				$request->setParameter("location_list", $location_list);
				$request->setParameter("location_count", count($location_list));
			}

		} elseif ($actionName == "reservation_view_edit_location_modify" && !isset($attributes["location_id"])) {
			return;

		} else {
			$location = $reservationView->getLocation($attributes["location_id"], true);
			if (empty($location)) {
				return $errStr;
			}
			$request->setParameter("location", $location);

			$select_rooms = $reservationView->getLocationRoom($attributes["location_id"]);
			if (empty($select_rooms)) {
				$select_rooms = array();
			}
			$request->setParameter("select_rooms", $select_rooms);
		}
    }
}
?>
