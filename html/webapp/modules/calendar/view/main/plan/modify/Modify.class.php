<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定の編集の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Main_Plan_Modify extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $calendar_id = null;
	var $display_type = null;
	var $edit_rrule = null;

    // 使用コンポーネントを受け取るため
	var $calendarView = null;
	var $session = null;

	// Filterから受け取るため
	var $room_arr = null;

  	// validatorから受け取るため
	var $calendar_obj = null;
	var $calendar_block = null;
	var $allow_plan_flag = null;
	var $manage_list = null;

    // 値をセットするため
	var $details_flag = null;
	var $timezone_list = null;
	var $block = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
    	$this->display_type = intval($this->display_type);
    	$this->edit_rrule = intval($this->edit_rrule);

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

		if ($mobile_flag == _ON) {
			if ($this->calendar_obj["rrule"] == "") {
				$this->edit_rrule = CALENDAR_PLAN_EDIT_ALL;
			} else {
				$this->edit_rrule = CALENDAR_PLAN_EDIT_THIS;
			}
		}
    	$this->details_flag = _ON;

		$this->timezone_list = explode("|", CALENDAR_DEF_TIMEZONE);
		$this->block = $this->calendarView->getBlock();

        return 'success';
    }
}
?>
