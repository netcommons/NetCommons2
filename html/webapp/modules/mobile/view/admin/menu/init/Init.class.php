<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  携帯メニュー編集画面表示クラス 編集画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_View_Admin_Menu_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $languages = null;
	
	// 使用コンポーネントを受け取るため
	var $pagesView=null;
	var $request = null;
	var $getdata = null;
	var $menuView = null;
	var $session = null;
	var $configView = null;


	// 値をセットするため
	var $module_id = null;
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

		//
		// 携帯メニュー表示各種設定値を得る
		//
		$conf = $this->configView->getConfig( $this->module_id, false );
		if( $conf === false ) {
			return false;
		}
		$this->mobile_menu_each_room = $conf['mobile_menu_each_room']['conf_value'];
		$this->mobile_menu_display_type = $conf['mobile_menu_type']['conf_value'];
		if( $this->mobile_menu_display_type == MOBILE_MENU_DISPLAY_TREE ) {
			$this->flat_flag = false;
		}


		$main_page_id = $this->main_page_id;
		$main_root_id = $pages[$this->main_page_id]['root_id'];
		$main_parent_id = $pages[$this->main_page_id]['parent_id'];
		$main_room_id = $pages[$this->main_page_id]['room_id'];
		$main_space_type = $pages[$this->main_page_id]['space_type'];

		$func_param = array($main_page_id, $main_root_id, $main_parent_id, $main_room_id, $main_space_type, &$this->main_active_node_arr, null, &$this->headerbtn_edit, $this->mobile_menu_display_type);

		$func = array($this->menuView, 'fetchcallback');

		$this->menus =& $this->menuView->getShowPageById(0, 0, 0, 0, 0, null, $func, $func_param);
		
		return 'success';
	}
}
?>
