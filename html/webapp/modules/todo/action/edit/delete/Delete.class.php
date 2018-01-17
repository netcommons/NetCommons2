<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todo削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Action_Edit_Delete extends Action
{
	var $todo_id = null;

    // 使用コンポーネントを受け取るため
    var $todoAction = null;
    var $db = null;
	var $calendarPlanAction = null;

    /**
     * Todo削除アクション
     *
     * @access  public
     */
    function execute()
    {
		$whereParams = array(
			"todo_id" => $this->todo_id,
			"calendar_id!=0" => null
		);
		$tasks = $this->db->selectExecute('todo_task', $whereParams);
		if(!empty($tasks)) {
			foreach($tasks as $task) {
				if (!$this->calendarPlanAction->deletePlan($task["calendar_id"], CALENDAR_PLAN_EDIT_THIS)) {
					return false;
				}
			}
		}

		$whereParams = array(
			"todo_id" => $this->todo_id
		);

		$result = $this->db->deleteExecute("todo_category", $whereParams);
    	if (!$result) {
			return 'error';
    	}

    	if (!$this->todoAction->deleteTodo()) {
        	return "error";
        }

		return "success";
    }
}
?>
