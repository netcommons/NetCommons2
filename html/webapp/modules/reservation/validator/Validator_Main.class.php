<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_Main extends Validator
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

    	$reserve_block = $attributes["reserve_block"];
    	if ($reserve_block["display_type"] == RESERVATION_DEF_MONTHLY) {
    		$view_date = substr($attributes["view_date"], 0, 6). "01";
    	} else {
    		$view_date = $attributes["view_date"];
    	}
    	
		$current_timestamp = mktime(0, 0, 0, substr($view_date,4,2), substr($view_date,6,2), substr($view_date,0,4));
		switch ($reserve_block["display_type"]) {
			case RESERVATION_DEF_MONTHLY:
				$start_timestamp = $current_timestamp - date("w",$current_timestamp) * 86400;
				$end_timestamp = $current_timestamp + date("t",$current_timestamp) * 86400;
				$end_wday = date("w", $end_timestamp);
				if ($end_wday == 0) {
					$end_timestamp = $end_timestamp;
				} else {
					$end_timestamp = $end_timestamp + (7 - $end_wday) * 86400; 
				}
				$category_id = 0;
				break;

			case RESERVATION_DEF_WEEKLY:
				$start_timestamp = $current_timestamp;
				$end_timestamp = $start_timestamp + 7 * 86400;
				$category_id = 0;
				break;

			case RESERVATION_DEF_LOCATION:
				$start_timestamp = $current_timestamp;
				$end_timestamp = $current_timestamp;
				$category_id = $reserve_block["category_id"];
				break;

			default:
				return $errStr;
		}

		$this_month = date("Ymd", mktime(0, 0, 0, intval(substr($view_date,4,2)), 1, intval(substr($view_date,0,4))));
		$next_month = date("Ymd", mktime(0, 0, 0, intval(substr($view_date,4,2)) + 1, 1, intval(substr($view_date,0,4))));
		$prev_month = date("Ymd", mktime(0, 0, 0, intval(substr($view_date,4,2)) - 1, 1, intval(substr($view_date,0,4))));
		$next_week = date("Ymd", $current_timestamp + 7 * 86400);
		$prev_week = date("Ymd", $current_timestamp - 7 * 86400);
		$prev_day = date("Ymd", $current_timestamp - 1 * 86400);
		$next_day = date("Ymd", $current_timestamp + 1 * 86400);

    	$request =& $container->getComponent("Request");

		$request->setParameter("this_month", $this_month);
		$request->setParameter("next_month", $next_month);
		$request->setParameter("prev_month", $prev_month);
		$request->setParameter("next_week", $next_week);
		$request->setParameter("prev_week", $prev_week);
		$request->setParameter("prev_day", $prev_day);
		$request->setParameter("next_day", $next_day);

		$request->setParameter("current_timestamp", $current_timestamp);
		$request->setParameter("start_timestamp", $start_timestamp);
		$request->setParameter("end_timestamp", $end_timestamp);

		$request->setParameter("start_date", date("Ymd",$start_timestamp));
		$request->setParameter("end_date", date("Ymd",$end_timestamp));

		$request->setParameter("input_date", date(_INPUT_DATE_FORMAT, $current_timestamp));
 
		$request->setParameter("category_id", $category_id);
    }
}
?>
