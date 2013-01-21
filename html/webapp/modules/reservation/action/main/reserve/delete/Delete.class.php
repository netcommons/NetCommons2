<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定の削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Action_Main_Reserve_Delete extends Action
{
    // 使用コンポーネントを受け取るため
	var $reservationAction = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$result = $this->reservationAction->deleteReserve();
		if ($result == false) {
			 return 'error';
		}
        return 'success';
    }
}
?>