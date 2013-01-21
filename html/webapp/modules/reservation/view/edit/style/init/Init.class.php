<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Edit_Style_Init extends Action
{
	// validatorから受け取るため
	var $category_list = null;
	var $location_list = null;
	var $location_count = null;
	var $reserve_block = null;
	var $location_count_list = null;

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