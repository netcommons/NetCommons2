<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク登録画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_View_Main_Entry extends Action
{
    // リクエストパラメータを受け取るため
    var $item_value = null;
    var $target_state = null;

    // validatorから受け取るため
    var $todo = null;
    var $task = null;

    // 使用コンポーネントを受け取るため
    var $todoView = null;

    // 値をセットするため
    var $todo_id = 0;
    var $categories = array();
    var $used_category = 0;

    /**
     * タスク登録画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->todo_id = $this->todo["todo_id"];

		if ($this->todo["used_category"] == _ON) {
			$this->categories = $this->todoView->getCategories($this->todo_id);
			if (empty($this->categories)) {
				return 'error';
			}
		}
		if ($this->todo["used_category"] == _OFF ||  count($this->categories) <= 1) {
			$this->used_category = _ON;
		}

        return "success";
    }
}
?>