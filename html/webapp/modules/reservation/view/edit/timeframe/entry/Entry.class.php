<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 時間枠の編集
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Edit_Timeframe_Entry extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $module_id = null;
	var $timeframe_id = null;

	// 使用コンポーネントを受け取るため
	var $configView = null;
	var $reservationView = null;

	// 値をセットするため
	var $timeframe = null;
	var $timeframe_colors = null;
	var $timezone_list = null;
	var $week_list = null;

	/**
	 * execute処理
	 *
	 * @access  public
	 */
	function execute()
	{
		// configから色の基本定義を取得
		$config = $this->configView->getConfigByConfname($this->module_id, 'timeframe_color');
		if($config) {
			$this->timeframe_colors = explode('|', $config['conf_value']);
		}

		$this->timezone_list = explode("|", RESERVATION_DEF_TIMEZONE);

		$this->week_list = $this->reservationView->getLocationWeekArray();

		return 'success';
	}
}