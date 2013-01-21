<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予約の存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_ReserveExists extends Validator
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

		if ($actionName == "reservation_view_main_init") {
			if (!empty($attributes["reserve_details_id"])) {
		 		$db =& $container->getComponent("DbObject");
				$params = array("reserve_details_id"=>$attributes["reserve_details_id"]);
				$attributes["reserve_id"] = $db->minExecute("reservation_reserve", "reserve_id", $params);
				$request->setParameter("reserve_id", $attributes["reserve_id"]);
			}
			if (empty($attributes["reserve_id"])) {
				return;
			}
			if (!$reservationView->reserveExists($attributes["reserve_id"])) {
				$request->setParameter("reserve_id", null);
				return;
			}
			$request =& $container->getComponent("Request");
			$reserve = $reservationView->getReserve($attributes["reserve_id"]);
			if ($reserve === false) {
				return $errStr;
			}
			$request->setParameter("reserve", $reserve);

		} else {
			if (!$reservationView->reserveExists($attributes["reserve_id"])) {
				return $errStr;
			}
		}
    }
}
?>
