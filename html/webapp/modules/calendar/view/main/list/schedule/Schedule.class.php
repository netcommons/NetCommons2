<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * スケジュールの予定データ取得
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Main_List_Schedule extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id_arr = null;
	var $room_arr = null;
	var $display_type = null;

    // 使用コンポーネントを受け取るため
	var $calendarView = null;

    // 値をセットするため
    var $calendar_block = null;
    var $today = null;
	var $plan_data = null;
	var $count_data = null;
	var $current_timestamp = null;
	var $start_timestamp = null;
	var $end_timestamp = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->current_timestamp = mktime(0,0,0,substr($this->today,4,2),substr($this->today,6,2),substr($this->today,0,4));
		if ($this->calendar_block["start_pos_weekly"] == CALENDAR_START_YESTERDAY) {
			$this->start_timestamp = $this->current_timestamp - 1 * 86400;
		} else {
			$this->start_timestamp = $this->current_timestamp;
		}
		$this->end_timestamp = $this->start_timestamp + $this->calendar_block["display_count"] * 86400;

		$start_date = date("Ymd",$this->start_timestamp);
		$end_date = date("Ymd",$this->end_timestamp);
		$this->plan_data = $this->calendarView->getPlanByDate($start_date, $end_date, $this->calendar_block["display_type"]);
		if ($this->calendar_block["display_type"] == CALENDAR_T_SCHEDULE) {
			$success = 'successTime';
		} else {
			$success = 'successUser';
		}
    	if ($this->plan_data === false) {
    		return 'error';
    	}
		return $success;
    }
}
?>
