<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  携帯設定新着設定変更アクション
 *
 * @package     NetCommons
 * @author      Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2010 AllCreator Co., Ltd.
 * @project     NC Support Project, provided by AllCreator Co., Ltd.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */

class Mobile_Action_Admin_Whatsnew extends Action
{
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;

	// リクエストパラメータを受け取るため
	var $display_type = null;
	var $select_room_flag = null;
	var $select_room = null;
	var $display_flag = null;
	var $display_days = null;
	var $display_number = null;
	var $display_modules = null;

	function execute()
	{
		$ret = $this->_update(MOBILE_WHATSNEW_DISPLAY_TYPE, $this->display_type);
		if ($ret === false) {
			return 'error';
		}
		$ret = $this->_update(MOBILE_WHATSNEW_DISPLAY_FLAG, $this->display_flag);
		if ($ret === false) {
			return 'error';
		}
		if($this->display_flag==_ON) {
			$ret = $this->_update(MOBILE_WHATSNEW_DISPLAY_NUMBER, $this->display_number);
			if ($ret === false) {
				return 'error';
			}
		} else {
			$ret = $this->_update(MOBILE_WHATSNEW_DISPLAY_DAYS, $this->display_days);
			if ($ret === false) {
				return 'error';
			}
		}
		$ret = $this->_update(MOBILE_WHATSNEW_SELECT_ROOM_FLAG, $this->select_room_flag);
		if ($ret === false) {
			return 'error';
		}
		if($this->select_room_flag == _ON) {
			$enroll_room = $this->session->getParameter(array('mobile', 'mobile_whatsnew_enroll_room'));
			if(is_array($enroll_room)) {
				$ret = $this->_update(MOBILE_WHATSNEW_SELECT_ROOM, implode(',',$this->session->getParameter(array('mobile', 'mobile_whatsnew_enroll_room'))));
			} else {
				$ret = $this->_update(MOBILE_WHATSNEW_SELECT_ROOM,'');
			}
			if ($ret === false) {
				return 'error';
			}
			$myroom_flag = $this->session->getParameter(array('mobile', 'mobile_whatsnew_select_myroom'));
			if(!isset($myroom_flag)) {
				$myroom_flag = _OFF;
			}
			$ret = $this->_update(MOBILE_WHATSNEW_SELECT_MYROOM, strval($myroom_flag));
			if ($ret === false) {
				return 'error';
			}
		} else {
			$ret = $this->_update(MOBILE_WHATSNEW_SELECT_ROOM, '');
			if ($ret === false) {
				return 'error';
			}
			$ret = $this->_update(MOBILE_WHATSNEW_SELECT_MYROOM, strval(_OFF));
			if ($ret === false) {
				return 'error';
			}
		}
		$ret = $this->_update(MOBILE_WHATSNEW_SELECT_MODULE, implode(',',$this->display_modules));
		if ($ret === false) {
			return 'error';
		}
		return 'success';
	}
	function _update($name, $value)
	{
		$params = array('conf_value'=>$value);
		$where_params = array('conf_name'=>$name);
		$ret = $this->db->updateExecute('config', $params, $where_params, true);
		if($ret === false) {
			return false;
		}
		return true;
	}
}
?>