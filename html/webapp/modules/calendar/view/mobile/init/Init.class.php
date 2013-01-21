<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレンダーの表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Mobile_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $display_type = null;
	var $date = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $preexecute = null;
	var $calendarView = null;
	var $mobileView = null;

	//AllowIdListのパラメータを受け取るため
	var $room_id_arr = null;
	var $block_id_arr = null;

	// 値をセットするため
	//--- 共通 ---
	var $plan_data = null;
	var $date_string = null;
	var $today = null;
	var $prev_year = null;
	var $this_year = null;
	var $next_year = null;
	var $prev_month = null;
	var $this_month = null;
	var $next_month = null;
	var $prev_week = null;
	var $next_week = null;
	var $prev_day = null;
	var $next_day = null;
	var $goto_today = null;
	//--- 月表示 ---
	var $start_timestamp = null;
	var $end_timestamp = null;
	//--- 年表示 ---
	var $month_data = null;

	var $html_flag = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		if (!isset($this->display_type)) {
			$this->display_type = $this->session->getParameter(array("calendar_mobile", "display_type"));
		}
		$this->display_type = intval($this->display_type);
		if ($this->display_type == 0) {
			$this->display_type = CALENDAR_DEFAULT_MOBILE;
		}
		$this->session->setParameter(array("calendar_mobile", "display_type"), $this->display_type);

		if (!isset($this->date)) {
			$this->date = $this->session->getParameter(array("calendar_mobile", "current_date"));
		}
		if (!isset($this->date)) {
			$this->date = timezone_date(null, false, "Ymd");
		}
    	$this->today = timezone_date(null, false, "Ymd");
		$this->prev_year = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							1, 1, intval(substr($this->date,0,4)))-1);
		$this->this_year = substr($this->date,0,4). "0101";
		$this->next_year = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							1, 1, intval(substr($this->date,0,4)))+1);
		$this->prev_month = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							intval(substr($this->date,4,2))-1, 1, intval(substr($this->date,0,4))));
		$this->this_month = substr($this->date,0,6). "01";
		$this->next_month = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							intval(substr($this->date,4,2))+1, 1, intval(substr($this->date,0,4))));
		$this->prev_week = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							intval(substr($this->date,4,2)), intval(substr($this->date,6,2))-7, intval(substr($this->date,0,4))));
		$this->next_week = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							intval(substr($this->date,4,2)), intval(substr($this->date,6,2))+7, intval(substr($this->date,0,4))));
		$this->prev_day = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							intval(substr($this->date,4,2)), intval(substr($this->date,6,2))-1, intval(substr($this->date,0,4))));
		$this->next_day = date("Ymd", mktime(intval(substr($this->date,8,2)), intval(substr($this->date,10,2)), intval(substr($this->date,12,2)),
							intval(substr($this->date,4,2)), intval(substr($this->date,6,2))+1, intval(substr($this->date,0,4))));

		$this->session->setParameter(array("calendar_mobile", "current_date"), $this->date);
    	$insert_time = timezone_date($this->date."000000", true, "YmdHis");
    	$this->timestamp = mktime(0, 0, 0, intval(substr($this->date,4,2)), intval(substr($this->date,6,2)), intval(substr($this->date,0,4)));

    	switch ($this->display_type) {
    		case CALENDAR_S_MONTHLY:
    		case CALENDAR_L_MONTHLY:
				$this->start_timestamp = mktime(0, 0, 0, intval(substr($this->this_month,4,2)), 1, intval(substr($this->this_month,0,4)));
				$this->end_timestamp = $this->start_timestamp + date("t",$this->start_timestamp) * 86400;
				$this->plan_data = $this->calendarView->getPlanCountByDate(date("Ymd",$this->start_timestamp), date("Ymd",$this->end_timestamp));
		    	if ($this->plan_data === false) {
		    		return 'error';
		    	}
    			$this->goto_today = (substr($this->today, 0, 6) == substr($this->this_month, 0, 6)) ? _OFF : _ON;
		    	$this->date_string = $this->calendarView->dateFormat($insert_time, null, false, CALENDAR_MOBILE_MONTH_FORMAT);
    			$success = 'successMonthly';
    			break;
    		case CALENDAR_WEEKLY:
				$this->start_timestamp = mktime(0, 0, 0, intval(substr($this->date,4,2)), intval(substr($this->date,6,2)), intval(substr($this->date,0,4)));
				$this->end_timestamp = $this->start_timestamp + 7 * 86400;
				$this->plan_data = $this->calendarView->getPlanByDate(date("Ymd",$this->start_timestamp), date("Ymd",$this->end_timestamp), $this->display_type);
		    	if ($this->plan_data === false) {
		    		return 'error';
		    	}
    			$this->goto_today = ($this->today == $this->date) ? _OFF : _ON;
		    	$this->date_string = $this->calendarView->dateFormat($insert_time, null, false, CALENDAR_MOBILE_MONTH_FORMAT);
    			$success = 'successWeekly';
    			break;
    		case CALENDAR_DAILY:
				$this->plan_data = $this->calendarView->getPlanByDate($this->date, $this->date, $this->display_type);
		    	if ($this->plan_data === false) {
		    		return 'error';
		    	}
		    	$this->date_string = $this->calendarView->dateFormat($insert_time, null, false, CALENDAR_MOBILE_DATE_FORMAT);
    			$this->goto_today = ($this->today == $this->date) ? _OFF : _ON;
				$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
    			$success = 'successDaily';
    			break;
    		default:
				return 'error';
    	}
    	return $success;
    }
}
?>
