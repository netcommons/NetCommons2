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
class Calendar_View_Main_Init extends Action
{
    // パラメータを受け取るため
	var $block_id = null;
	var $date = null;

    // 使用コンポーネントを受け取るため
	var $session = null;
	var $calendarView = null;
	var $holidayView = null;
	var $commonMain = null;

	// Filterから受け取るため
	var $room_id_arr = null;
	var $room_arr = null;

 	// validatorから受け取るため
	var $enroll_room_arr = null;
	var $calendar_block = null;
	var $date_list = null;

	var $current_timestamp = null;
	var $start_timestamp = null;
	var $end_timestamp = null;

	var $next_year = null;
	var $this_year = null;
	var $prev_year = null;
	var $next_month = null;
	var $this_month = null;
	var $prev_month = null;
	var $next_week = null;
	var $prev_week = null;
	var $next_day = null;
	var $prev_day = null;

	var $today = null;
	var $yesterday = null;
	var $tommorow = null;
	var $after_tommorow = null;

	// 値をセットするため
    var $plan_data = null;
	var $holidays = null;
	var $allow_plan_flag = null;
	var $popup_calendar_date_topId = "";

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
    	if ($mobile_flag == _ON) {
			return 'successMobile';
    	}
    	$_id = $this->session->getParameter("_id");
    	$this->session->setParameter(array("calendar", "_id", $this->block_id), $_id);
		$this->popup_calendar_date_topId = $this->commonMain->getTopId($this->block_id, 0, "popup_calendar_date");
		$this->session->setParameter("_id", $_id);

    	switch ($this->calendar_block["display_type"]) {
    		case CALENDAR_YEARLY:
				$start_date = date("Ym",$this->current_timestamp);
				foreach ($this->date_list as $date => $item) {
					$plan_data = $this->calendarView->getPlanCountByDate(date("Ymd",$item["start_timestamp"]), date("Ymd",$item["end_timestamp"]));
			    	if ($plan_data === false) {
			    		return 'error';
			    	}
					if (!empty($plan_data)) {
						$this->plan_data[$date] = $plan_data;
					}
				}
    			$success = 'successYearly';
    			break;

    		case CALENDAR_S_MONTHLY:
				$start_date = date("Ymd",$this->start_timestamp);
				$end_date = date("Ymd",$this->end_timestamp);

				$this->plan_data = $this->calendarView->getPlanCountByDate($start_date, $end_date);
		    	if ($this->plan_data === false) {
		    		return 'error';
		    	}

				$success = 'successSMonthly';
    			break;

    		case CALENDAR_L_MONTHLY:
				$start_date = date("Ymd",$this->start_timestamp);
				$end_date = date("Ymd",$this->end_timestamp);

				$this->plan_data = $this->calendarView->getPlanByDate($start_date, $end_date, CALENDAR_L_MONTHLY);
		    	if ($this->plan_data === false) {
		    		return 'error';
		    	}
				$success = 'successLMonthly';
    			break;

    		case CALENDAR_WEEKLY:
				$start_date = date("Ymd",$this->start_timestamp);
				$end_date = date("Ymd",$this->end_timestamp);

				$this->plan_data = $this->calendarView->getPlanByDate($start_date, $end_date, CALENDAR_WEEKLY);
		    	if ($this->plan_data === false) {
		    		return 'error';
		    	}

    			$success = 'successWeekly';
    			break;

    		case CALENDAR_DAILY:
    			$success = 'successDaily';
    			break;

    		case CALENDAR_T_SCHEDULE:
    		case CALENDAR_U_SCHEDULE:
    			$success = 'successSchedule';
    			break;

    		default:
				$success = 'error';
    	}

		$this->holidays = $this->holidayView->get(date("Ymd",$this->start_timestamp),date("Ymd",$this->end_timestamp));
    	if ($this->holidays === false) {
    		return 'error';
    	}

    	$this->allow_plan_flag = $this->calendarView->getAllowPlanList();

        return $success;
    }
}
?>
