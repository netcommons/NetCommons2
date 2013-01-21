<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todo登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Action_Edit_Entry extends Action
{
	// リクエストパラメータを受け取るため
	var $todo_id = null;

    // 使用コンポーネントを受け取るため
    var $todoAction = null;
	
	/**
     * Todo登録アクション
     *
     * @access  public
     */
    function execute()
	{
		if (!$this->todoAction->setTodo()) {
        	return "error";
        }

		if (empty($this->todo_id)) {
			return "create";
		}
		
		return "modify";
	}
}
?>