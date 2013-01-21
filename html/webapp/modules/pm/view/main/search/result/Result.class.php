<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メッセージ検索結果画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Search_Result extends Action 
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $pmPager = null;
	var $request = null;
	var $session = null;

	// 値をセットするため
    var $messageCount = null;
    var $messages = null;
	var $filter = null;
	var $tags_list = null;
	var $current_menu = null;
	
	var $pager = null;
	var $page = null;
	var $trash_flag = null;
	var $search_flag = null;
	
    /**
     *メッセージ検索結果画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->current_menu = PM_LEFTMENU_SEARCH;

		// メッセージ件数を取得
		$this->messageCount = $this->pmView->getMessageCount("search");
		if ($this->messageCount === false) {
			return "error";
		}
		
		// メッセージデータ配列を取得
		$query = &$this->pmView->generateMessagesQuery("search");
		$this->pager = &$this->pmPager->pager($query, PM_MAX_PAGE_DISPLAY, "r.receiver_id");
		$this->page = $this->pmPager->current_page;
		
		$this->messages = $this->pmView->getMessages($query, $this->pmPager->limit(), $this->pmPager->offset());
		
		if ($this->messages === false) {
			return "error";
		}
		
		$this->trash_flag = false;
		foreach($this->messages as $message) {
			if ($message["delete_state"] == PM_MESSAGE_STATE_TRASH) {
				$this->trash_flag = true;
				break;
			} 
		}
		
		// 選択されだフィルタを設定
		$this->filter = $this->request->getParameter("filter");
		
		// タグリストを取得
		$this->tags_list = $this->pmView->getTags();
		
		$this->search_flag = "search";
		
		return 'success';
    }
}
?>
