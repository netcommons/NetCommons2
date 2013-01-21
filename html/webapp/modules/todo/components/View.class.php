<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todo取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Components_View
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
	 * @var 携帯フラグを保持
	 *
	 * @access	private
	 */
	var $_mobile_flag = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Todo_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
		$session =& $this->_container->getComponent("Session");
		$this->_mobile_flag = $session->getParameter("_mobile_flag");
	}

	/**
	 * カテゴリデータを取得する
	 *
     * @return array	カテゴリデータ配列
	 * @access	public
	 */
	function &getCategories($todo_id)
	{
		$params = array(
			$todo_id
		);

		$sql = "SELECT category_id, category_name ".
				"FROM {todo_category} ".
				"WHERE todo_id = ? ".
				"ORDER BY display_sequence";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		if (empty($result)) {
			$commonMain =& $this->_container->getComponent("commonMain");
			$todoAction =& $commonMain->registerClass(WEBAPP_DIR.'/modules/todo/components/Action.class.php', "Todo_Components_Action", "todoAction");
			$result = $todoAction->setDefaultCategory($todo_id);
			if ($result == false) {
				return $result;
			}

			$result = array();
			$result[0] = array(
				"category_id" => 0,
				"category_name" => ""
			);
		}

		return $result;
	}

	/**
	 * カテゴリデータを取得する
	 *
     * @return array	カテゴリデータ配列
	 * @access	public
	 */
	function &getCategoryCount($todo_id, $category_id=null)
	{
		if (isset($category_id)) {
			$params = array(
				$todo_id,
				$category_id
			);
			$sql = "SELECT COUNT(*) ".
					" FROM {todo_category} ".
					" WHERE todo_id = ?".
					" AND category_id = ?";
		} else {
			$params = array(
				$todo_id
			);
			$sql = "SELECT COUNT(*) ".
					"FROM {todo_category} ".
					"WHERE todo_id = ?";
		}
		$result = $this->_db->execute($sql, $params, null, null, false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		return $result[0][0];
	}

	/**
	 * カテゴリ順序データを取得する
	 *
     * @return array	カテゴリ順序データ配列
	 * @access	public
	 */
	function &getCategorySequence($todo_id, $drag_category_id, $drop_category_id)
	{
		$params = array(
			$todo_id,
			$drag_category_id,
			$drop_category_id
		);

		$sql = "SELECT category_id, display_sequence ".
				"FROM {todo_category} ".
				"WHERE todo_id = ? ".
				"AND (category_id = ? OR category_id = ?) ";
		$result = $this->_db->execute($sql, $params);
		if ($result === false ||
			count($result) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$result[0]["category_id"]] = $result[0]["display_sequence"];
		$sequences[$result[1]["category_id"]] = $result[1]["display_sequence"];

		return $sequences;
	}

	/**
	 * Todoが配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock()
	{
		$params = array($this->_request->getParameter("todo_id"));
		$sql = "SELECT T.room_id, B.block_id ".
				"FROM {todo} T ".
				"INNER JOIN {todo_block} B ".
				"ON T.todo_id = B.todo_id ".
				"WHERE T.todo_id = ? ".
				"ORDER BY B.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}

	/**
	 * Todoが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function todoExists()
	{
		$params = array(
			$this->_request->getParameter("todo_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT todo_id ".
				"FROM {todo} ".
				"WHERE todo_id = ? ".
				"AND room_id = ?";
		$todoIDs = $this->_db->execute($sql, $params);
		if ($todoIDs === false) {
			$this->_db->addError();
			return $todoIDs;
		}

		if (count($todoIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDのTodo件数を取得する
	 *
     * @return string	Todo件数
	 * @access	public
	 */
	function getTodoCount()
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$count = $this->_db->countExecute("todo", $params);

		return $count;
	}

	/**
	 * 在配置されているTodoIDを取得する
	 *
     * @return string	配置されているTodoID
	 * @access	public
	 */
	function &getCurrentTodoID()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT todo_id ".
				"FROM {todo_block} ".
				"WHERE block_id = ?";
		$todoIDs = $this->_db->execute($sql, $params);
		if ($todoIDs === false) {
			$this->_db->addError();
			return $todoIDs;
		}

		return $todoIDs[0]["todo_id"];
	}

	/**
	 * Todo一覧データを取得する
	 *
     * @return array	Todo一覧データ配列
	 * @access	public
	 */
	function &getTodos()
	{
		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "todo_id";
		}
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT todo_id, todo_name, insert_time, insert_user_id, insert_user_name ".
				"FROM {todo} ".
				"WHERE room_id = ? ".
				$this->_db->getOrderSQL($orderParams);
		$todos = $this->_db->execute($sql, $params);
		if ($todos === false) {
			$this->_db->addError();
		}

		return $todos;
	}

	/**
	 * Todo用デフォルトデータを取得する
	 *
     * @return array	Todo用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultTodo()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);
		if ($config === false) {
        	return $config;
        }

		$todo = array(
			"task_authority" => constant($config["task_authority"]["conf_value"]),
			"default_sort" => constant($config["default_sort"]["conf_value"]),
			"used_category" => constant($config["used_category"]["conf_value"]),
		);

		return $todo;
	}

	/**
	 * 配置されているTodoデータを取得する
	 *
     * @return string	Todoデータ
	 * @access	public
	 */
	function &getTodo()
	{
		$params = array($this->_request->getParameter("todo_id"));

		$sql = "SELECT todo_id, todo_name, task_authority ".
				"FROM {todo} ".
				"WHERE todo_id = ?";
		$todos = $this->_db->execute($sql, $params, 1);
		if ($todos === false) {
			$this->_db->addError();
			return $todos;
		}

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName != "todo_view_edit_entry" &&
				$actionName != "todo_action_edit_entry") {
			$block = $this->getCurrentTodo();
			if ($block === false) {
				$this->_db->addError();
				return $block;
			}
			$todos[0]["task_authority"] = false;
			$todos[0]["default_sort"] = $block["default_sort"];
			$todos[0]["used_category"] = $block["used_category"];
			$todos[0]["soon_period"] = $this->_getSoonPeriod();
		}

		return $todos[0];
	}

	/**
	 * 現在配置されているTodoデータを取得する
	 *
     * @return array	配置されているTodoデータ配列
	 * @access	public
	 */
	function &getCurrentTodo()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT B.block_id, B.todo_id, B.default_sort, B.used_category, ".
						"T.todo_name, T.task_authority ".
				"FROM {todo_block} B ".
				"INNER JOIN {todo} T ".
				"ON B.todo_id = T.todo_id ".
				"WHERE B.block_id = ?";
		$todos = $this->_db->execute($sql, $params);
		if ($todos === false) {
			$this->_db->addError();
		}
		if (empty($todos)) {
			return $todos;
		}

		$todos[0]["task_authority"] = $this->_hasTaskAuthority($todos[0]);
		$todos[0]["soon_period"] = $this->_getSoonPeriod();

		return $todos[0];
	}

	/**
	 * タスク登録権限を取得する
	 *
	 * @param	array	$todo	タスク権限の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasTaskAuthority($todo)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		if ($authID >= $todo["task_authority"]) {
			return true;
		}

		return false;
	}

	/**
	 * 限間近警告日数データを取得する
	 *
     * @return string	限間近警告日数データ
	 * @access	public
	 */
	function &_getSoonPeriod()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfigByConfname($moduleID, "soon_period");
		if ($config === false) {
        	return $config;
        }

        return $config["conf_value"];
	}

	/**
	 * タスクデータを取得する
	 *
     * @return array	タスクデータ
	 * @access	public
	 */
	function &getTask()
	{
		$params = array(
			$this->_request->getParameter("task_id")
		);
		$sql = "SELECT task_id, todo_id, priority, state, period, calendar_id, progress, task_value, insert_user_id, category_id ".
				"FROM {todo_task} ".
				"WHERE task_id = ?";
		$tasks = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeTaskArray"));
		if ($tasks === false) {
			$this->_db->addError();
		}

		return $tasks[0];
	}

	/**
	 * タスク用デフォルトデータを取得する
	 *
     * @return array	Todo用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultTask()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfigByConfname($moduleID, "priority");
		if ($config === false) {
        	return $config;
        }

		$task = array(
			"period" => "",
			"priority" => constant($config["conf_value"])
		);

		return $task;
	}

	/**
	 * タスク件数を取得する
	 *
     * @return array	タスク件数
	 * @access	public
	 */
	function &getTaskCount()
	{
    	$params = array(
			"todo_id" => $this->_request->getParameter("todo_id")
		);
		$targetState = $this->_request->getParameter("target_state");
		if (isset($targetState) || $this->_mobile_flag == _ON) {
			$params["state"] = intval($targetState);
		}
		$count = $this->_db->countExecute("todo_task", $params);

		return $count;
	}

	/**
	 * タスクデータ配列を取得する
	 *
     * @return array	タスクデータ配列
	 * @access	public
	 */
	function &getTasks()
	{
		$todo = $this->_request->getParameter("todo");
		if ($todo["used_category"] == _ON) {
			$orderParams["category_id"] = "ASC";
		}
		$sortColumn = $this->_request->getParameter("sort_col");
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortColumn)) {
			if ($todo["default_sort"] == TODO_NONE) {
				$sortColumn = "task_sequence";
			} elseif ($todo["default_sort"] == TODO_PRIORITY) {
				$sortColumn = "priority";
				$sortDirection = "DESC";
			} elseif ($todo["default_sort"] == TODO_STATE) {
				$sortColumn = "state";
				$sortDirection = "DESC";
			} elseif ($todo["default_sort"] == TODO_PERIOD) {
				$sortColumn = "period";
				$sortDirection = "DESC";
			} elseif ($todo["default_sort"] == TODO_PROGRESS) {
				$sortColumn = "progress";
			} elseif ($todo["default_sort"] == TODO_TASK_VALUE) {
				$sortColumn = "task_value";
			}
		}
		if (empty($sortDirection)) {
			$sortDirection = "ASC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("todo_id"));
		$stateSQL = "";
		$targetState = $this->_request->getParameter("target_state");
		if (isset($targetState) || $this->_mobile_flag == _ON) {
			$params[] = intval($targetState);
			$stateSQL = "AND state = ? ";
		}
		$sql = "SELECT task_id, priority, state, period, progress, task_value, insert_user_id, category_id " .
				"FROM {todo_task} ".
				"WHERE todo_id = ? ".
				$stateSQL.
				$this->_db->getOrderSQL($orderParams);

		$callbackFunc = array($this, "_makeTaskArray");
		if ($todo["used_category"] == _OFF) {
			$funcParams = array();
		} else {
			$funcParams = array("array_key" => "category");
		}
		$tasks = $this->_db->execute($sql, $params, null, null, true, $callbackFunc, $funcParams);
		if ($tasks === false) {
			$this->_db->addError();
		}

		return $tasks;
	}

	/**
	 * タスク番号データを取得する
	 *
     * @return array	タスク番号データ配列
	 * @access	public
	 */
	function &getTaskSequence()
	{
		$params = array(
			$this->_request->getParameter("drag_task_id"),
			$this->_request->getParameter("drop_task_id"),
			$this->_request->getParameter("todo_id")
		);

		$sql = "SELECT task_id, task_sequence ".
				"FROM {todo_task} ".
				"WHERE (task_id = ? ".
				"OR task_id = ?) ".
				"AND todo_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false ||
			count($result) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$result[0]["task_id"]] = $result[0]["task_sequence"];
		$sequences[$result[1]["task_id"]] = $result[1]["task_sequence"];

		return $sequences;
	}

	/**
	 * タスクデータ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_makeTaskArray(&$recordSet, $funcParams = array())
	{
		$todo = $this->_request->getParameter("todo");
		if (!empty($funcParams["format"])) {
			$format = $funcParams["format"];
		} else {
			$format = _DATE_FORMAT;
		}
		if (!empty($funcParams["array_key"])) {
			$array_key = $funcParams["array_key"];
		} else {
			$array_key = "";
		}

		$tasks = array();
		while ($row = $recordSet->fetchRow()) {
			$row["category_id"] = isset($row["category_id"]) ? intval($row["category_id"]) : 0;
			$row["edit_authority"] = false;
			if ($todo["task_authority"]
					&& $this->_hasEditAuthority($row["insert_user_id"])) {
				$row["edit_authority"] = true;
			}

			if (!empty($row["period"])) {
				$period = timezone_date_format($row["period"], null);
				if (substr($period, 8) == "000000") {
					$previousDay = -1;
					$format = str_replace("H", "24", $format);
					$timeFormat = str_replace("H", "24", _SHORT_TIME_FORMAT);
				} else {
					$previousDay = 0;
					$timeFormat = _SHORT_TIME_FORMAT;
				}

				$date = mktime(intval(substr($period, 8, 2)),
								intval(substr($period, 10, 2)),
								intval(substr($period, 12, 2)),
								intval(substr($period, 4, 2)),
								intval(substr($period, 6, 2)) + $previousDay,
								intval(substr($period, 0, 4)));
				$row["display_period_date"] = date($format, $date);
				$row["display_period_time"] = date($timeFormat, $date);

				if ($this->_mobile_flag == _ON) {
					$row["year"] = date("Y", $date);
					$row["month"] = date("m", $date);
					$row["day"] = date("d", $date);
				}

				if (!isset($thisDate)) {
					$thisDate = timezone_date_format(null, null);
					$soonDate = mktime(0, 0, 0,
										intval(substr($thisDate, 4, 2)),
										intval(substr($thisDate, 6, 2)) + $todo["soon_period"],
										intval(substr($thisDate, 0, 4)));
					$thisDate = timezone_date_format(null, $format);
					$soonDate = date($format, $soonDate);
				}
				if ($thisDate > $row["display_period_date"]) {
					$row["period_class_name"] = TODO_PERIOD_CLASS_NAME_OVER;
				} elseif ($soonDate >= $row["display_period_date"]) {
					$row["period_class_name"] = TODO_PERIOD_CLASS_NAME_SOON;
				}
			}
			if ($array_key == "category") {
				if (!isset($tasks[$row["category_id"]])) {
					$tasks[$row["category_id"]] = array();
				}
				$tasks[$row["category_id"]][] = $row;
			} else {
				$tasks[] = $row;
			}
		}

		return $tasks;
	}

	/**
	 * 編集権限を取得する
	 *
	 * @param	array	$insertUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasEditAuthority(&$insertUserID)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");

		$authID = $session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}

		$userID = $session->getParameter("_user_id");
		if ($insertUserID == $userID) {
			return true;
		}

		$hierarchy = $session->getParameter("_hierarchy");
		$authCheck =& $container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($insertUserID);
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($block_id_arr)
	{
		$sql = "SELECT block.*, todo.todo_name" .
				" FROM {todo_block} block" .
				" INNER JOIN {todo} todo ON (block.todo_id=todo.todo_id)" .
				" WHERE block.block_id IN (".implode(",",$block_id_arr).")" .
				" ORDER BY block.insert_time DESC, block.todo_id DESC";

        return $this->_db->execute($sql, null);
	}

}
?>