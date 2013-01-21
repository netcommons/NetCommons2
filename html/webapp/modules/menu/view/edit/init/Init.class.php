<?php
/**
 * メニュー編集画面表示クラス
 * 編集画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_View_Edit_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $pagesView=null;
	var $request = null;
	var $getdata = null;
	var $menuView = null;
	var $session = null;


	// 値をセットするため
	var $main_page_id = null;
	var $main_root_id = null;
	var $main_active_node_arr = array();
	var $menus=array();
	// 主担でなくても編集できるようにする
	var $headerbtn_edit = false;

	var $flat_flag = false;

	function execute()
	{
		$this->main_page_id = intval($this->session->getParameter("_main_page_id"));
		$pages = $this->getdata->getParameter("pages");
		if(!isset($pages_obj[$this->main_page_id])) {
			$pages[$this->main_page_id] =$this->pagesView->getPageById($this->main_page_id);
		}
		$blocks =& $this->getdata->getParameter("blocks");
		$temp_name = $blocks[$this->block_id]['temp_name'];
		if(preg_match("/(flat|header)/i",$temp_name)) {
			$this->flat_flag = true;
		}
		$main_page_id = $this->main_page_id;
		$main_root_id = $pages[$this->main_page_id]['root_id'];
		$main_parent_id = $pages[$this->main_page_id]['parent_id'];
		$main_room_id = $pages[$this->main_page_id]['room_id'];
		$main_space_type = $pages[$this->main_page_id]['space_type'];
		$func_param = array($main_page_id, $main_root_id, $main_parent_id, $main_room_id, $main_space_type, &$this->main_active_node_arr, null, &$this->headerbtn_edit,"edit");
		$func = array($this->menuView, 'fetchcallback');
		$this->menus =& $this->menuView->getShowPageById($this->block_id, $this->main_page_id, $main_root_id, $main_parent_id, $pages[$this->main_page_id]['room_id'], null, $func, $func_param);

		return 'success';
	}
}
?>