<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブロック配置チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_Main extends Validator
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
 		$session =& $container->getComponent("Session");

		$today =  timezone_date(null, false, "Ymd");
		$today_timestamp = mktime(0,0,0,substr($today,4,2),substr($today,6,2),substr($today,0,4));
		
		$calendar_block = $attributes["calendar_block"];

    	switch ($calendar_block["display_type"]) {
    		case CALENDAR_YEARLY:
				switch ($calendar_block["start_pos_yearly"]) {
					case CALENDAR_START_APRIL:
						$month = intval(substr($attributes["date"],4,2));
						if ($month >= 1 && $month < 4) {
							$set_current_timestamp = mktime(0,0,0,4,1,substr($attributes["date"],0,4) - 1);
						} else {
			    			$set_current_timestamp = mktime(0,0,0,4,1,substr($attributes["date"],0,4));
						}
						break;
					case CALENDAR_START_JANUARY:
		    			$set_current_timestamp = mktime(0,0,0,1,1,substr($attributes["date"],0,4));
						break;
					case CALENDAR_START_LAST_MONTH:
		    			$set_current_timestamp = mktime(0,0,0,substr($today,4,2)-1,1,substr($attributes["date"],0,4));
						break;
					default: 
		    			$set_current_timestamp = mktime(0,0,0,substr($today,4,2),1,substr($attributes["date"],0,4));
		
				}
    			break;
    		case CALENDAR_S_MONTHLY:
    		case CALENDAR_L_MONTHLY:
				$set_current_timestamp = mktime(0,0,0,substr($attributes["date"],4,2),1,substr($attributes["date"],0,4));
    			break;
    		case CALENDAR_WEEKLY:
    		case CALENDAR_DAILY:
				$set_current_timestamp = mktime(0,0,0,substr($attributes["date"],4,2),substr($attributes["date"],6,2),substr($attributes["date"],0,4));
    			break;
    		case CALENDAR_T_SCHEDULE:
    		case CALENDAR_U_SCHEDULE:
				$set_current_timestamp = mktime(0,0,0,substr($today,4,2),substr($today,6,2),substr($today,0,4));
    			break;
    		default:
    			if ($session->getParameter("_mobile_flag") == _ON) {
    				$set_current_timestamp = mktime(0,0,0,substr($attributes["date"],4,2),substr($attributes["date"],6,2),substr($attributes["date"],0,4));
    			} else {
					return $errStr;
    			}
    	}

		switch ($calendar_block["start_pos_yearly"]) {
			case CALENDAR_START_APRIL:
				$month = intval(substr($attributes["date"],4,2));
				if ($month >= 1 && $month < 4) {
					$this_year = date("Y", mktime(0,0,0,1,1,substr($attributes["date"],0,4) - 1));
				} else {
					$this_year = date("Y", mktime(0,0,0,1,1,substr($attributes["date"],0,4)));
				}
				break;
			case CALENDAR_START_JANUARY:
				$this_year = date("Y", mktime(0,0,0,1,1,substr($attributes["date"],0,4)));
				break;
			case CALENDAR_START_LAST_MONTH:
				$from_month = date("Ym", mktime(0,0,0,substr($today,4,2)-1,1,substr($attributes["date"],0,4)));
				$to_month = date("Ym", mktime(0,0,0,substr($today,4,2)+11,1,substr($attributes["date"],0,4)));
				if ($from_month <= $attributes["date"] && $to_month > $attributes["date"]) {
					$this_year = date("Y", mktime(0,0,0,1,1,substr($attributes["date"],0,4)));
				} else {
					$this_year = date("Y", mktime(0,0,0,1,1,substr($attributes["date"],0,4)-1));
				}
				break;
			default: 
				$from_month = date("Ym", mktime(0,0,0,substr($today,4,2),1,substr($attributes["date"],0,4)));
				$to_month = date("Ym", mktime(0,0,0,substr($today,4,2)+12,1,substr($attributes["date"],0,4)));
				if ($from_month <= $attributes["date"] && $to_month > $attributes["date"]) {
					$this_year = date("Y", mktime(0,0,0,1,1,substr($attributes["date"],0,4)));
				} else {
					$this_year = date("Y", mktime(0,0,0,1,1,substr($attributes["date"],0,4)-1));
				}
		}

		$this_month = date("Ym", mktime(0,0,0,substr($attributes["date"],4,2),1,substr($attributes["date"],0,4)));

 		$prev_day = date("Ymd", $set_current_timestamp - 1 * 86400);
		$next_day = date("Ymd", $set_current_timestamp + 1 * 86400);

		$next_year = date("Ym", mktime(0,0,0,substr($attributes["date"],4,2),1,substr($attributes["date"],0,4)+1));
		$prev_year = date("Ym", mktime(0,0,0,substr($attributes["date"],4,2),1,substr($attributes["date"],0,4)-1));
		$next_month = date("Ym", mktime(0,0,0,substr($attributes["date"],4,2)+1,1,substr($attributes["date"],0,4)));
		$prev_month = date("Ym", mktime(0,0,0,substr($attributes["date"],4,2)-1,1,substr($attributes["date"],0,4)));

		$next_week = date("Ymd", $set_current_timestamp + 7 * 86400);
		$prev_week = date("Ymd", $set_current_timestamp - 7 * 86400);

		$yesterday = date("Ymd", $today_timestamp - 1 * 86400);
		$tommorow = date("Ymd", $today_timestamp + 1 * 86400);
		$after_tommorow = date("Ymd", $today_timestamp + 2 * 86400);

    	switch ($calendar_block["display_type"]) {
    		case CALENDAR_YEARLY:
				$start_date = date("Ym",$set_current_timestamp);
				$date_list = array();
				for ($i=0; $i<12; $i++) {
					$date = date("Ym", mktime(0, 0, 0, substr($start_date,4,2)+$i, 1, substr($start_date,0,4)));
					$date_list[$date] = array();
					$current_timestamp = mktime(0, 0, 0, substr($date,4,2), 1, substr($date,0,4));
		
					$start_timestamp = $current_timestamp - date("w",$current_timestamp) * 86400;
					$end_timestamp = $current_timestamp + date("t",$current_timestamp) * 86400;
					$end_wday = date("w", $end_timestamp);
					if ($end_wday != 0) {
		    			$end_timestamp = $end_timestamp + (7 - $end_wday) * 86400; 
					}
					$date_list[$date]["current_timestamp"] = $current_timestamp;
					$date_list[$date]["start_timestamp"] = $start_timestamp;
					$date_list[$date]["end_timestamp"] = $end_timestamp;
					if ($i == 0) {
						$set_start_timestamp = $start_timestamp;
					}
				}
				$set_end_timestamp = $end_timestamp;
				
				$request->setParameter("date_list", $date_list);
    			break;
    		case CALENDAR_S_MONTHLY:
    		case CALENDAR_L_MONTHLY:
				$set_start_timestamp = $set_current_timestamp - date("w",$set_current_timestamp) * 86400;
				$end_timestamp = $set_current_timestamp + date("t",$set_current_timestamp) * 86400;
				$end_wday = date("w", $end_timestamp);
				if ($end_wday == 0) {
					$set_end_timestamp = $end_timestamp;
				} else {
					$set_end_timestamp = $end_timestamp + (7 - $end_wday) * 86400; 
				}
    			break;
    		case CALENDAR_WEEKLY:
				if ($calendar_block["start_pos_weekly"] == CALENDAR_START_YESTERDAY) {
					$set_start_timestamp = $set_current_timestamp - 1 * 86400;
				} else {
					$set_start_timestamp = $set_current_timestamp;
				}
				$set_end_timestamp = $set_start_timestamp + 7 * 86400;
     			break;
    		case CALENDAR_DAILY:
				$set_start_timestamp = $set_current_timestamp;
				$set_end_timestamp = $set_current_timestamp;
    			break;
    		case CALENDAR_T_SCHEDULE:
    		case CALENDAR_U_SCHEDULE:
				if ($calendar_block["start_pos_weekly"] == CALENDAR_START_YESTERDAY) {
					$set_start_timestamp = $set_current_timestamp - 1 * 86400;
				} else {
					$set_start_timestamp = $set_current_timestamp;
				}
				$set_end_timestamp = $set_start_timestamp + $calendar_block["display_count"] * 86400;
    			break;
    		default:
    			if ($session->getParameter("_mobile_flag") == _ON) {
    				$set_start_timestamp = $set_current_timestamp;
    				$set_end_timestamp = $set_current_timestamp;
    			} else {
					return $errStr;
    			}
    	}

		$request->setParameter("next_year", $next_year);
		$request->setParameter("this_year", $this_year);
		$request->setParameter("prev_year", $prev_year);
		$request->setParameter("next_month", $next_month);
		$request->setParameter("this_month", $this_month);
		$request->setParameter("prev_month", $prev_month);
		$request->setParameter("next_week", $next_week);
		$request->setParameter("prev_week", $prev_week);
		$request->setParameter("prev_day", $prev_day);
		$request->setParameter("next_day", $next_day);

		$request->setParameter("today", $today);
		$request->setParameter("yesterday", $yesterday);
		$request->setParameter("tommorow", $tommorow);
		$request->setParameter("after_tommorow", $after_tommorow);

		$request->setParameter("current_timestamp", $set_current_timestamp);
		$request->setParameter("start_timestamp", $set_start_timestamp);
		$request->setParameter("end_timestamp", $set_end_timestamp);
    }
}
?>
