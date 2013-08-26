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
	var $session = null;

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

			case "copy":
				$user_id = $this->session->getParameter("_user_id");
				$user_name = $this->session->getParameter("_handle");
				$time = timezone_date();

				//todoテーブルの取得
				$where_params = array(
					"todo_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$result = $this->db->selectExecute("todo", $where_params);
				if($result === false || !isset($result[0])) {
					return "false";
				}
				$todo = $result[0];

				//todoテーブルのコピー
				unset($todo["todo_id"]);
				$todo["room_id"] = $this->move_room_id;
				$todo['insert_user_id'] = $user_id;
				$todo['update_user_id'] = $user_id;
				$todo['insert_user_name'] = $user_name;
				$todo['update_user_name'] = $user_name;
				$todo['insert_time'] = $time;
				$todo['update_time'] = $time;

				$copy_todo_id = $this->db->insertExecute("todo", $todo, false, "todo_id");
				if ($copy_todo_id === false) {
					return 'error';
				}

				//todo_categoryテーブルのコピー
				$sql = "INSERT INTO {todo_category}"
					. " SELECT "
							. "category_id" //item_type_id
							. ", ?" //todo_id
							. ", category_name"
							. ", display_sequence"
							. ", ?" //room_id
							. ", ?" //insert_time
							. ", insert_site_id"
							. ", ?" //insert_user_id
							. ", ?" //insert_user_name
							. ", ?" //update_time
							. ", update_site_id"
							. ", ?" //update_user_id
							. ", ?" //update_user_name
						. " FROM {todo_category}"
						. " WHERE todo_id = ? AND room_id = ?";

				$params = array(
					"copy_todo_id" => $copy_todo_id,
					"copy_room_id" => $this->move_room_id,
					"insert_time" => $time,
					"insert_user_id" => $user_id,
					"insert_user_name" => $user_name,
					"update_time" => $time,
					"update_user_id" => $user_id,
					"update_user_name" => $user_name,
					"org_todo_id" => $this->unique_id,
					"org_room_id" => $this->room_id,
				);

				$result = $this->db->execute($sql, $params);
				if ( $result === false ) {
					// エラーが発生した場合、エラーリストに追加
					$this->db->addError();
					return 'error';
				}

				//todo_taskテーブルの取得
				$where_params = array(
					"todo_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$orgTasks = $this->db->selectExecute("todo_task", $where_params);
				if($orgTasks === false) {
					return "false";
				}

				//todo_taskテーブルのコピー
				foreach ($orgTasks as $i=>$task) {
					unset($task["task_id"]);
					$task["todo_id"] = $copy_todo_id;
					$task["state"] = _OFF;
					$task["period"] = "";
					$task["calendar_id"] = _OFF;
					$task["progress"] = _OFF;
					$task["room_id"] = $this->move_room_id;
					$task['insert_user_id'] = $user_id;
					$task['update_user_id'] = $user_id;
					$task['insert_user_name'] = $user_name;
					$task['update_user_name'] = $user_name;
					$task['insert_time'] = $time;
					$task['update_time'] = $time;

					$task_id = $this->db->insertExecute("todo_task", $task, false, "task_id");
					if ($task_id === false) {
						return 'error';
					}
				}

				//todo_blockテーブルの取得
				$where_params = array(
					"block_id"=> intval($this->block_id)
				);
				$result = $this->db->selectExecute("todo_block", $where_params);
				if($result === false || !isset($result[0])) {
					return "false";
				}
				$block = $result[0];

				//todo_blockテーブルのコピー
				$block["block_id"] = $this->move_block_id;
				$block["todo_id"] = $copy_todo_id;
				$block["room_id"] = $this->move_room_id;
				$block['insert_user_id'] = $user_id;
				$block['update_user_id'] = $user_id;
				$block['insert_user_name'] = $user_name;
				$block['update_user_name'] = $user_name;
				$block['insert_time'] = $time;
				$block['update_time'] = $time;

				$result = $this->db->insertExecute("todo_block", $block, false);
				if ($result === false) {
					return 'error';
				}

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