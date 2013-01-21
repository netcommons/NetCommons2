<?php
/**
 * モジュール追加時に呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Chat_Action_Edit_Create extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	
	// バリデートによりセット
	var $chat_obj = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	
	function execute()
	{
		$params = array(
			"block_id" => intval($this->block_id), 
			"height" => intval($this->chat_obj['height']),
			"width" => intval($this->chat_obj['width']), 
			"reload" => intval($this->chat_obj['reload']),
			"status" => $this->chat_obj['status'],
			"display_type" => $this->chat_obj['display_type'],
			"line_num" => intval($this->chat_obj['line_num'])
			//"allow_anonymous_chat" => _OFF
		);
		
    	$result = $this->db->insertExecute("chat", $params, true);
    	if($result === false) {
    		return 'error';
    	}
    	
		return 'success';
	}
}
?>