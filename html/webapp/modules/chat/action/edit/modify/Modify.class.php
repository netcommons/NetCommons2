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
class Chat_Action_Edit_Modify extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $height = null;
	var $width = null;
	var $reload = null;
	var $status = null;
	var $display_type = null;
	var $line_num = null;
	//var $allow_anonymous_chat = null;

	// 使用コンポーネントを受け取るため
	var $db = null;

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{
		$params = array(
			"block_id" => intval($this->block_id),
			"height" => intval($this->height),
			"width" => intval($this->width),
			"reload" => intval($this->reload),
			"status" => intval($this->status),
			"display_type" => intval($this->display_type),
			"line_num" => intval($this->line_num)
			//"allow_anonymous_chat" => $this->allow_anonymous_chat
		);
		
		$where_params = array(
			"block_id" => $params["block_id"]
		);
		$result = $this->db->updateExecute("chat", $params, $where_params ,true);
		if($result) {
			if(intval($this->status) == 0) {
				$this->db->deleteExecute("chat_contents", $where_params);
				$this->db->deleteExecute("chat_login", $where_params);
			}
			return 'success';
		} else {
			return 'error';
		}
	}
}
?>
