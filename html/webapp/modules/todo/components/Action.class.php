<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todo登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Components_Action
{
	/**
	 * @var Containerオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Todo_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
	}

	/**
	 * カテゴリデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setDefaultCategory($todo_id)
	{
		$params = array(
			"category_id" => 0,
			"todo_id" => $todo_id,
			"category_name" => "",
			"display_sequence" => 1
		);

		$result = $this->_db->insertExecute("todo_category", $params, true);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * Todoデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setTodo()
	{
		$params = array(
			"todo_name" => $this->_request->getParameter("todo_name"),
			"task_authority" => intval($this->_request->getParameter("task_authority"))
		);

		$todoID = $this->_request->getParameter("todo_id");
		if (empty($todoID)) {
			$params["room_id"] = intval($this->_request->getParameter("room_id"));
			$result = $this->_db->insertExecute("todo", $params, true, "todo_id");
		} else {
			$params["todo_id"] = $todoID;
			$result = $this->_db->updateExecute("todo", $params, "todo_id", true);
		}
		if (!$result) {
			return false;
		}

		if (!empty($todoID)) {
        	return true;
        }

		$todoID = $result;
		$this->_request->setParameter("todo_id", $todoID);
        if (!$this->setBlock()) {
			return false;
		}

		return true;
	}

	/**
	 * Todoデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteTodo()
	{
		$params = array(
			"todo_id" => $this->_request->getParameter("todo_id")
		);

    	if (!$this->_db->deleteExecute("todo_block", $params)) {
    		return false;
    	}

		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$tasks = $this->_db->selectExecute('todo_task', $params);
		if(!empty($tasks)) {
			foreach($tasks as $task) {
				if (!$whatsnewAction->delete($task['task_id'])) {
					return false;
				}
			}
			if (!$this->_db->deleteExecute("todo_task", $params)) {
				return false;
			}
    	}

    	if (!$this->_db->deleteExecute("todo", $params)) {
    		return false;
    	}

		return true;
	}

	/**
	 * Todo用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$blockID = $this->_request->getParameter("block_id");

		$params = array($blockID);
		$sql = "SELECT block_id ".
				"FROM {todo_block} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $blockID,
			"todo_id" => $this->_request->getParameter("todo_id")
		);

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($blockIDs)
				&& $actionName == "todo_action_edit_current") {
			if (!$this->_db->updateExecute("todo_block", $params, "block_id", true)) {
				return false;
			}

			return true;
		}

		if ($actionName == "todo_action_edit_current") {
			$todoView =& $this->_container->getComponent("todoView");
			$todo = $todoView->getDefaultTodo();
		}
		if ($actionName == "todo_action_edit_entry") {
			$todo = $this->_request->getParameter("todo");
		}
		if (!empty($todo)) {
			$this->_request->setParameter("default_sort", $todo["default_sort"]);
		}

		$params["default_sort"] =  intval($this->_request->getParameter("default_sort"));
		$params["used_category"] =  intval($this->_request->getParameter("used_category"));

		if (!empty($blockIDs)) {
			$result = $this->_db->updateExecute("todo_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("todo_block", $params, true);
		}
        if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * タスクデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setTask()
	{
		$taskID = $this->_request->getParameter("task_id");
		$todoID = $this->_request->getParameter("todo_id");
		$priority = $this->_request->getParameter("priority");
		$state = $this->_request->getParameter("state");
		$period = $this->_request->getParameter("period");
		$task_value = $this->_request->getParameter("task_value");
		$progress = $this->_request->getParameter("progress");
		$category_id = $this->_request->getParameter("category_id");
		if ($state == _ON) {
			$progress = '100';
		}
		if ($progress == '100') {
			$state = _ON;
		} else {
			$state = _OFF;
		}

		$whatsnewFlag = false;
		$calendarFlag = false;

		$session =& $this->_container->getComponent("Session");

		if (empty($taskID)) {
			$params = array($todoID);
			$sql = "SELECT MAX(task_sequence) ".
					"FROM {todo_task} ".
					"WHERE todo_id = ?";
			$sequences = $this->_db->execute($sql, $params, null, null, false);
			if ($sequences === false) {
				$this->_db->addError();
				return false;
			}

			$params = array(
				"todo_id" => $todoID,
				"task_sequence" => $sequences[0][0] + 1,
				"priority" => intval($priority),
				"state" => intval($state),
				"period" => $period,
				"progress" => intval($progress),
				"category_id" => intval($category_id),
				"task_value" => $task_value
			);
			$result = $this->_db->insertExecute("todo_task", $params, true, "task_id");
			$whatsnewFlag = true;
			$calendarFlag = true;
		} else {
			$params = array(
				"task_id" => $taskID
			);
			if (isset($priority)) {
				$params["priority"] = intval($priority);
			}
			if (isset($state)
					|| $session->getParameter("_mobile_flag") == _ON) {
				$params["state"] = intval($state);
			}
			if (isset($period)) {
				$params["period"] = $period;
			}
			if (isset($progress)) {
				$params["progress"] = intval($progress);
			}
			if (isset($category_id)) {
				$params["category_id"] = intval($category_id);
			}
			if (isset($task_value)) {
				$params["task_value"] = $task_value;
				$whatsnewFlag = true;
				$calendarFlag = true;
			}

			$result = $this->_db->updateExecute("todo_task", $params, "task_id", true);
		}
		if (!$result) {
			return false;
		}

		$insertFlag = false;
		if (empty($taskID)) {
			$taskID = $result;
			$this->_request->setParameter("task_id", $taskID);
			$insertFlag = true;
		}
		if ($session->getParameter("_mobile_flag") == _ON) {
			$this->_request->setParameter("target_state", intval($state));
		}

		// -- 新着情報関連 Start --
		if ($whatsnewFlag) {
			$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
			$todo = $this->_request->getParameter("todo");
			$value = $todo["todo_name"]. _SEARCH_SUBJECT_SEPARATOR. $task_value;
			$whatsnew = array(
				"unique_id" => $taskID,
				"title" => $value,
				"description" => $value,
				"action_name" => "todo_view_main_init",
				"parameters" => "todo_id=". $todo["todo_id"]
			);
			if ($insertFlag) {
				$result = $whatsnewAction->insert($whatsnew);
			} else {
				$result = $whatsnewAction->update($whatsnew);
			}
			if ($result === false) {
				return false;
			}
		}
		// -- 新着情報関連 End --

		// -- カレンダ情報関連 Start --
		if ($calendarFlag) {
			$task = $this->_request->getParameter("task");
			$calendarAction =& $this->_container->getComponent("calendarAction");
			$calendar = $this->_request->getParameter("calendar");
			if ($calendar == _ON) {
				$params = array(
					"room_id" => $this->_request->getParameter("room_id"),
				);

				if (!empty($task["task_value"])) {
					$params["title"] = $task["task_value"];
				}
				if (isset($task_value)) {
					$params["title"] = $task_value;
				}
				if (isset($period)) {
					$params["start_time_full"] = date("YmdHis", mktime(intval(substr($period,8,2)), intval(substr($period,10,2)), intval(substr($period,12,2)),
																	intval(substr($period,4,2)), intval(substr($period,6,2))-1, intval(substr($period,0,4))));
					$params["end_time_full"] = $period;
					$params["allday_flag"] = _ON;
				}

				$params["link_module"] = CALENDAR_LINK_TODO;
				$params["link_id"] = $taskID;
				$params["link_action_name"] = "todo_view_main_init&todo_id=". $todoID. "&block_id=". $this->_request->getParameter("block_id");
				if (empty($task["calendar_id"])) {
					$result = $calendarAction->insertPlan($params);
				} else {
					$result = $calendarAction->updatePlan($task["calendar_id"], $params);
				}
			} elseif (!empty($task["calendar_id"])) {
				$result = $calendarAction->deletePlan($task["calendar_id"]);
			}
			if ($result === false) {
				return false;
			}

			if ($calendar == _ON
					&& !empty($task["calendar_id"])) {
				return true;
			}

			if ($calendar == _ON) {
				$params = array(
					"task_id" => $taskID,
					"calendar_id" => $result
				);
			}
			if ($calendar != _ON) {
				$params = array(
					"task_id" => $taskID,
					"calendar_id" => 0
				);
			}
			if (!$this->_db->updateExecute("todo_task", $params, "task_id", true)) {
				return false;
			}
		}
		// -- カレンダ情報関連 End --

		return true;
	}

	/**
	 * タスク番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateTaskSequence()
	{
		$dragSequence = $this->_request->getParameter("drag_sequence");
		$dropSequence = $this->_request->getParameter("drop_sequence");

		$params = array(
			$this->_request->getParameter("todo_id"),
			$dragSequence,
			$dropSequence
		);

        if ($dragSequence > $dropSequence) {
        	$sql = "UPDATE {todo_task} ".
					"SET task_sequence = task_sequence + 1 ".
					"WHERE todo_id = ? ".
					"AND task_sequence < ? ".
					"AND task_sequence > ?";
        } else {
        	$sql = "UPDATE {todo_task} ".
					"SET task_sequence = task_sequence - 1 ".
					"WHERE todo_id = ? ".
					"AND task_sequence > ? ".
					"AND task_sequence <= ?";
        }

		$result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		if ($dragSequence > $dropSequence) {
			$dropSequence++;
		}
		$params = array(
			$dropSequence,
			$this->_request->getParameter("drag_task_id")
		);

    	$sql = "UPDATE {todo_task} ".
				"SET task_sequence = ? ".
				"WHERE task_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * タスクデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteTask()
	{
		$calendarAction =& $this->_container->getComponent("calendarAction");
		$task = $this->_request->getParameter("task");
		if (!empty($task["calendar_id"])
				&& !$calendarAction->deletePlan($task["calendar_id"], CALENDAR_PLAN_EDIT_THIS)) {
			return false;
		}

		$params = array(
			"task_id" => $task["task_id"]
		);

		$sql = "SELECT task_sequence ".
				"FROM {todo_task} ".
				"WHERE task_id = ?";
		$sequences = $this->_db->execute($sql, $params, 1, null, false);
		if ($sequences === false) {
			$this->_db->addError();
			return false;
		}
		$sequence = $sequences[0][0];

    	if (!$this->_db->deleteExecute("todo_task", $params)) {
    		return false;
    	}

		$params = array(
			"todo_id" => $task["todo_id"]
		);
		$sequenceParam = array("task_sequence" => $sequence);
		if (!$this->_db->seqExecute("todo_task", $params, $sequenceParam)) {
			return false;
		}

		// -- 新着情報関連 Start --
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$result = $whatsnewAction->delete($task["task_id"]);
		if ($result === false) {
			return false;
		}
		// -- 新着情報関連 End --

		return true;
	}

}
?>