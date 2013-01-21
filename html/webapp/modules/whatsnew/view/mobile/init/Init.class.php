<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_View_Mobile_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $module_id = null;
	var $result_only = null;
	var $limit = null;
	var $offset = null;
	var $limited_room_id = null;
	var $limited_module_id = null;

	// 使用コンポーネントを受け取るため
	var $whatsnewView = null;
	var $mobileView = null;
	var $session = null;
	var $getdata = null;

	// Filterによりセット
	var $room_arr_flat =null;

	// 値をセットするため
	var $whatsnew_obj = null;
	var $whatsnew_data = null;
	var $prev_offset = null;
	var $next_offset = null;
	var $block_num = null;
	var $whatsnew_total_count = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$mobile_modules = $this->getdata->getParameter('mobile_modules');
		$mobile_modules_arr = array();
		foreach ($mobile_modules as $display_position=>$mmodules) {
			foreach ($mmodules as $dir_name=>$mmodule) {
				$mobile_modules_arr[] = $mmodule['module_id'];
			}
		}

		$display_modules = '';
		foreach (explode(',',$this->whatsnew_obj['display_modules']) as $module) {
			if(in_array($module,$mobile_modules_arr)) {
				if($this->limited_module_id!=null) {
					if($module==$this->limited_module_id) {
						$display_modules .= ','.$module;
					}
				} else {
					$display_modules .= ','.$module;
				}
			}
		}
		$this->whatsnew_obj['display_modules'] = substr($display_modules, 1);

		if($this->whatsnew_obj['display_type'] == WHATSNEW_DEF_FLAT) {
			if($this->whatsnew_obj['display_flag'] == _OFF || $this->whatsnew_obj['display_number'] > WHATSNEW_MOBILE_LIMIT) {
				$this->limit = WHATSNEW_MOBILE_LIMIT;
			} else {
				$this->limit = $this->whatsnew_obj['display_number'];
			}
			$this->offset = intval($this->offset);

			$this->prev_offset = ($this->offset-$this->limit < 0 ? 0 : $this->offset-$this->limit);
			$this->next_offset = $this->offset + $this->limit;
			$this->whatsnew_data = $this->whatsnewView->getResults($this->whatsnew_obj, $this->room_arr_flat, $this->limit, $this->offset);
		} else {
			$limited_room = array();
			if($this->limited_room_id!==null) {
				$limited_room[$this->limited_room_id] = $this->room_arr_flat[$this->limited_room_id];
			} else {
				$limited_room = $this->room_arr_flat;
			}

			$this->limit = WHATSNEW_MOBILE_LIMIT_LITTLE+1;	// 続きがあるかを確認するために+1
			$this->offset = intval($this->offset);
			$this->prev_offset = ($this->offset-WHATSNEW_MOBILE_LIMIT_LITTLE < 0 ? 0 : $this->offset-WHATSNEW_MOBILE_LIMIT_LITTLE);
			$this->next_offset = $this->offset + WHATSNEW_MOBILE_LIMIT_LITTLE;
			$this->whatsnew_data = $this->whatsnewView->getResults($this->whatsnew_obj, $limited_room, $this->limit, $this->offset);
		}

		if($this->whatsnew_obj['display_flag'] == _ON) {
			$this->whatsnew_total_count = $this->whatsnew_obj['display_number'];
		} else {
			$this->whatsnew_total_count = $this->session->getParameter('whatsnew_total');
		}

		$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock($this->block_id);

		$user_id = $this->session->getParameter('_user_id');
		if (!empty($user_id)) {
			$this->room_arr_flat['0'] = array('page_name' => WHATSNEW_NO_PAGE);
		}
		if ($this->whatsnew_data === false) {
			return 'error';
		}

		return 'success';
	}
}
?>
