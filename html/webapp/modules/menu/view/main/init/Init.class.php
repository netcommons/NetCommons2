<?php
/**
 * メニューモジュール
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_View_Main_Init extends Action
{	
	// リクエストパラメータを受け取るため
	var $block_id = null;
	
	// 使用コンポーネントを受け取るため
	var $pagesView=null;
	//var $request = null;
	var $getdata = null;
	var $session = null;
	var $menuView = null;
	
	// 値をセットするため
	var $main_page_id = null;
	var $menus = null;
	var $main_active_node_arr = array();
	var $top_page_arr = null;
	
	// 主担でなくても編集できるようにする
	var $headerbtn_edit = false;
	
	function execute()
	{
		$this->main_page_id = intval($this->session->getParameter("_main_page_id"));
		$pages = $this->getdata->getParameter("pages");
		if(!isset($pages[$this->main_page_id])) {
			$pages[$this->main_page_id] = $this->pagesView->getPageById($this->main_page_id);
		}
		$main_page_id = $this->main_page_id;
		$main_root_id = $pages[$this->main_page_id]['root_id'];
		$main_parent_id = $pages[$this->main_page_id]['parent_id'];
		$main_room_id = $pages[$this->main_page_id]['room_id'];
		$main_space_type = $pages[$this->main_page_id]['space_type'];
		$this->top_page_arr = null;
		$func_param = array($main_page_id, $main_root_id, $main_parent_id, $main_room_id, $main_space_type, &$this->main_active_node_arr, &$this->top_page_arr, &$this->headerbtn_edit,"init");
		$func = array($this->menuView, 'fetchcallback');
		$this->menus =& $this->menuView->getShowPageById($this->block_id, $this->main_page_id, $main_root_id, $main_parent_id, $pages[$this->main_page_id]['room_id'], null, $func, $func_param);

		return 'success';
	}
}
?>
