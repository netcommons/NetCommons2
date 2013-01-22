<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム写真一覧画面表示携帯版アクションクラス
 *
 * @package     NetCommons
 * @author      Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2010 AllCreator Co., Ltd.
 * @project     NC Support Project, provided by AllCreator Co., Ltd.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */

class Photoalbum_View_Mobile_List extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;
	var $block_id = null;
	var $album_id = null;
	var $pageNumber = null;
	var $sort = null;

	// 使用コンポーネントを受け取るため
	var $photoalbumView = null;
	var $request = null;
	var $session = null;
	var $configView = null;

	// validatorから受け取るため
	var $album = null;

	// 値をセットするため
	var $photoCount = null;
	var $pagePrevious = null;
	var $pageNext = null;
	var $pageStart = null;
	var $pageEnd = null;
	var $photos = null;
	var $visible_row = null;

	var $block_num = null;

	/**
	 * フォトアルバム写真一覧画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		// 表示件数を設定
		$this->visible_row = PHOTOALBUM_MOBILE_PHOTO_LIST_LENGTH;

		// 改ページデータの設定
		$pageCount = 0;
		$this->photoCount = $this->album['photo_count'];
		if (!empty($this->photoCount)) {
			$pageCount = ceil($this->photoCount / $this->visible_row);
		}
		if ($pageCount > 1) {
			if ($this->pageNumber === null && $this->session->getParameter('photoalbum_photolist_page_number'. $this->block_id) !== null) {
				$this->pageNumber = $this->session->getParameter('photoalbum_photolist_page_number'. $this->block_id);
			}
			$this->pageNumber = intval($this->pageNumber);
			$this->session->setParameter('photoalbum_photolist_page_number'. $this->block_id, $this->pageNumber);

			$this->pagePrevious = $this->pageNumber - 1;
			$this->pageNext = $this->pageNumber + 1;

			$visiblePage = $this->session->getParameter('photoalbum_photolist_visible_page'. $this->block_id);
			if (!isset($visiblePage)) {
				$visiblePage = $this->configView->getConfigByConfname($this->module_id, 'visible_page');
				$visiblePage = $visiblePage['conf_value'];
				$this->session->setParameter('photoalbum_photolist_visible_page'. $this->block_id, $visiblePage);
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

		$this->request->setParameter('album_id', $this->album['album_id']);
		$this->photos = $this->photoalbumView->getPhotos($this->visible_row, $offset);
		if ($this->photos === false) {
			return 'error';
		}
		return 'success';
	}
}
?>