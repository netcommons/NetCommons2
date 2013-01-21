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
class Reservation_Validator_Time extends Validator
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
		
		if ($actionName == "reservation_action_main_reserve_add" || 
			$actionName == "reservation_action_main_reserve_modify") {
			if ($attributes["allday_flag"] == _ON) {
				return;
			}

			if (isset($attributes["start_hour"]) && isset($attributes["start_minute"])) {
				$time = $attributes["start_hour"].$attributes["start_minute"];
				$key = "start_time";
			}
			if (isset($attributes["end_hour"])) {
				if (empty($attributes["end_minute"])) {
					$attributes["end_minute"] = "00";
				}
				$time = $attributes["end_hour"].$attributes["end_minute"];
				$key = "end_time";
			}
	    	if (empty($key)) {
	    		return $errStr;
	    	}
		
		} elseif ($actionName == "reservation_view_main_reserve_add" || 
			$actionName == "reservation_view_main_reserve_modify") {
				
			if (isset($attributes["start_hour"]) && isset($attributes["start_minute"])) {
				$time = $attributes["start_hour"].$attributes["start_minute"];
				$key = "start_time";
			}
			if (isset($attributes["end_hour"])) {
				if (empty($attributes["end_minute"])) {
					$attributes["end_minute"] = "00";
				}
				$time = $attributes["end_hour"].$attributes["end_minute"];
				$key = "end_time";
			}
			if (empty($key) && isset($attributes["time"])) {
				$time = $attributes["time"];
				$key = "time";
			}
	    	if (empty($key)) {
	    		return;
	    	}
				
		} else {
			$time = $attributes["time"];
			$key = "time";
	    	if (empty($time)) {
	    		return;
	    	}
		}
		
		$hour = intval(substr($time, 0, 2));
		$minute = intval(substr($time, 2, 2));

    	if ($hour < 0 || $hour > 24 || ($hour == 24 && $minute != 0)) {
    		return $errStr;
    	}

    	if ($minute < 0 || $minute >= 60) {
    		return $errStr;
    	}
		
		$request->setParameter($key, sprintf("%02d", $hour).sprintf("%02d", $minute)."00");
    }
}
?>
