<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブロックチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_Block extends Validator
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
		
		if (!empty($attributes["reserve"])) {
			$request->setParameter("view_date", $attributes["reserve"]["start_date_view"]);
			$request->setParameter("current_time", $attributes["reserve"]["start_time_view"]);
			$request->setParameter("location_id", $attributes["reserve"]["location_id"]);
			$request->setParameter("category_id", $attributes["reserve"]["category_id"]);
		} else {
			$request->setParameter("current_time", "");
		}
		
		if (isset($attributes["display_type"])) {
			$reserve_block = $reservationView->getBlock($attributes["display_type"]);
		} else {
			$reserve_block = $reservationView->getBlock();
		}
		
    	if ($reserve_block === false) {
    		return $errStr;
    	}
		switch ($reserve_block["display_type"]) {
			case RESERVATION_DEF_MONTHLY:
			case RESERVATION_DEF_WEEKLY:
			case RESERVATION_DEF_LOCATION:
				break;
			default:
				return $errStr;
		}
		
		$request->setParameter("reserve_block", $reserve_block);
    }
}
?>
