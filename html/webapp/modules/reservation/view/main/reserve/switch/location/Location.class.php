<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設の切替
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Main_Reserve_Switch_Location extends Action
{
	// リクエストパラメータを受け取るため
	var $reserve_room_id = null;

	// validatorから受け取るため
	var $location = null;
	var $allow_add_rooms = null;

	// Filterから受け取るため
	var $room_arr = null;

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