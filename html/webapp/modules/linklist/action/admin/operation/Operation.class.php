<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール操作時(move,copy,shortcut)に呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Action_Admin_Operation extends Action
{
	var $mode = null;	//move or shortcut or copy
	// 移動元
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;
	var $unique_id = null;
	
	// 移動先
	var $move_page_id = null;
	var $move_room_id = null;
	var $move_block_id = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	var $commonOperation = null;
	
	function execute()
	{
		switch ($this->mode) {
    		case "move":
    			//存在チェック
				$where_params = array(
					"linklist_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$result = $this->db->selectExecute("linklist", $where_params);
    			if($result === false || !isset($result[0])) {
					return "false";
				}
				
    			//更新
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("linklist", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("linklist_category", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$linklist_block_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"linklist_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("linklist_block", $linklist_block_params, $where_params, false);
				if($result === false) {
					return "false";
				}
				
				$where_params = array(
					"linklist_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$result = $this->db->updateExecute("linklist_link", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				
				break;
			default:
				return "false";
		}
		return "true";
	}
}
?>