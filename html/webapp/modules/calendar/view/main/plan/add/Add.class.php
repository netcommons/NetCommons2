<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定追加の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Main_Plan_Add extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $start_date = null;
	var $date = null;
	var $time = null;
	var $details_flag = null;
	var $plan_room_id = null;
	var $title = null;
	var $title_icon = null;
	var $room_id = null;
	var $room_arr = null;
	var $allday_flag = null;
	var $notification_mail = null;

    // 使用コンポーネントを受け取るため
	var $calendarView = null;
	var $session = null;
	var $request = null;
	var $commonMain = null;

	// validatorから受け取るため
	var $allow_plan_flag = null;
	var $manage_list = null;

    // 値をセットするため
	var $calendar_block = null;
	var $holidays = null;
	var $current_timestamp = null;
	var $calendar_obj = null;
	var $start_time = null;
	var $end_time = null;
	var $edit_rrule = null;
	var $timezone_list = null;
	var $block = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if (!empty($this->start_date)) {
    		$this->date = $this->start_date;
    		$this->request->setParameter("date", $this->date);
    	}
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
    	$this->details_flag = intval($this->details_flag);
    	$this->notification_mail = intval($this->notification_mail);
    	if ($mobile_flag == _ON) {
    		if (empty($this->date)) {
    			$this->date = $this->session->getParameter(array("calendar_mobile", "current_date"));
    		}
    	} else {
			if (empty($this->date) || !preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})$/", $this->date, $matches) || !checkdate($matches[2], $matches[3], $matches[1])) {
				$this->date = timezone_date(null, false, "Ymd");
			}
    	}

		if (isset($this->allday_flag)) {
			$this->allday_flag = intval($this->allday_flag);
		} elseif (empty($this->time) && empty($this->start_time) && empty($this->end_time)) {
			$this->allday_flag = _ON;
		} else {
			$this->allday_flag = _OFF;
		}
		if (empty($this->time) && empty($this->start_time) && empty($this->end_time)) {
			if (date("H") == "23") {
				$this->time = date("H")."00";
			} else {
				$this->time = sprintf("%02d", (intval(date("H")) + 1))."00";
			}
		}
		if (empty($this->start_time)) {
			$this->start_time = $this->time."00";
		}
		if (empty($this->end_time)) {
			if (substr($this->time,0,2) == "23") {
				$this->end_time = "240000";
			} else {
				$this->end_time = sprintf("%02d", intval(substr($this->time,0,2))+1).substr($this->time,2,2)."00";
			}
		}
		$current_timestamp = mktime(0,0,0,substr($this->date,4,2),substr($this->date,6,2),substr($this->date,0,4));

    	$user_id = $this->session->getParameter("_user_id");
    	if (!empty($user_id)) {
	    	$this->room_arr[0][0][0] = array(
	    		"page_id" => CALENDAR_ALL_MEMBERS_ID,
	    		"parent_id" => 0,
	    		"page_name" => CALENDAR_ALL_MEMBERS_LANG,
	    		"thread_num" => 0,
	    		"space_type" => _SPACE_TYPE_UNDEFINED,
	    		"private_flag" => _OFF,
	    		"authority_id" => $this->session->getParameter("_user_auth_id")
	    	);
    	}

		$timezoneMain =& $this->commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");

    	$this->calendar_obj = array(
    		"calendar_id" => 0,
    		"room_id" => ($this->plan_room_id > 0 ? $this->plan_room_id : $this->room_id),
    		"title" => $this->title,
    		"title_icon" => $this->title_icon,
    		"allday_flag" => $this->allday_flag,
    		"start_date" => date("Ymd", $current_timestamp),
    		"input_start_date" => date(_INPUT_DATE_FORMAT, $current_timestamp),
    		"start_time" => $this->start_time,
    		"end_date" => date("Ymd", $current_timestamp),
    		"input_end_date" => date(_INPUT_DATE_FORMAT, $current_timestamp),
    		"end_time" => $this->end_time,
    		"timezone_offset" => $this->session->getParameter("_timezone_offset"),
    		"timezone_offset_key" => $timezoneMain->getLangTimeZone($this->session->getParameter("_timezone_offset"), false),
    		"location" => "",
    		"contact" => "",
    		"description" => "",
    		"rrule_arr" => $this->calendarView->parseRRule("", true)
    	);
		if ($this->details_flag == _ON) {
			$this->edit_rrule = CALENDAR_PLAN_EDIT_ALL;
		}

		$this->timezone_list = explode("|", CALENDAR_DEF_TIMEZONE);
		$this->block = $this->calendarView->getBlock();

		return 'success';
    }
}
?>
