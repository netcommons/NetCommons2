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
class Chat_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// バリデートによりセット
	var $chat_obj = null;

	// 使用コンポーネントを受け取るため
	var $chatAction = null;

	// 値をセットするため
	var $chat_login = null;

	function execute()
	{
		if(intval($this->chat_obj['status']) == _OFF) {
			return 'close';
		}
		if(intval($this->chat_obj['display_type']) == CHAT_DISPLAY_BLOCK) {
			$this->chatAction->setChatLogin($this->block_id);
			return 'block';
		}
		$this->chat_login = count($this->chatAction->getChatLogin($this->block_id, $this->chat_obj['reload']));

		return 'success';
	}
}
?>