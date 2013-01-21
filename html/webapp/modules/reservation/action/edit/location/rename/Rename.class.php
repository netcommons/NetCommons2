<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設名変更
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Action_Edit_Location_Rename extends Action
{
    // リクエストパラメータを受け取るため
    var $location_name = null;

    // 使用コンポーネントを受け取るため
	var $reservationAction = null;

	// 値をセットするため
	var $rename = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->reservationAction->renameLocation();
    	if ($result === false) {
    		return 'error';
    	}
    	$this->rename = $this->location_name;
    	return 'success';
    }
}
?>
