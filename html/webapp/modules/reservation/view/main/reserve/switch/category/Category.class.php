<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリーの切替
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Main_Reserve_Switch_Category extends Action
{
    // リクエストパラメータを受け取るため
	var $location_id = null;

	// validatorから受け取るため
	var $location_list = null;
	var $location_count = null;

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
?>