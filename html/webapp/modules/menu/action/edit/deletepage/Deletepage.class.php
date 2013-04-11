<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページ削除アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Action_Edit_Deletepage extends Action
{
	// リクエストパラメータを受け取るため
	var $main_page_id = null;
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $pagesView = null;
	var $pagesAction = null;
	var $db = null;

	// 値をセットするため
	var $url = null;

	/**
	 * ページ追加アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$page_id = intval($this->main_page_id);
		$page =& $this->pagesView->getPageById($page_id);
		if($page === false || !isset($page['page_id'])) {
			return 'error';
		}
		if($page['thread_num'] == 0 || $page['room_id'] == $page['page_id']) {
			//深さが0 または、ルーム
			return 'error';
		}

		$pageIds = array($page_id);
		$hasChildPageIds = array();
		if ($page['node_flag']) {
			$hasChildPageIds = $pageIds;
		}

		while (!empty($hasChildPageIds)) {
			$sql = "SELECT page_id, "
						. "node_flag "
					. "FROM {pages} "
					. "WHERE parent_id IN (" . implode(',', $hasChildPageIds) . ")";
			$childPages = $this->db->execute($sql);
			if ($childPages === false) {
				$this->db->addError();
				return 'error';
			}

			$hasChildPageIds = array();
			foreach ($childPages as $childPage) {
				if ($childPage['node_flag']) {
					$hasChildPageIds[] = $childPage['page_id'];
				}
				$pageIds[] = $childPage['page_id'];
			}
		}

		$inValue = implode(',', $pageIds);
		if (!$this->pagesAction->deletePagesByInOperator($inValue)) {
			return 'error';
		}

		if(in_array($this->session->getParameter("_main_page_id"), $pageIds)) {
			$this->session->setParameter("_editing_block_id", intval($this->block_id));
		
			//再表示URL
			$this->url = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION."&page_id=".$page['room_id'];
		} else {
			$this->url = "true";
		}
		return 'success';
	}
}
?>
