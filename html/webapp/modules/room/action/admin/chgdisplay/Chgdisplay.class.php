<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 状態変更
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Chgdisplay extends Action
{
	// パラメータを受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	var $display_flag = null;
	
	// バリデートによりセット
	var $parent_page = null;
	var $page = null;
	
	// 使用コンポーネントを受け取るため
	var $pagesView = null;
    var $pagesAction = null;
	
    /**
     * 状態変更
     *
     * @access  public
     */
    function execute()
    {
    	$display_flag = $this->display_flag;
		if($this->edit_current_page_id == 0) return 'error';
		
    	// ----------------------------------------------------------------------
		// --- ページテーブル編集                                             ---
		// ----------------------------------------------------------------------
		$params = array(
					"display_flag" => $display_flag,
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
			$rooms_where_params = array(
				"room_id = ".intval($this->edit_current_page_id)." OR parent_id = ".intval($this->edit_current_page_id) => null
			);
			$rooms_id_arr =& $this->pagesView->getPages($rooms_where_params, null, null, null, array($this, "_roomsFetchcallback"));
			$pages_where_params = array(
				" room_id IN (". implode(",", $rooms_id_arr). ") " => null
			);
			$subgroup_pages_id_arr =& $this->pagesView->getPages($pages_where_params, null, null, null, array($this, "_subpagesFetchcallback"));

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
    }

	// add by mutaguchi@opensource-workshop.jp
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_roomsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['room_id']] = $row['room_id'];
		}
		return $ret;
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