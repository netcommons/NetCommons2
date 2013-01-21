<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [chat表示]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Chat_View_Mobile_Contents extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// バリデートによりセット
	var $chat_obj = null;

	// 使用コンポーネントを受け取るため
	var $chatView = null;
	var $chatAction = null;
	var $session = null;
	var $mobileView = null;

	// 値をセットするため
	var $chat_login = "";
	var $chat_login_count = 0;
	var $chat_contents = array();
	var $block_num = 0;
	var $chat_id = 0;

	function execute()
	{
		$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );

		if(intval($this->chat_obj['status']) == _OFF) {
			return 'close';
		}

		$this->chatAction->setChatLogin($this->block_id);
		$login_array = $this->chatAction->getChatLogin($this->block_id, $this->chat_obj['reload']);
		$this->chat_login_count = count($login_array);

		for ($i = 0; $i < $this->chat_login_count; $i++) {
			if(!empty($login_array[$i]['update_user_name'])) {
				if ($i > 0) {
					$this->chat_login .= ",";
				}
				$this->chat_login .= $login_array[$i]['update_user_name'];
			}
		}
		$this->chat_contents = $this->chatView->getChatText($this->block_id, $this->chat_obj["line_num"]);
		if (!empty($this->chat_contents)) {
			$this->chat_id = $this->chat_contents[0]["chat_id"];
		}

		return 'success';
	}
}
?>