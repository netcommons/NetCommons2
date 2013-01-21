<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限設定の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Edit_Auth extends Action
{
	// Filterによりセット
	var $room_arr = null;

    // 使用コンポーネントを受け取るため
	var $session = null;

	// validatorから受け取るため
	var $allow_plan_flag = null;
	var $manage_list = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$_user_auth_id = $this->session->getParameter("_user_auth_id");
    	if ($_user_auth_id == _AUTH_ADMIN) {
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
       	return 'success';
    }
}
?>