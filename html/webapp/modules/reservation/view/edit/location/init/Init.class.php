<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設一覧の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Edit_Location_Init extends Action
{
	// validatorから受け取るため
	var $category_list = null;
	var $location_list = null;

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