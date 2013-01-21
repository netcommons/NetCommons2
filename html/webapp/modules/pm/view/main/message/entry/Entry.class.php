<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メッセージ登録画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Message_Entry extends Action
{	
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $request = null;

	// 値をセットするため
    var $message = null;
	var $flag = null;
	var $reply_flag = null;
	var $sender_handle = null;
	var $top_el_id = null;
	var $top_id_name = null;
	var $send_subject = null;

    /**
     * メッセージ登録画面表示アクション
     *
     * @access  public
     */
    function execute()
    {	
		$receiver_id = $this->request->getParameter("receiver_id");
		$this->reply_flag = 0;
		$flag = $this->request->getParameter("flag");
		$this->sender_handle = $this->request->getParameter("sender_handle");

		if (!empty($receiver_id)) {
			$this->message = $this->pmView->getMessage();
			if ($flag == PM_REPLY_MESSAGE) {
				$this->reply_flag = 1;
				$this->message["body"] = PM_QUOTE_BODY_START.$this->message["body"].PM_QUOTE_BODY_END;
			}
		}
		if ($this->send_subject != "") {
			$this->message["subject"] = $this->send_subject;
		}
		
		$this->flag = $flag;

        return "success";
    }
}
?>
