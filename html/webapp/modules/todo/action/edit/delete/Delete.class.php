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

    /**
     * Todo削除アクション
     *
     * @access  public
     */
    function execute()
    {
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
