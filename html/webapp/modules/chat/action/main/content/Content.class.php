<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Chat_Action_Main_Content extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $chat_id = null;
	var $line_num = null;

	// 使用コンポーネントを受け取るため
	var $chatView = null;
	var $chatAction = null;

	// バリデートによりセット
	var $chat_obj = null;

	// 値をセットするため
	var $chat_login = "";
	var $chat_contents = null;

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{
		if($this->chat_obj['status'] == _OFF) {
			return 'success'; // ウインドウを閉じるため
		}

		$this->chatAction->setChatLogin($this->block_id);
		$login_array = $this->chatAction->getChatLogin($this->block_id, $this->chat_obj['reload']);

		$login_count = count($login_array);
		for ($i = 0; $i < $login_count; $i++) {
			if(!empty($login_array[$i]['update_user_name'])) {
				if ($i > 0) {
					$this->chat_login .= ";|";
				}
				$this->chat_login .= $login_array[$i]['update_user_name'];
			}
		}

		$this->chat_contents = $this->chatView->getChatText($this->block_id, $this->line_num, $this->chat_id);
		return 'success';
	}
}
?>
