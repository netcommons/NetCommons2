<?php

/**
 * 回覧一覧画面表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $list_type = null;
	var $order_type = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $circularView = null;
	var $circularCommon = null;
	var $mobileView = null;

	// 値をセットするため
	var $menu_type = null;
	var $circular_count = null;
	var $circular_list = null;
	var $now_page = null;
	var $visible_row = null;
	var $visible_row_map = null;
	var $has_create_auth = null;
	var $block_num = null;

	var $portal_info = null;

	//ページ
    var $pager = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		if($this->session->getParameter('_mobile_flag') == true) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock($this->block_id);
		}

		$block = $this->circularView->getBlock();
		if ($block === false) {
			return 'error';
		}
		$config = $this->circularView->getConfig();
		if ($config === false) {
			return 'error';
		}
		if ($block["block_type"] == CIRCULAR_BLOCK_TYPE_PORTAL) {
			$portalInfo = $this->circularView->getPortalCircular();
			if ($portalInfo === false) {
				return 'error';
			}
			$this->portal_info = $portalInfo;
			return "portal";
		}

		$this->menu_type = $this->session->getParameter(array('menu_type', $this->block_id));
		if ($this->menu_type === null) {
			$userAuthId = $this->session->getParameter('_user_auth_id');
			if ($userAuthId >= CIRCULAR_ALL_VIEW_AUTH) {
				$menuKeyValue = explode(':', CIRCULAR_ADMIN_MENU_TYPE_ARRAY);
				$this->menu_type[$menuKeyValue[0]]['list_name'] = $menuKeyValue[1];
			}
			$menuItems = explode('|', CIRCULAR_MENU_TYPE_ARRAY);
			foreach ($menuItems as $menuItem) {
				$menuKeyValue = explode(':', $menuItem);
				$this->menu_type[$menuKeyValue[0]]['list_name'] = $menuKeyValue[1];
			}
		}
		$this->session->setParameter(array('menu_type', $this->block_id), $this->menu_type);

		foreach ($this->menu_type as $menuListType=>$menuItem) {
			$count = $this->circularView->getCircularCount($menuListType);
			if ($count === false) {
				return 'error';
			}
			$this->menu_type[$menuListType]['circular_count'] = $count;
		}

		if ($this->list_type === null) {
			$listType = $this->session->getParameter(array('list_type', $this->block_id));
			if ($listType === null) {
				$this->list_type = CIRCULAR_LIST_TYPE_UNSEEN;
			} else {
				$this->list_type = $listType;
			}
		}
		$this->session->setParameter(array('list_type', $this->block_id), $this->list_type);

		$this->circular_count = $this->menu_type[intval($this->list_type)]['circular_count'];

		if ($this->visible_row === null) {
			$visibleRow = $this->session->getParameter(array('visible_row', $this->block_id));
			if ($visibleRow === null) {
				$this->visible_row = CIRCULAR_DEFAULT_VISIBLE_ROW;
			} else {
				$this->visible_row = $visibleRow;
			}
		}
		$this->session->setParameter(array('visible_row', $this->block_id), $this->visible_row);

		if ($this->now_page === null) {
			$nowPage = $this->session->getParameter(array('now_page', $this->block_id));
			if ($nowPage === null) {
				$this->now_page = 1;
			} else {
				$this->now_page = $nowPage;
			}
		}
		$this->session->setParameter(array('now_page', $this->block_id), $this->now_page);

		if ($this->order_type === null) {
			$orderType = $this->session->getParameter(array('order_type', $this->block_id));
			if ($orderType === null) {
				$this->order_type = "DESC";
			} else {
				$this->order_type = $orderType;
			}
		}
		$this->session->setParameter(array('order_type', $this->block_id), $this->order_type);

		$authId = $this->session->getParameter('_auth_id');
		if ($authId >= $config['create_authority']) {
			$this->has_create_auth = _ON;
		} else {
			$this->has_create_auth = _OFF;
		}

		$this->circularView->setPageInfo($this->pager, $this->circular_count, $this->visible_row, $this->now_page);
		$result = $this->circularView->getCircularList(intval($this->list_type), $this->visible_row, $this->pager['disp_begin'], $this->order_type);
		if ($result === false) {
			return 'error';
		}
		$this->circular_list = $result;

		$this->visible_row_map =& $this->circularCommon->getMap(CIRCULAR_VISIBLE_ROW);

		return 'success';

	}
}
?>