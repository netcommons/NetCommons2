<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予約の編集チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_EditReserve extends Validator
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

		if ($attributes["reserve"]["hasModifyAuth"] == _OFF) {
			return $errStr;
		}

		if ($attributes["reserve"]["location_id"] != $attributes["location_id"]) {
			if (empty($attributes["allow_add_rooms"])) {
				return $errStr;
			}
			if ($attributes["reserve_room_id"] == "0") {
				return;
			}
			if (!in_array($attributes["reserve_room_id"], $attributes["allow_add_rooms"])) {
				return $errStr;
			}
		}
    }
}
?>
