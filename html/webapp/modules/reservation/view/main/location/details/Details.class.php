<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Main_Location_Details extends Action
{
	// validatorから受け取るため
	var $location = null;
	var $select_rooms = null;

	// Filterから受け取るため
	var $room_arr = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
       	return 'success';
    }
}
?>
