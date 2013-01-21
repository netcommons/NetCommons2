<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 参加ルーム選択画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Edit_Selectroom extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;

	// Filterによりセット
	var $room_arr =null; 

	// 使用コンポーネントを受け取るため
	var $calendarView = null;
	var $session = null;

	// 値をセットするため
	var $not_enroll_room_arr = array();
	var $enroll_room_arr = array();
	var $calendar_block = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
    	$this->calendar_block = $this->calendarView->getBlock();
    	if ($this->calendar_block === false) {
    		return 'error';
    	}
    	$sess_myroom_flag = $this->session->getParameter(array("calendar", "myroom_flag", $this->block_id));
		if (isset($sess_myroom_flag)) {
			$this->calendar_block["myroom_flag"] = intval($sess_myroom_flag);
		}

    	$result = $this->calendarView->getSelectRoomList();
    	if ($result === false) {
    		return 'error';
    	}
		$this->enroll_room_arr = $result["enroll_room_arr"];
		$this->not_enroll_room_arr = $result["not_enroll_room_arr"];
		
		return 'success';
	}
}
?>