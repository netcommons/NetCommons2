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
class Menu_View_Edit_Detail extends Action
{	
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $main_page_id = null;
	var $visibility_flag = null;
	var $flat_flag = null;
	
	// 使用コンポーネントを受け取るため
	var $menuView = null;
	var $pagesView = null;
	
	// 値をセットするため
	var $parent_id = null;
	var $thread_num = null;
	var $menus = null;
	var $main_active_node_arr = array();
	function execute()
	{
		$page_id = intval($this->main_page_id);
		$page =& $this->pagesView->getPageById($page_id);
		if(count($page) == 0) {
			return 'success';
		}
		
		$main_page_id = $page_id;
		$main_root_id = $page['root_id'];
		$main_parent_id = $page['parent_id'];
		$main_room_id = $page['room_id'];
		$func_param = array($main_page_id, $main_root_id, $main_parent_id, $main_room_id, &$this->main_active_node_arr,"edit");
		$func = array($this->menuView, 'fetchcallback');
		$this->menus =& $this->menuView->getShowPageById($this->block_id, $main_page_id, $main_root_id, $main_parent_id, $page['room_id'],  $page['thread_num']+1,$func, $func_param);
		
		$this->thread_num = $page['thread_num']+1;
		$this->parent_id = $page_id;
		
		return 'success';
	}
}
?>
