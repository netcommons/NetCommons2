<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  携帯メニュー画面：ページ一覧を出す。指定ページ内のブロック一覧を出す
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Menu_View_Mobile_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $room_id = null;
	var $t = null;

	// コンポーネントを使用するため
	var $mobileView = null;
	var $session = null;
	var $modulesView = null;
	var $configView = null;

	//AllowIdListのパラメータを受け取るため
	var $room_arr = null;

	// 値をセットするため
	var $blocks = null;
	var $count = null;
	var $pageTree = null;
	var $pageCount = null;
	var $topPage = null;
	var $menu_display_type = null;
	var $each_room_flag = null;
	var $return_param = null;
	var $html_flag = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		// 携帯メニューのConfig設定を取得
		$mod  = $this->modulesView->getModuleByDirname( "mobile" );
		if( $mod === false ) {
			return "error";
		}
		$conf = $this->configView->getConfig( $mod['module_id'], false );
		if( $conf === false ) {
			return "error";
		}
		$this->menu_display_type = $conf['mobile_menu_type']['conf_value'];
		$this->each_room_flag = $conf['mobile_menu_each_room']['conf_value'];

		// ルーム別メニュでかつどこかのルームを表示させようとしてたら
		if( $this->room_id != 0 && $this->room_id != _SPACE_TYPE_PUBLIC && $this->each_room_flag == _ON ) {
			$is_room_detail_display = true;
		} else {
			$is_room_detail_display = false;
		}
		if( $this->t == _ON || $is_room_detail_display == true ) {

			$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
			$this->blocks = $this->mobileView->getBlocksByPage($this->page_id);
			if ($this->blocks === false) {
				return 'error';
			}
			if( isset( $this->blocks[0] ) ) {
				$this->count = count($this->blocks[0]);
			} else {
				$this->count = 0;
			}
			// ブロックがひとつしかないルーム配下ページの詳細表示
			if( $this->count == 1 && $is_room_detail_display==true ) {
				$this->page_id = $this->room_id;
				$this->blocks = $this->mobileView->getBlocksByPage($this->page_id);
				if ($this->blocks === false) {
					return 'error';
				}
			}

			$currentPageName = '';
			$currentRoomId = '';
			$currentSpaceType = _SPACE_TYPE_PUBLIC;
			$currentPage = $this->mobileView->getCurrentPage($this->page_id);
			if (!empty($currentPage)) {
				list($currentRoomId, $currentPageName, $currentSpaceType) = array_values($currentPage);
			}
			$this->session->setParameter('_page_title', $currentPageName);
			if($currentRoomId != _SELF_TOPPUBLIC_ID) {
				$this->return_param = '&page_id=' . $currentRoomId . '&t=' ._ON;
			}
		}

		$roomIds = $this->mobileView->getAllowRoomIdArr($this->room_arr);
		$this->pageTree = $this->mobileView->getPageTree($this->menu_display_type, $this->each_room_flag, $roomIds);
		if ($this->pageTree === false) {
			return 'error';
		}
		if(isset($this->pageTree[$this->page_id])) {
			$this->pageCount = count($this->pageTree[$this->page_id]);
		}
		$this->topPage = current($this->pageTree);
		if ($this->each_room_flag == _OFF) {
			$root = array();
			$dummy = array('thread_num'=>0,'visible'=>true);
			if (isset($this->pageTree[_SPACE_TYPE_PUBLIC])) {
				$root[0][_SPACE_TYPE_PUBLIC] = $dummy;
			}
			if (isset($this->pageTree[_SPACE_TYPE_GROUP])) {
				$root[0][_SPACE_TYPE_GROUP] = $dummy;
			}
			if (isset($_SESSION['_self_myroom_page']['room_id'])) {
				$root[0][$_SESSION['_self_myroom_page']['room_id']] = $dummy;
			}
			if (isset($root[0])) {
				$this->topPage = $root[0];
				$this->pageTree += $root;
			}
		}
		$this->mobileView->getRoomTree($this->room_arr, $this->menu_display_type, $this->each_room_flag, $roomIds);

		if($this->t == _ON || $is_room_detail_display) {
			return 'success_detail';
		} else {
			return 'success';
		}
	}
}
?>