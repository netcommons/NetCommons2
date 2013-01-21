<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * iCalendarの取込
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Edit_Ical_Import extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $plan_room_id = null;
    var $import_indexs = null;
    var $all_delete_flg = null;


    // 使用コンポーネントを受け取るため
	var $calendarPlanAction = null;
	var $session = null;
	var $calendarAction = null;

	// Action内の変数
	var $_timezone_offset = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		if($this->all_delete_flg == _ON){
			$result = $this->calendarAction->deletePlanAll($this->plan_room_id);
			if(!$result){ return 'error'; }
        }

		$vevents = $this->session->getParameter(array("calendar", "ical", "vevents", $this->block_id));
		$vtimezone = $this->session->getParameter(array("calendar", "ical", "vtimezone", $this->block_id));

		$this->_timezone_offset = $vtimezone["STANDARD"]["TZOFFSETFROM"];
		foreach ($this->import_indexs as $i=>$index) {
			$result = $this->_insertCalendar($vevents[$index]);
			if (!$result) { return 'error'; }
		}
		$this->session->removeParameter(array("calendar", "ical", "vcalendar", $this->block_id));
		$this->session->removeParameter(array("calendar", "ical", "vtimezone", $this->block_id));
		$this->session->removeParameter(array("calendar", "ical", "vevents", $this->block_id));
        return 'success';
    }

    function _insertCalendar($vevent)
    {
    	if (isset($vevent["RRULE"])) {
    		$rrule_str = $vevent["RRULE"];
			$rrule_arr = $this->calendarPlanAction->parseRRule($rrule_str);
			if ($rrule_arr["FREQ"] != "NONE" && !isset($rrule_arr["INTERVAL"])) {
				$rrule_arr["INTERVAL"] = 1;
			}
			if ($rrule_arr["FREQ"] == "YEARLY" && empty($rrule_arr["BYMONTH"])) {
				$rrule_arr["BYMONTH"] = array(substr($vevent["DTSTART"], 4, 2));
			}
			if (!empty($rrule_arr["UNTIL"])) {
				$rrule_arr["UNTIL"] = $this->calendarPlanAction->dateFormat(substr($rrule_arr["UNTIL"],0,8)."240000", $this->_timezone_offset, true, "Ymd")."T".
										 $this->calendarPlanAction->dateFormat(substr($rrule_arr["UNTIL"],0,8)."240000", $this->_timezone_offset, true, "His");
			}

			$rrule_str = $this->calendarPlanAction->concatRRule($rrule_arr);
    	} else {
			$rrule_str = "";
			$rrule_arr = array();
    	}
    	$plan_params = array(
			"room_id" => $this->plan_room_id,
			"title" => $vevent["SUMMARY"],
			"title_icon" => "",
			"allday_flag" => (isset($vevent["ALLDAY"]) ? $vevent["ALLDAY"] : _OFF),
			"start_time_full" => $vevent["DTSTART"],
			"end_time_full" => $vevent["DTEND"],
			"timezone_offset" => $this->_timezone_offset,
			"location" => (isset($vevent["LOCATION"]) ? $vevent["LOCATION"] : ""),
			"contact" => (isset($vevent["CONTACT"]) ? $vevent["CONTACT"] : ""),
			"description" => (isset($vevent["DESCRIPTION"]) ? $vevent["DESCRIPTION"] : ""),
			"rrule" => $rrule_str
		);
		$result = $this->calendarPlanAction->insertPlan($plan_params);
		if ($result === false) {
			return false;
		}
    	return true;
    }
}
?>