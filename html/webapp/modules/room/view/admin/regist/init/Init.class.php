<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム作成-編集初期画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_Regist_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	var $subgroup_flag = null;			//サブグループ作成用
	
	// コンポーネントを使用するため
	var $pagesView = null;
	var $session = null;
	
	// 値をセットするため
	var $page = array();
	var $parent_page = array();
	
	var $subgroup_pages = array();
	
    /**
     * ルーム作成-編集初期画面
     *
     * @access  public
     */
    function execute()
    {	
    	$this->parent_page_id = ($this->parent_page_id == null) ? 0 : intval($this->parent_page_id);
    	$this->edit_current_page_id = ($this->edit_current_page_id == null) ? 0 : intval($this->edit_current_page_id);
    	
    	$room_name =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","room_name"));
		if($this->edit_current_page_id != 0) {
    		if(!$room_name) {
    			$this->page =& $this->pagesView->getPageById(intval($this->edit_current_page_id));
    			if($this->page === false) return 'error';
			}
    	}
    	if($room_name) {
    		// セッションからデータ取得
    		$this->page['page_name'] =& $room_name;
			$this->page['display_flag'] =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","display_flag"));
			$this->page['default_entry_flag'] =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","space_type_common"));
    	}
    	if($this->parent_page_id != 0) {
	    	$this->parent_page =& $this->pagesView->getPageById(intval($this->parent_page_id));
	    	if($this->parent_page === false) return 'error';
    	}
    	if($this->subgroup_flag == _ON && $this->parent_page) {
    		// ルーム一覧取得(thread==1,space_type=parent_page['space_type'])
    		$where_params = array(
    							'user_id' => $this->session->getParameter("_user_id"),
    							'thread_num' => 1,
    							'space_type' => $this->parent_page['space_type'],
    							'{pages}.page_id={pages}.room_id' => null,
    							'createroom_flag' => _ON
    						);
    		$order_params = array("display_sequence" => "ASC");
    		$this->subgroup_pages =& $this->pagesView->getPagesUsers($where_params, $order_params);
    		if($this->subgroup_pages === false) return 'error';
    	}
    	return 'success';
    }
}
?>