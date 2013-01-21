<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予約の詳細の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Main_Reserve_Details extends Action
{
	// validatorから受け取るため
	var $reserve = null;
	var $rrule_reserve_id = null;

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
