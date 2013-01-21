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
class Calendar_View_Main_List_Daily extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id_arr = null;
	var $room_arr = null;
	var $date = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
	var $calendarView = null;

    // 値をセットするため
    var $calendar_block = null;
    var $today = null;
	var $plan_data = null;
	var $current_timestamp = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		if (empty($this->date) || !preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})$/", $this->date, $matches) || !checkdate($matches[2], $matches[3], $matches[1])) {
			$this->date = timezone_date(null, false, "Ymd");
		}
		$this->today = timezone_date(null, false, "Ymd");
		$this->current_timestamp = mktime(0,0,0,substr($this->date,4,2),substr($this->date,6,2),substr($this->date,0,4));
		$this->plan_data = $this->calendarView->getPlanByDate($this->date, $this->date, CALENDAR_DAILY);
    	if ($this->plan_data === false) {
    		return 'error';
    	}
		return 'success';
    }
}
?>
