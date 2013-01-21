<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日付チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_Date extends Validator
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

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$today = timezone_date(null, false, "Ymd");
		$request->setParameter("today", $today);
		
		if ($actionName == "reservation_view_main_reserve_add" || $actionName == "reservation_view_main_reserve_modify") {
			if (empty($attributes["date"])) {
				return;
			}
			$view_date = $attributes["date"];
			$key = "reserve_date";

		} else {
	    	if (empty($attributes["view_date"])) {
				$request->setParameter("view_date", $today);
	    		return;
	    	}
	    	$view_date = $attributes["view_date"];
	    	$key = "view_date";
		}

    	if (strlen($view_date) == 8 || strlen($view_date) == 6) {
    	} else {
    		return $errStr;
    	}
    	if (strlen($view_date) == 6) {
    		$view_date .= "01";
    	}
    	
    	$pattern = "/^([0-9]{4})([0-9]{2})([0-9]{2})$/";
    	if (!preg_match($pattern, $view_date, $matches)) {
    		return $errStr;
    	}
    	
    	if ($matches[1] < RESERVATION_SELECT_MIN_YEAR) {
    		$view_date = RESERVATION_SELECT_MIN_YEAR. "0101";
    	}
    	if ($matches[1] > RESERVATION_SELECT_MAX_YEAR - 1) {
    		$view_date = (RESERVATION_SELECT_MAX_YEAR - 1). "1231";
    	}

    	$request->setParameter($key, $view_date);
    }
}
?>
