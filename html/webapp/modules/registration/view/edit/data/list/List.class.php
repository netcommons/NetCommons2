<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 入力データ一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_View_Edit_Data_List extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;
	var $block_id = null;
	var $sort_item = null;
	var $visible_row = null;
	var $pageNumber = null;

	// 使用コンポーネントを受け取るため
	var $registrationView = null;
	var $configView = null;
	var $session = null;

	// validatorから受け取るため
	var $registration = null;
	var $items = null;

	// 値をセットするため
	var $totalDataCount = null;
	var $topics = null;
	var $posts = null;
	var $pagePrevious = null;
	var $pageNext = null;
	var $pageStart = null;
	var $pageEnd = null;
	var $datas = null;

	/**
	 * 入力データ一覧画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if (!isset($this->sort_item)) {
			$this->sort_item = $this->session->getParameter("registration_sort_item". $this->block_id);
			if (empty($this->sort_item )) {
				$this->sort_item = REGISTRATION_ALBUM_SORT_DESCEND;
			}
		}
		if ($this->sort_item != REGISTRATION_ALBUM_SORT_DESCEND
			&& $this->sort_item != REGISTRATION_ALBUM_SORT_ASCEND) {
			$this->sort_item = intval($this->sort_item);
		}
		$this->session->setParameter("registration_sort_item". $this->block_id, $this->sort_item);	

		if (!isset($this->visible_row)) {
			$this->visible_row = $this->session->getParameter("registration_visible_row". $this->block_id);
		}
		if (!isset($this->visible_row)) {
			$config = $this->configView->getConfigByConfname($this->module_id, "visible_row");
			if ($config === false) {
				return "error";
			}
			
			$this->visible_row = $config["conf_value"];
		}
		$this->visible_row = intval($this->visible_row);
		$this->session->setParameter("registration_visible_row". $this->block_id, $this->visible_row);	

		$this->totalDataCount = $this->registrationView->getDataCount();
		if ($this->totalDataCount === false) {
			return "error";
		}

		$pageCount = 0;
		if (!empty($this->totalDataCount) && !empty($this->visible_row)) {
			$pageCount = ceil($this->totalDataCount / $this->visible_row);
		}
		if ($pageCount > 1) {
			$this->pageNumber = intval($this->pageNumber);
			
			$this->pagePrevious = $this->pageNumber - 1;
			$this->pageNext = $this->pageNumber + 1;

			$visiblePage = $this->session->getParameter("registration_visible_page". $this->block_id);
			if (!isset($visiblePage)) {
				$visiblePage = $this->configView->getConfigByConfname($this->module_id, "visible_page");
				$visiblePage = $visiblePage["conf_value"];
				$this->session->setParameter("registration_visible_page". $this->block_id, $visiblePage);
			}
			if (empty($visiblePage)) {
				$visiblePage = 1;
			}

			$this->pageStart = $this->pageNumber - $visiblePage + 1;
			if ($this->pageStart < 0){
				$this->pageStart = 0;
			}

			$this->pageEnd = $this->pageNumber + $visiblePage;
			if ($this->pageEnd > $pageCount) {
				$this->pageEnd = $pageCount;
			}
		}

		$offset = $this->pageNumber * $this->visible_row;
		$this->datas = $this->registrationView->getDataList($this->visible_row, $offset);
		if ($this->datas === false) {
			return "error";
		}

		return "success";
	}
}
?>