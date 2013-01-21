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
class Bbs_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;
	var $block_id = null;
	var $expand = null;
	var $visible_row = null;
	var $pageNumber = null;

	// 使用コンポーネントを受け取るため
	var $bbsView = null;
 	var $session = null;
 	var $configView = null;
	var $mobileView = null;

	// validatorから受け取るため
	var $bbs = null;

	// 値をセットするため
	var $postExists = null;
	var $topicCount = null;
	var $topics = null;
	var $posts = null;
	var $pagePrevious = null;
	var $pageNext = null;
	var $pageStart = null;
	var $pageEnd = null;
	var $block_num = null;
	var $pageCount = null;
	var $html_flag = null;

	/**
	 * 記事一覧画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		// ブロック数
		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}
		$this->html_flag = $this->mobileView->getTextHtmlMode($this->html_flag);

		// 表示件数を設定
		if (!isset($this->visible_row)) {
			$this->visible_row = $this->session->getParameter("bbs_visible_row". $this->block_id);
		}
		if (!isset($this->visible_row)) {
			$this->visible_row = $this->bbs["visible_row"];
		}
		$this->visible_row = intval($this->visible_row);
		$this->session->setParameter("bbs_visible_row". $this->block_id, $this->visible_row);

		// 根記事件数を設定
		$this->topicCount = 0;
		if ($this->bbs["display"] == BBS_DISPLAY_TOPIC_VALUE ||
				$this->bbs["display"] == BBS_DISPLAY_ALL_VALUE ||
				$this->bbs["display"] == BBS_DISPLAY_OLD_VALUE) {
			$this->topicCount = $this->bbsView->getTopicCount();
		}
		if ($this->topicCount === false) {
			return "error";
		}
		if ($this->bbs["display"] == BBS_DISPLAY_OLD_VALUE &&
				$this->topicCount > 0) {
			$this->topicCount--;
		}

		// 対象記事有無の設定
		if ($this->bbs["display"] == BBS_DISPLAY_NEWEST_VALUE) {
			$topicID = $this->bbsView->getNewestTopicID();
		}

		if (empty($topicID) && empty($this->topicCount)) {
			return "success";
		}
		$this->postExists = true;

		// 改ページデータの設定
		$this->pageCount = 0;
		if (!empty($this->topicCount) && !empty($this->visible_row)) {
			$this->pageCount = ceil($this->topicCount / $this->visible_row);
		}
		if ($this->pageCount > 1) {
			$this->pageNumber = intval($this->pageNumber);

			$this->pagePrevious = $this->pageNumber - 1;
			$this->pageNext = $this->pageNumber + 1;

			$visiblePage = $this->session->getParameter("bbs_visible_page". $this->block_id);
			if (!isset($visiblePage)) {
				$visiblePage = $this->configView->getConfigByConfname($this->module_id, "visible_page");
				$visiblePage = $visiblePage["conf_value"];
				$this->session->setParameter("bbs_visible_page". $this->block_id, $visiblePage);
			}
			if (empty($visiblePage)) {
				$visiblePage = 1;
			}

			$this->pageStart = $this->pageNumber - $visiblePage + 1;
			if ($this->pageStart < 0){
				$this->pageStart = 0;
			}

			$this->pageEnd = $this->pageNumber + $visiblePage;
			if ($this->pageEnd > $this->pageCount) {
				$this->pageEnd = $this->pageCount;
			}
		}

		$offset = $this->pageNumber * $this->visible_row;
		// スレッド表示用記事の設定
		if ($this->expand == BBS_EXPAND_THREAD_VALUE) {
			if ($this->bbs["display"] == BBS_DISPLAY_TOPIC_VALUE) {
				$this->topics = $this->bbsView->getTopic($this->visible_row, $offset);
			} elseif ($this->bbs["display"] == BBS_DISPLAY_NEWEST_VALUE) {
				$this->topics = $this->bbsView->getThread($topicID);
			} elseif ($this->bbs["display"] == BBS_DISPLAY_OLD_VALUE) {
				$this->topics = $this->bbsView->getOldTopic($this->visible_row, $offset);
			} elseif ($this->bbs["display"] == BBS_DISPLAY_ALL_VALUE) {
				$this->topics = $this->bbsView->getAll($this->visible_row, $offset);
			}

			if ($this->topics === false) {
				return "error";
			}
		}

		// フラット表示用記事の設定
		if ($this->expand == BBS_EXPAND_FLAT_VALUE) {
			if ($this->bbs["display"] == BBS_DISPLAY_TOPIC_VALUE) {
				$this->posts = $this->bbsView->getFlatTopic($this->visible_row, $offset);
			} elseif ($this->bbs["display"] == BBS_DISPLAY_NEWEST_VALUE) {
				$this->posts = $this->bbsView->getFlat($topicID);
			} elseif ($this->bbs["display"] == BBS_DISPLAY_OLD_VALUE) {
				$this->posts = $this->bbsView->getFlatOldTopic($this->visible_row, $offset);
			} elseif ($this->bbs["display"] == BBS_DISPLAY_ALL_VALUE) {
				$this->posts = $this->bbsView->getFlatAll($this->visible_row, $offset);
			}

			if ($this->posts === false) {
				return "error";
			}
		}

		return "success";
	}
}
?>