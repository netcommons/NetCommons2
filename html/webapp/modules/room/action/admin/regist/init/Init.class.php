<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム基本項目更新時使用
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Regist_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	
	var $room_name = null;
	var $display_flag = null;
	var $space_type_common = null;
	
	// バリデートによりセット
	var $parent_page = null;
	var $page = null;
		
	// 使用コンポーネントを受け取るため
	var $pagesView = null;
	var $pagesAction = null;
	var $request = null;
	
    /**
     * ルーム基本項目更新
     *
     * @access  public
     */
    function execute()
    {
    	$room_name = $this->room_name;
		$display_flag = $this->display_flag;
		$default_entry_flag = isset($this->space_type_common) ? $this->space_type_common: _OFF;
		if($this->edit_current_page_id == 0) return 'error';
		
    	// ----------------------------------------------------------------------
		// --- ページテーブル編集                                             ---
		// ----------------------------------------------------------------------
		$params = array(
					"page_name" =>$room_name,
					"display_flag" => $display_flag,
					"default_entry_flag" => $default_entry_flag
				);
		$upd_where_params = array("page_id" => intval($this->edit_current_page_id));
		$result = $this->pagesAction->updPage($params, $upd_where_params);
    	if($result === false) {
    		return 'error';	
    	}
    	// ----------------------------------------------------------------------
    	// ---  公開中->準備中に変更した場合、そのサブグループも準備中にする
    	// ---  準備中->公開中に変更した場合、そのサブグループも公開中にする
    	// ----------------------------------------------------------------------
    	if($this->page['display_flag'] != $display_flag) {
    		$where_params = array (
									"room_id"=>intval($this->edit_current_page_id)
								);
    		$subgroup_pages_id_arr =& $this->pagesView->getPages($where_params, null, null, null, array($this, "_subpagesFetchcallback"));
    		if(count($subgroup_pages_id_arr) > 0) {
	    		$params = array(
						"display_flag" => $display_flag
					);
				$upd_where_params = array(
											" page_id IN (". implode(",", $subgroup_pages_id_arr). ") " => null
										);
				$result = $this->pagesAction->updPage($params, $upd_where_params);
		    	if($result === false) {
		    		return 'error';	
		    	}
    		}
    	}
    	// ----------------------------------------------------------------------
		// --- default_entry_flag更新                                         ---
		// ----------------------------------------------------------------------
		if($this->page['default_entry_flag'] != $default_entry_flag) {
			$where_params = array (
									"room_id"=>intval($this->edit_current_page_id),
									"{pages}.page_id!={pages}.room_id" => null
								);
    		$child_pages_id_arr =& $this->pagesView->getPages($where_params, null, null, null, array($this, "_subpagesFetchcallback"));
    		if(count($child_pages_id_arr) > 0) {
	    		$params = array(
						"default_entry_flag" => $default_entry_flag
					);
				$upd_where_params = array(
											" page_id IN (". implode(",", $child_pages_id_arr). ") " => null
										);
				$result = $this->pagesAction->updPage($params, $upd_where_params);
		    	if($result === false) {
		    		return 'error';	
		    	}
    		}
		}
    	$permalink = $this->page['permalink'];
		$permalink_arr = explode('/', $permalink);
		
		$current_page_name = $permalink_arr[count($permalink_arr)-1];
		if($this->page['thread_num'] != 0 && $this->page['page_name'] == $current_page_name) {
			$result = $this->pagesAction->updPermaLink($this->page, $room_name);
			if ($result === false) {
				return 'error';
			}
		}
    	
    	// ----------------------------------------------------------------------
		// --- 終了処理　　　　　                                             ---
		// ----------------------------------------------------------------------
		$this->request->setParameter("show_space_type", $this->page['space_type']);
		$this->request->setParameter("show_private_flag", $this->page['private_flag']);
		$this->request->setParameter("show_default_entry_flag", $this->page['default_entry_flag']);
		
		return 'success';
    }
    
    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_subpagesFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['page_id']] = $row['page_id'];
		}
		return $ret;
	}
}
?>
