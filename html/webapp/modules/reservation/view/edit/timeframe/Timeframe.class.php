<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 時間枠設定の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Edit_Timeframe extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;

	// 使用コンポーネントを受け取るため
	var $configView = null;

	// 値をセットするため
	var $timeframe_list = null;
	var $timeframe_list_count = null;

	/**
	 * execute処理
	 *
	 * @access  public
	 */
	function execute()
	{
		return 'success';
	}
}