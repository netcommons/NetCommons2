<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム初期画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
    var $block_id = null;
    var $visible_row = null;
    var $sort = null;
    var $pageNumber = null;

	// 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    var $session = null;
 	var $configView = null;
 	var $request = null;
	var $mobileView = null;

    // validatorから受け取るため
	var $photoalbum = null;

    // 値をセットするため
    var $albums = null;
    var $albumCount = null;
    var $pagePrevious = null;
    var $pageNext = null;
    var $pageStart = null;
    var $pageEnd = null;
    var $album = null;
    var $photos = null;

    /**
     * フォトアルバム初期画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	if ($this->photoalbum["display"] == PHOTOALBUM_DISPLAY_SLIDE) {
    		$this->request->setParameter("album_id", $this->photoalbum["display_album_id"]);

    		$this->album = $this->photoalbumView->getAlbum();
    		if ($this->album === false) {
				return "error";
			}

    		$this->photos = $this->photoalbumView->getPhotos();
    		if ($this->photos === false) {
				return "error";
			}

    		return "slide";
    	}

		$mobile_flag = $this->session->getParameter('_mobile_flag');
		// ページに存在するブロック数
		if($mobile_flag == true) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock($this->block_id);
		}

		// 表示件数を設定
		if($mobile_flag == true) {
			$this->visible_row = PHOTOALBUM_MOBILE_ALBUM_LIST_LENGTH;
		} else {
			if (!isset($this->visible_row)) {
				$this->visible_row = $this->session->getParameter("photoalbum_visible_row". $this->block_id);
			} else {
				$this->session->removeParameter("photoalbum_page_number". $this->block_id);
			}
			if (!isset($this->visible_row)) {
				$this->visible_row = $this->photoalbum["album_visible_row"];
			}
		}
		$this->visible_row = intval($this->visible_row);
		$this->session->setParameter("photoalbum_visible_row". $this->block_id, $this->visible_row);

    	// ソート項目を設定
		if (!isset($this->sort)) {
			$this->sort = $this->session->getParameter("photoalbum_album_sort". $this->block_id);
    	}
		if (!isset($this->sort)) {
			$this->sort = PHOTOALBUM_ALBUM_SORT_NEW;
		}
		$this->sort = intval($this->sort);
		$this->session->setParameter("photoalbum_album_sort". $this->block_id, $this->sort);

    	// 改ページデータの設定
		$pageCount = 0;
		$this->albumCount = $this->photoalbumView->getAlbumCount();
		if (!empty($this->albumCount) && !empty($this->visible_row)) {
			$pageCount = ceil($this->albumCount / $this->visible_row);
		}
		if ($pageCount > 1) {
			if (!isset($this->pageNumber)) {
				$this->pageNumber = $this->session->getParameter("photoalbum_page_number". $this->block_id);
	    	}
			$this->pageNumber = intval($this->pageNumber);
			$this->session->setParameter("photoalbum_page_number". $this->block_id, $this->pageNumber);

			$this->pagePrevious = $this->pageNumber - 1;
			$this->pageNext = $this->pageNumber + 1;

			$visiblePage = $this->session->getParameter("photoalbum_visible_page". $this->block_id);
			if (!isset($visiblePage)) {
				$visiblePage = $this->configView->getConfigByConfname($this->module_id, "visible_page");
				$visiblePage = $visiblePage["conf_value"];
				$this->session->setParameter("photoalbum_visible_page". $this->block_id, $visiblePage);
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
    	$this->albums = $this->photoalbumView->getAlbums($offset);
    	if ($this->albums === false) {
			return "error";
		}

		return "list";
    }
}
?>