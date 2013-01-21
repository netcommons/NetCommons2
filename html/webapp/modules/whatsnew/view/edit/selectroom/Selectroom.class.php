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
class Whatsnew_View_Edit_Selectroom extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;

	// Filterによりセット
	var $room_arr =null; 

	// 使用コンポーネントを受け取るため
	var $pagesView = null;
	var $whatsnewView = null;
	var $getdata = null;
	var $session = null;

	// 値をセットするため
	var $not_enroll_room_arr = array();
	var $enroll_room_arr = array();
	var $whatsnew_obj = null;
	var $_page = null;

	// プライベート変数
	var $_sess_enroll_room = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
    	$this->whatsnew_obj = $this->whatsnewView->getBlock($this->block_id);
    	if (!$this->whatsnew_obj) {
    		return 'error';
    	}
		$sess_myroom_flag = $this->session->getParameter(array("whatsnew", "myroom_flag", $this->block_id));

		if (isset($sess_myroom_flag)) {
			$this->whatsnew_obj["myroom_flag"] = intval($sess_myroom_flag);
		}
		$this->_sess_enroll_room = $this->session->getParameter(array("whatsnew", "enroll_room", $this->block_id));

		$thread_num = 0;
		$parent_id = 0;

		foreach ($this->room_arr[$thread_num][$parent_id] as $disp => $room) {
			if ($room["space_type"] == _SPACE_TYPE_GROUP && $room["private_flag"] == _ON) {
				continue;
			}

			if (!isset($this->_sess_enroll_room) && in_array($room["page_id"], $this->whatsnew_obj["select_room_list"]) || 
				!empty($this->_sess_enroll_room) && in_array($room["page_id"], $this->_sess_enroll_room)) {
				
				$this->enroll_room_arr[] = $room;
			} else {
				$this->not_enroll_room_arr[] = $room;
			}
			$this->_makeRoomArray(1, $room["page_id"], $room);
		}

		return 'success';
	}

	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function _makeRoomArray($thread_num, $parent_id, &$parent_room) 
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
			if (!isset($this->_sess_enroll_room) && in_array($room["page_id"], $this->whatsnew_obj["select_room_list"]) || 
				!empty($this->_sess_enroll_room) && in_array($room["page_id"], $this->_sess_enroll_room)) {
				$this->enroll_room_arr[] = $room;
			} else {
				$this->not_enroll_room_arr[] = $room;
			}
			$this->_makeRoomArray($next_thread_num, $room["page_id"], $room);
		}

		return true;
	}
	
}
?>