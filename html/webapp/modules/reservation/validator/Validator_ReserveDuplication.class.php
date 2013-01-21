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
class Reservation_Validator_ReserveDuplication extends Validator
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
		
		$result = $reservationView->checkReserveDuplication();

		if ($result === true) {
			return;
		} elseif (is_array($result)) {
			$errStr .= "<br />";
			$errStr .= implode("<br />", $result);
			return $errStr;

		} else {
			return $errStr;
		}
		
    }
}
?>
