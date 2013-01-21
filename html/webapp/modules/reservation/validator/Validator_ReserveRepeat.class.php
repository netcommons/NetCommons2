<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 繰返しチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_ReserveRepeat extends Validator
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
		
		if ($actionName == "reservation_action_main_reserve_add" && intval($attributes["details_flag"]) == _OFF) {
			return;
		}
		
		$rrule = $reservationView->getInputRRule();
		if (isset($rrule["error_mess"])) {
			return $rrule["error_mess"];
		}
		$request->setParameter("rrule", $rrule);
		
		$repeat_time = $reservationView->getRepeatReserve($rrule);
		$request->setParameter("repeat_time", $repeat_time);

		if ($actionName == "reservation_action_main_reserve_modify") {
			$reserve_id_arr = $reservationView->getRepeatReserveId($attributes["reserve"]["reserve_details_id"]);
			$request->setParameter("reserve_id_arr", $reserve_id_arr);
		}
    }
}
?>
