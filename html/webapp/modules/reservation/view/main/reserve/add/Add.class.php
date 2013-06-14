<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予約追加の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Main_Reserve_Add extends Action
{
    // リクエストパラメータを受け取るため
	var $details_flag = null;
	var $location_id = null;
	var $module_id = null;
	var $entry_calendar = null;
	var $notification_mail = null;
	var $category_id = null;

	// validatorから受け取るため
	var $location = null;
	var $allow_add_rooms = null;
	var $category_list = null;
	var $location_list = null;
	var $location_count = null;
	var $location_count_list = null;
	var $reserve_block = null;
	var $timeframe_list = null;
	var $timeframe_list_count = null;
	var $start_timeframe_id = null;
	var $end_timeframe_id = null;

	// Filterから受け取るため
	var $room_arr = null;

    // 使用コンポーネントを受け取るため
	var $reservationView = null;
	var $configView = null;

    // 値をセットするため
	var $reserve = null;
	var $edit_rrule = null;
	var $reserve_room_id = null;
	var $week_list = null;
	var $timezone_list = null;
	var $mail_send = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->entry_calendar = intval($this->entry_calendar);
    	$this->notification_mail = intval($this->notification_mail);

    	$this->reserve = $this->reservationView->getAddReserve();
		$this->details_flag = intval($this->details_flag);
		if ($this->details_flag == _ON) {
			$this->edit_rrule = RESERVATION_RESERVE_EDIT_ALL;
		}
		$this->reserve_room_id = $this->reserve["room_id"];

    	$this->timezone_list = explode("|", RESERVATION_DEF_TIMEZONE);
		$this->week_list = $this->reservationView->getLocationWeekArray();

		$config = $this->configView->getConfigByConfname($this->module_id, "mail_send");
		if ($config === false) {
    		return 'error';
    	}
    	if (defined($config["conf_value"])) {
    		$this->mail_send = constant($config["conf_value"]);
    	} else {
    		$this->mail_send = intval($config["conf_value"]);
    	}
		
		return 'success';
    }
}
?>
