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
class Todo_Action_Admin_Operation extends Action
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
	var $whatsnewAction = null;

	function execute()
	{
		switch ($this->mode) {
    		case "move":
    			//存在チェック
				$where_params = array(
					"todo_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$result = $this->db->selectExecute("todo", $where_params);
    			if($result === false || !isset($result[0])) {
					return "false";
				}

    			//更新
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("todo", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$todo_blocks_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"todo_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("todo_block", $todo_blocks_params, $where_params, false);
				if($result === false) {
					return "false";
				}

				//新着のデータ更新するために取得
				$where_params = array(
					"todo_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$func = array($this, "_fetchcallbackTodo");
				$task_id_arr = $this->db->selectExecute("todo_task", $where_params, null, null, null, $func);
				if($task_id_arr === false) {
					return "false";
				}

				$result = $this->db->updateExecute("todo_task", $params, $where_params, false);
				if($result === false) {
					return "false";
				}

				$where_params = array(
					"todo_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("todo_category", $params, $where_params, false);
				if($result === false) {
					return "false";
				}

				//--新着情報関連 Start--
				if(is_array($task_id_arr) && count($task_id_arr) > 0) {
					$whatsnew = array(
						"unique_id" => $task_id_arr,
						"room_id" => $this->move_room_id
					);
					$result = $this->whatsnewAction->moveUpdate($whatsnew);
					if ($result === false) {
						return false;
					}
				}
				//--新着情報関連 End--

				break;
			default:
				return "false";
		}
		return "true";
	}
	/**
	 * fetch時コールバックメソッド(config)
	 * @param result adodb object
	 * @access	private
	 */
	function &_fetchcallbackTodo($result) {
		$task_id_arr = array();
		while ($row = $result->fetchRow()) {
			$task_id_arr[] = $row['task_id'];
		}
		return $task_id_arr;
	}
}
?>