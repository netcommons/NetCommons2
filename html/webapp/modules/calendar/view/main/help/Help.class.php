<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ヘルプの表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Main_Help extends Action
{
 	// Filterから受け取るため
	var $room_arr = null;

    // 使用コンポーネントを受け取るため
	var $calendarView = null;
	var $session = null;

 	// validatorから受け取るため
	var $enroll_room_arr = null;
	var $calendar_block = null;

    // 値をセットするため
	var $allow_plan_flag = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->allow_plan_flag = $this->calendarView->getAllowPlanList();

        return 'success';
    }
}
?>
