<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タグ登録画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Tag_Entry extends Action
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $request = null;

    //  値をセットするため
    var $tag = null;
	var $search_flag = null;
	var $select_all_flag = null;
	var $filter = null;
	var $sortCol = null;
	var $sortDir = null;
	var $page = null;
	
	var $top_el_id = null;
	var $parent_id_name = null;
	var $top_id_name = null;

    /**
     * タグ登録画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->search_flag = $this->request->getParameter("search_flag");
		$this->select_all_flag = $this->request->getParameter("select_all_flag");
		$this->filter = $this->request->getParameter("filter");
		$this->sortCol = $this->request->getParameter("sortCol");
		$this->sortDir = $this->request->getParameter("sortDir");
		$this->page = $this->request->getParameter("page");
		
		$this->tag = $this->pmView->getTag();
        return "success";
    }
}
?>
