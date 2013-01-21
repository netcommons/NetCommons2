<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 隠しページ変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Action_Edit_Visibility extends Action
{
    // リクエストパラメータを受け取るため
    var $main_page_id = null;
    var $visibility_flag = null;
    var $block_id = null;
    
    // 使用コンポーネントを受け取るため
    var $menuView = null;
    var $menuAction = null;
    var $pagesView = null;
    var $session = null;
    var $getdata = null;
    
    /**
     * 隠しページ変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	$flat_flag = false;
    	$blocks =& $this->getdata->getParameter("blocks");
		$temp_name = $blocks[$this->block_id]['temp_name'];
		if(preg_match("/(flat|header)/i",$temp_name)) {
			$flat_flag = true;
		}

    	$page_id = intval($this->main_page_id);
    	$where_params = array(
			"page_id" => $page_id
		);
		$current_page =& $this->pagesView->getPages($where_params);
		$private_flag = _OFF;
		if($this->session->getParameter("_auth_id") >= _AUTH_CHIEF && isset($current_page[0]) && $current_page[0]['private_flag'] == _ON && $current_page[0]['thread_num'] == 0) {
			$private_flag = _ON;
		}
    	
    	if($private_flag == _ON) {
    		$menus = $this->menuView->getMenuDetail(array("block_id"=>$this->block_id, "page_id"=>-1));
    	
    		$params = array(
				"block_id" => $this->block_id,
				"page_id" => -1,
				"visibility_flag" => $this->visibility_flag,
				"room_id" => $current_page[0]['room_id']
			);
    	} else {
    		$menus = $this->menuView->getMenuDetail(array("block_id"=>$this->block_id, "page_id"=>$page_id));
    	
    		$params = array(
				"block_id" => $this->block_id,
				"page_id" => $page_id,
				"visibility_flag" => $this->visibility_flag,
				"room_id" => $current_page[0]['room_id']
			);
		}
    	
		//現状、visibility_flagしかないため、表示する場合は、削除
		if(!isset($menus[0])) {
			if($this->visibility_flag == _OFF) {
				//insert
				if(!$this->menuAction->insMenuDetail($params)) {
					return 'error';
				}
			}
		} else {
			if($this->visibility_flag == _ON) {
				//del
				if(!$this->menuAction->delMenuDetailById($this->block_id, $page_id)) {
					return 'error';
				}
				if($private_flag == _ON) {
					if(!$this->menuAction->delMenuDetailById($this->block_id, -1)) {
						return 'error';
					}
				}
			} else {
				//update
				if(!$this->menuAction->updMenuDetail($params)) {
					return 'error';
				}
			}
		}
		//子供のデータが取得できれば、すべてvisibility_flag=_ONに変更する
		//現状、visibility_flagしかないため、削除
		if(isset($current_page[0]) && ($flat_flag == false
			 || $current_page[0]['space_type'] != _SPACE_TYPE_PUBLIC || $current_page[0]['thread_num'] == 0)) {
			$parent_id_arr = array($current_page[0]['page_id']);
			$where_params = array(
				"root_id" => ($current_page[0]['root_id'] == 0) ? $current_page[0]['page_id'] : $current_page[0]['root_id'],
			);
			$order_params = array("thread_num"=>"ASC");
			$pages =& $this->pagesView->getPages($where_params, $order_params);
			if(isset($pages[0])) {
				foreach($pages as $page) {
					if(in_array($page['parent_id'],$parent_id_arr)) {
						array_push( $parent_id_arr, $page['page_id']);
					}
				}
				array_shift ($parent_id_arr);
				if(isset($parent_id_arr[0])) {
					foreach($parent_id_arr as $del_page_id) {
						if(!$this->menuAction->delMenuDetailById($this->block_id, $del_page_id)) {
							return 'error';
						}
					}
				}
			}
		}
		return 'success';
    }
}
?>
