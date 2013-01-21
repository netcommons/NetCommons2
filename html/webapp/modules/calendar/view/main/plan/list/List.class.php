<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ポップアップ予定の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Main_Plan_List extends Action
{
    // パラメータを受け取るため
	var $block_id = null;
	var $date = null;
	var $room_id_arr = null;
	var $calendar_block = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
	var $calendarView = null;
	var $holidayView = null;

    // 値をセットするため
	var $holidays = null;
	var $current_timestamp = null;
	var $plan_data = null;

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
		$this->current_timestamp = mktime(0,0,0,substr($this->date,4,2),substr($this->date,6,2),substr($this->date,0,4));
		$this->holidays = $this->holidayView->get($this->date);
    	if ($this->holidays === false) {
    		return 'error';
    	}
		$this->plan_data = $this->calendarView->getPlanByDate($this->date);
    	if ($this->plan_data === false) {
    		return 'error';
    	}
        return 'success';
    }
}
?>
