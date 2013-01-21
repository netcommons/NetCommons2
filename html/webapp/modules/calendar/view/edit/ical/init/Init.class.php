<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * iCalendarの取込
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Edit_Ical_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;

    // 使用コンポーネントを受け取るため
	var $calendarView = null;
	var $session = null;

    // 値をセットするため
	var $allow_plan_flag = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->allow_plan_flag = $this->calendarView->getAllowPlanList();
    	$user_id = $this->session->getParameter("_user_id");
    	if (!empty($user_id)) {
	    	$this->room_arr[0][0][0] = array(
	    		"page_id" => CALENDAR_ALL_MEMBERS_ID,
	    		"parent_id" => 0,
	    		"page_name" => CALENDAR_ALL_MEMBERS_LANG,
	    		"thread_num" => 0,
	    		"space_type" => _SPACE_TYPE_UNDEFINED,
	    		"private_flag" => _OFF,
	    		"authority_id" => $this->session->getParameter("_user_auth_id")
	    	);
    	}
    	$this->plan_room_id = $this->room_id;
       	return 'success';
    }
}
?>