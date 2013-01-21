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
class Chat_View_Main_Block extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $chatView = null;
	var $chatAction = null;
	
	// 値をセットするため
	var $chat_obj = null;
	
	function execute()
	{
		$this->chat_obj = $this->chatView->getChatById($this->block_id);
		if($this->chat_obj) {
			$this->chatAction->setChatLogin($this->block_id);
			return 'success';
		}
		return 'nonexistent';
	}
}
?>
