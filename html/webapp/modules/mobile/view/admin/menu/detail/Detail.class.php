<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  メニューモジュール
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_View_Admin_Menu_Detail extends Action
{	
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $main_page_id = null;
	var $visibility_flag = null;
	
	// 使用コンポーネントを受け取るため
	var $menuView = null;
	var $pagesView = null;
	var $configView = null;
	var	$db = null;
	
	// 値をセットするため
	var $module_id = null;
	var $parent_id = null;
	var $thread_num = null;
	var $menus = null;
	var $main_active_node_arr = array();
	var $mobile_menu_display_type = null;
	var	$space_visibility_flag = null;

	function execute()
	{
		$page_id = intval($this->main_page_id);
		$page =& $this->pagesView->getPageById($page_id);
		if(count($page) == 0) {
			return 'success';
		}
		// 2010.04.02
		$this->space_visibility_flag = _ON;

		// 指定されたページがどのスペースに属しているかを調べる
		$space_type = $page['space_type'];
		// 指定されたスペースのpage_idを調べる
		$param = array( "space_type"=>$space_type, 
						"thread_num"=>0,
						"private_flag"=>_OFF
				);
		$space_page = $this->db->selectExecute( "pages", $param );
		if( $space_page != false && is_array( $space_page ) && count( $space_page )>0 ) {	// 一つだけ見つかるはず
			// そのpage_idを持つものが携帯メニュー設定で「非表示設定」となっていないかを調べる
			$param = array( "page_id"=>$space_page[0]['page_id'] );
			$mobile_menu_detail = $this->db->selectExecute( "mobile_menu_detail", $param );
			// 非表示設定となっていたら
			if( $mobile_menu_detail != false && is_array( $mobile_menu_detail ) && count( $mobile_menu_detail )>0 ) {
				// visibility_flag = OFFとする
				$this->space_visibility_flag = _OFF;
			}
		}
		 
		$conf = $this->configView->getConfig( $this->module_id, false );
		if( $conf === false ) {
			return false;
		}
		$this->mobile_menu_display_type = $conf['mobile_menu_type']['conf_value'];

		$main_page_id = $page_id;
		$main_root_id = $page['root_id'];
		$main_parent_id = $page['parent_id'];
		$main_room_id = $page['room_id'];
		$func_param = array($main_page_id, $main_root_id, $main_parent_id, $main_room_id, &$this->main_active_node_arr,$this->mobile_menu_display_type);
		$func = array($this->menuView, 'fetchcallback');
		$this->menus =& $this->menuView->getShowPageById($this->block_id, $main_page_id, $main_root_id, $main_parent_id, $page['room_id'],  $page['thread_num']+1,$func, $func_param);
		
		$this->thread_num = $page['thread_num']+1;
		$this->parent_id = $page_id;
		
		return 'success';
	}
}
?>
