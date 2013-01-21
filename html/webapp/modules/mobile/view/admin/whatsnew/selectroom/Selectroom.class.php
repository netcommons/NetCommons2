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
class Mobile_View_Admin_Whatsnew_Selectroom extends Action
{
    // リクエストパラメータを受け取るため
	var $module_id = null;

	// Filterによりセット
	var $room_arr =null;

	// 使用コンポーネントを受け取るため
	var $configView = null;
	var $session = null;

	// 値をセットするため
	var $not_enroll_room_arr = array();
	var $enroll_room_arr = array();
	var $config = null;
	var $_page = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->config = $this->configView->getConfig($this->module_id, false);
		if( $this->config == false ) {
			return 'error';
		}
		if($this->config[MOBILE_WHATSNEW_SELECT_ROOM]['conf_value']!=_OFF) {
			$select_room_list = explode(',',$this->config[MOBILE_WHATSNEW_SELECT_ROOM]['conf_value']);
		}
		else{
			$select_room_list = array();
		}

		$sess_myroom_flag = $this->session->getParameter(array('mobile', 'mobile_whatsnew_select_myroom'));
		if (isset($sess_myroom_flag)) {
			$this->config[MOBILE_WHATSNEW_SELECT_MYROOM]['conf_value'] = intval($sess_myroom_flag);
		}

		if ( empty($select_room_list) && $this->config[MOBILE_WHATSNEW_SELECT_MYROOM]['conf_value']==_OFF ) {
			foreach ($this->room_arr[0][0] as $room) {
				if ($room['space_type'] == _SPACE_TYPE_PUBLIC) {
					$select_room_list = array($room['room_id']);
				}
			}
		}

		$sess_enroll_room = $this->session->getParameter(array('mobile', 'mobile_whatsnew_enroll_room'));
		if (empty($sess_enroll_room)) {
			$sess_enroll_room = array();
		}
		$thread_num = 0;
		$parent_id = 0;

		foreach ($this->room_arr[$thread_num][$parent_id] as $disp => $room) {
			if ($room['space_type'] == _SPACE_TYPE_GROUP && $room['private_flag'] == _ON) {
				continue;
			}

			if (!isset($sess_enroll_room) && in_array($room['page_id'], $select_room_list) ||
				!empty($sess_enroll_room) && in_array($room['page_id'], $sess_enroll_room)) {

				$this->enroll_room_arr[] = $room;
			} else {
				$this->not_enroll_room_arr[] = $room;
			}
			$this->_makeRoomArray(1, $room["page_id"], $room, $select_room_list, $sess_enroll_room);
		}
		return 'success';
	}
	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function _makeRoomArray($thread_num, $parent_id, &$parent_room, $select_room_list, $sess_enroll_room)
	{
		if (!isset($this->room_arr[$thread_num]) || !isset($this->room_arr[$thread_num][$parent_id])) {
			return true;
		}

		$next_thread_num = $thread_num + 1;
		foreach ($this->room_arr[$thread_num][$parent_id] as $disp=>$room) {
			if ($room["space_type"] == _SPACE_TYPE_GROUP && $room["private_flag"] == _ON) {
				continue;
			}
			if ($room["space_type"] != _SPACE_TYPE_GROUP || $room["space_type"] == _SPACE_TYPE_GROUP && $room["private_flag"] != _ON && $room["thread_num"] > 1) {
				$room["parent_page_name"] = $parent_room["page_name"];
			}
			if (!isset($sess_enroll_room) && in_array($room["page_id"], $select_room_list) ||
				!empty($sess_enroll_room) && in_array($room["page_id"], $sess_enroll_room)) {
				$this->enroll_room_arr[] = $room;
			} else {
				$this->not_enroll_room_arr[] = $room;
			}
			$this->_makeRoomArray($next_thread_num, $room["page_id"], $room, $select_room_list, $sess_enroll_room);
		}

		return true;
	}
}
?>