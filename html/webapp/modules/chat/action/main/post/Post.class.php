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
class Chat_Action_Main_Post extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $chat_text = null;
	var $chat_id = null;
	var $line_num = null;

	// 使用コンポーネントを受け取るため
	var $chatView = null;
	var $chatAction = null;
	var $db = null;
	
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
		//$this->chat_login .= $more;
		
		if($this->chat_text != "") {
			$user_id = $this->chatAction->getUserId();
			if($user_id == "0") {
				return 'error';
			}
			$params = array(
				"block_id" =>$this->block_id,
				"chat_text" => $this->chat_text,
				"color" => CHAT_DEFAULT_CHAT_COLOR
			);
			$result = $this->insChatText($params);
			if($result === false) {
				return 'error';
			}
			$this->chat_contents = $this->chatView->getChatText($this->block_id, $this->line_num, $this->chat_id);
		}

		return 'success';
	}
	
	/**
	 * チャットテキストInsert
	 * @param array
	 * @return boolean
	 * @access	public
	 */
	function insChatText($params=array()) {
		$where = array("block_id" => $params["block_id"]);
		$chat_id = $this->db->countExecute("chat_contents", $where) + 1;
		$parame_chat_id = array("chat_id" => $chat_id);
		$params = array_merge($parame_chat_id, $params);
		
		$result = $this->db->insertExecute("chat_contents", $params, true);
    	if($result === false) {
    		return false;
    	}
    	
		return $chat_id;
	}
}
?>
