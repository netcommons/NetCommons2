<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * CSVインポート画面の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Edit_Import_Init extends Action
{
	// Filter_AllowIdListから受け取るため
	var $room_arr = null;
	var $room_id_arr = null;

	// 使用するコンポーネント
	var $reservationView = null;
	var $request = null;

	// 値をセットするため
//	var $current_time = null;
	var $category_list = array();
	var $location_list = array();
	var $location_count = 0;
//	var $reserve_block = null;
	var $location_count_list = array();
	var $location = array();
	var $allow_add_rooms = array();
	var $help_reserve_time = "";
	var $help_start_time = "";
	var $help_end_time = "";
	var $location_id = 0;

	/**
	 * execute処理
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->help_start_time = "090000";
		$this->help_end_time = "180000";
		$this->help_reserve_time = timezone_date_format(null, "Ymd");

		$this->category_list = $this->reservationView->getCategories();
		$this->location_count_list = $this->reservationView->getCountLocationByCategory();

		$this->location_list = $this->reservationView->getLocations();
		$this->location_count = count($this->location_list);
		foreach ($this->location_list as $location) {
			$this->location = $location;
			$this->location_id = $this->location["location_id"];
			$this->request->setParameter("location", $location);
			break;
		}

		$this->allow_add_rooms = $this->reservationView->getAddLocationRoom($location);

		return 'success';
	}
}
?>