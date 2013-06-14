<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設予約の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $view_date = null;
	var $current_time = null;

    // 使用コンポーネントを受け取るため
	var $session = null;
	var $reservationView = null;

	// Filterから受け取るため
	var $room_id_arr = null;

	// validatorから受け取るため
	var $location_count = null;
	var $reserve_block = null;
	var $location_list = null;
	var $category_list = null;
	var $holiday_list = null;
    var $reserve_data = null;
    var $week_list = null;
	var $location_count_list = null;

	var $current_timestamp = null;
	var $start_timestamp = null;
	var $end_timestamp = null;

	var $today = null;
	var $this_month = null;
	var $next_month = null;
	var $prev_month = null;
	var $next_week = null;
	var $prev_week = null;
	var $next_day = null;
	var $prev_day = null;
	var $input_date = null;

	var $timeframe_list = null;
	var $timeframe_list_count = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$_id = $this->session->getParameter("_id");
    	$this->session->setParameter(array("reservation", "_id", $this->block_id), $_id);
		$this->week_list = $this->reservationView->getWeekArray();

		if ($this->location_count == 0) {
			return 'noLocation';
		}

		switch ($this->reserve_block["display_type"]) {
			case RESERVATION_DEF_MONTHLY:
		        return 'successMonthly';

			case RESERVATION_DEF_WEEKLY:
				$this->frame_height = $this->reserve_block["display_interval"] * RESERVATION_DEF_H_INTERVAL + 10;
		        return 'successWeekly';

			case RESERVATION_DEF_LOCATION:
				$this->frame_width = $this->reserve_block["display_interval"] * RESERVATION_DEF_V_INTERVAL + 10;
				$this->rowspan = count($this->location_list) + 2;
		        return 'successLocation';

			default:
				return 'error';
		}
    }
}
?>
