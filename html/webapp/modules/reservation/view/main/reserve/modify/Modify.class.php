<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定の編集の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Main_Reserve_Modify extends Action
{
    // リクエストパラメータを受け取るため
	var $module_id = null;

	// validatorから受け取るため
	var $reserve = null;
	var $location = null;
	var $allow_add_rooms = null;
	var $category_list = null;
	var $reserve_block = null;
	var $location_id = null;
	var $location_list = null;
	var $location_count = null;
	var $location_count_list = null;
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
	var $edit_rrule = null;
	var $reserve_room_id = null;
	var $week_list = null;
	var $timezone_list = null;
	var $mail_send = null;
	var $entry_calendar = _OFF;
	var $notification_mail = _OFF;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->reserve_room_id = $this->reserve["room_id"];

		$this->week_list = $this->reservationView->getLocationWeekArray();

    	$this->timezone_list = explode("|", RESERVATION_DEF_TIMEZONE);

    	$this->edit_rrule = intval($this->edit_rrule);
    	$this->details_flag = _ON;

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
