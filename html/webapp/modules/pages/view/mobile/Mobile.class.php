<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページ表示クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Pages_View_Mobile extends Action
{
	// 使用コンポーネントを受け取るため
	var $configView = null;
	var $getdata = null;
	var $preexecute = null;
	var $session = null;

	//リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;
	var $module_id = null;

	/**
	 * execute処理
	 *
	 * @access  public
	 */
	function execute()
	{
		$default_module = $this->session->getParameter("_mobile_default_module");
		$mobile_modules = $this->getdata->getParameter("mobile_modules");
		$blocks = $this->getdata->getParameter("blocks");

		if ($this->block_id && isset($blocks[$this->block_id])) {
			$module_id = $blocks[$this->block_id]["module_id"];
			$params = array(
				"page_id" => $this->page_id,
				"room_id" => $this->room_id,
				"block_id" => $this->block_id,
				"module_id" => $module_id,
				"_output" => _OFF
			);
			$this->result = $this->preexecute->preExecute($blocks[$this->block_id]["action_name"], $params);
		} elseif (isset($mobile_modules[_DISPLAY_POSITION_HEADER][$default_module])) {
			if( $this->page_id != false && $this->block_id == false && strpos( $_SERVER['REQUEST_URI'], "page_id=" )) {
				return 'havepage';
			}
			$module_id = $mobile_modules[_DISPLAY_POSITION_HEADER][$default_module]["module_id"];
			$params = array(
				"page_id" => $this->page_id,
				"room_id" => $this->room_id,
				"module_id" => $module_id,
				"_output" => _OFF
			);
			$this->result = $this->preexecute->preExecute($mobile_modules[_DISPLAY_POSITION_HEADER][$default_module]["mobile_action_name"], $params);
		} else {
			$module_id = 0;
			$this->result = "";
		}
		$this->session->setParameter("_mobile_page_id", $this->page_id);
		$this->session->setParameter("_mobile_room_id", $this->room_id);
		$this->session->setParameter("_mobile_module_id", $module_id);
		return 'success';
	}
}
?>