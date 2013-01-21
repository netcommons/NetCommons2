<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Chat_Action_Mobile_Post extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $chat_text = null;
	var $write = null;
	var $clear = null;

	// コンポーネントを受け取るため
	var $session = null;
	var $db = null;

	// バリデートによりセット
	var $chat_obj = null;

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		if($this->chat_obj['status'] == _OFF) {
			return 'write'; // ウインドウを閉じるため
		}

		if (isset($this->write)) {
			if($this->chat_text != "") {
				$params = array(
					"block_id" =>$this->block_id,
					"chat_text" => $this->chat_text,
					"color" => CHAT_DEFAULT_CHAT_COLOR
				);
				$result = $this->insChatText($params);
				if($result === false) {
					return 'error';
				}
			}
	    	return 'write';
    	} elseif (isset($this->clear) && $this->session->getParameter("_auth_id") >= _AUTH_CHIEF) {
    		return 'clear';
    	} else {
	    	return 'error';
    	}
    }

	/**
	 * チャットテキストInsert
	 * @param array
	 * @return boolean
	 * @access	public
	 */
	function insChatText($params=array())
	{
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