<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Action_Edit_Addblock extends Action
{
    // 使用コンポーネントを受け取るため
	var $reservationAction = null;
	var $reservationView = null;
	var $session = null;

	// validatorから受け取るため
	var $category_count = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	if ($this->category_count == 0) {
	    	$result = $this->reservationAction->setCategory();
	    	if ($result === false) {
	    		return 'error';
	    	}
    	}
    	$result = $this->reservationAction->setBlock();
    	if ($result === false) {
    		return 'error';
    	}
    	
    	$location_count = $this->reservationView->getCountLocation();
    	if ($location_count == 0 && $this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
			return 'addLocation';
    	} else {
			return 'success';
    	}
    }
}
?>