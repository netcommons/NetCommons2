<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メッセージ詳細画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Message_Detail extends Action
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $page_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $pmAction = null;
	var $request = null;

    // 値をセットするため
    var $message = null;
	var $current_menu = null;
	var $tags_list = null;
	var $page = null;
	var $filter = null;
	
	var $search_flag = null;
	var $trash_flag = null;
	var $active_action_name = null;
	
	var $top_el_id = null;
	var $top_id_name = null;
	var $parent_id_name = null;

    /**
     * メッセージ詳細画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$receiver_id = $this->request->getParameter("receiver_id");
		if(empty($receiver_id)){
			$message_id = $this->request->getParameter("message_id");
			$receiver_id = $this->pmView->getMessageReceiverId($message_id);
			$this->request->setParameter("receiver_id", $receiver_id);
		}
		
		$mailbox = $this->request->getParameter("mailbox");
		$location = $this->request->getParameter("location");
		$this->page = $this->request->getParameter("page");
		$this->filter = $this->request->getParameter("filter");
		// $this->top_el_id = $this->request->getParameter("top_el_id");
		$this->search_flag = $this->request->getParameter("search_flag");
		
		$this->active_action_name = $this->request->getParameter("active_action");
		if(empty($this->active_action_name)){
			$this->active_action_name = $this->request->getParameter("active_center");
		}
		$this->message = $this->pmView->getMessage();

		if(!$this->message['read_state'] && ($location != 'detail')){
			$this->pmAction->setMessageReadState();
		}
		
	    if ($this->message === false) {
        	return "error";
        }
		
		$this->trash_flag = false;
		if ($this->message["delete_state"] == PM_MESSAGE_STATE_TRASH) {
			$this->trash_flag = true;
		}
		
		// 選択されだトレイを設定
		$this->current_menu = $mailbox;

		// タグリストを取得
		$this->tags_list = $this->pmView->getTags();

        return "success";
    }
}
?>
