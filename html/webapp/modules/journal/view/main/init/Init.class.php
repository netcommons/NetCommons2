<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事一覧画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Journal_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $journal_id = null;
	var $category_id = null;
	var $visible_item = null;
	var	$block_id = null;
	var $now_page = null;

	var $post_id = null;

	// バリデートによりセット
	var $journal_obj = null;

	// 使用コンポーネントを受け取るため
	var $journalView = null;
	var $session = null;
	var $mobileView = null;
	var $request = null;

	// 値をセットするため
	var $categories = null;
	var $journal_count = null;
	var $journal_list = null;
	var $block_num   = null;
	var $trackback_result = null;

	//ページ
    var $pager = null;

	/**
	 * 記事一覧画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->trackback_result = $this->request->getParameter("trackback_result");
		if(empty($this->journal_id)) {
			$this->journal_id = $this->journal_obj['journal_id'];
		}

		$this->categories = $this->journalView->getCatByJournalId($this->journal_id);
		if($this->categories === false) {
			return 'error';
		}

		$category_id = $this->session->getParameter(array("journal", $this->journal_id, "category_id"));
		if($this->category_id != "") {
			$this->session->setParameter(array("journal", $this->journal_id, "category_id"), $this->category_id);
		}else if($category_id != ""){
			$this->category_id = $category_id;
		}

		$visible_item = $this->session->getParameter(array("journal", $this->journal_id, "visible_item"));
		if($this->visible_item != "") {
			if($visible_item != "" && $this->visible_item != $visible_item) {
				$this->now_page = 1;
			}
			$this->session->setParameter(array("journal", $this->journal_id, "visible_item"), $this->visible_item);
		}else if($visible_item != ""){
			$this->visible_item = $visible_item;
		}else {
			$this->visible_item = $this->journal_obj['visible_item'];
		}

		$now_page = $this->session->getParameter(array("journal", $this->journal_id, "now_page"));
		if(!empty($this->now_page)) {
			$this->session->setParameter(array("journal", $this->journal_id, "now_page"), $this->now_page);
		}else if(!empty($now_page)){
			$this->now_page = $now_page;
		}

		$this->journal_count = $this->journalView->getPostCount($this->journal_id, $this->category_id);
		if($this->journal_count === false) {
			return 'error';
		}

		$this->journalView->setPageInfo($this->pager, $this->journal_count, $this->visible_item, $this->now_page);
		$this->journal_list = $this->journalView->getPostList($this->journal_id, $this->category_id, $this->visible_item, $this->pager['disp_begin']);
		if($this->journal_list === false) {
			return 'error';
		}

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		return 'success';
	}
}
?>