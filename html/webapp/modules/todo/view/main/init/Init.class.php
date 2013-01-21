<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todoメイン画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Todo_View_Main_Init extends Action
{
	// パラメータを受け取るため
	var $block_id = null;
	var $target_state = null;
	var $all_view = null;
	var $sort_col = null;
	var $sort_dir = null;

	// 使用コンポーネントを受け取るため
	var $todoView = null;
	var $session = null;
	var $request = null;
	var $filterChain = null;
	var $mobileView = null;

	// validatorから受け取るため
	var $todo = null;

    // 値をセットするため
    var $todo_id = 0;
    var $categories = array();
	var $taskCount = null;
	var $visibleRows = null;
	var $tasks = null;
	var $showChangeSequence = null;

	var $block_num = null;

	/**
	 * Todoメイン画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if (!isset($this->target_state)) {
			if (!isset($this->all_view)) {
				$all_view = $this->session->getParameter(array("todo", $this->block_id, "all_view"));
			} else {
				$all_view = intval($this->all_view);
			}
			if (!isset($all_view)) {
				$all_view = _OFF;
			}
			if ($all_view == _ON) {
				$this->target_state = null;
			} else {
				$this->target_state = _OFF;
			}
			$this->session->setParameter(array("todo", $this->block_id, "all_view"), $all_view);
			$this->request->setParameter("target_state", $this->target_state);
		}
		if (!isset($this->sort_col)) {
			$this->sort_col = $this->session->getParameter(array("todo", $this->block_id, "sort_col"));
			$this->request->setParameter("sort_col", $this->sort_col);
		} else {
			$this->session->setParameter(array("todo", $this->block_id, "sort_col"), $this->sort_col);
		}
		if (!isset($this->sort_dir)) {
			$this->sort_dir = $this->session->getParameter(array("todo", $this->block_id, "sort_dir"));
			$this->request->setParameter("sort_dir", $this->sort_dir);
		} else {
			$this->session->setParameter(array("todo", $this->block_id, "sort_dir"), $this->sort_dir);
		}

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		$this->taskCount = $this->todoView->getTaskCount();
		if ($this->taskCount === false) {
			return "error";
		}

		$this->tasks = $this->todoView->getTasks();
		if ($this->tasks === false) {
			return "error";
		}

		if (!empty($this->tasks)
				&& $this->todo["task_authority"]
				&& $this->todo["used_category"] == _OFF
				&& $this->todo["default_sort"] == TODO_NONE) {
			$this->showChangeSequence = true;
		}

		$_id = $this->session->getParameter("_id");
	 	$this->session->setParameter(array("todo", "_id", $this->block_id), $_id);

		$this->todo_id = $this->todo["todo_id"];
		if ($this->todo["used_category"] == _ON) {
			$this->categories = $this->todoView->getCategories($this->todo_id);
			if (empty($this->categories)) {
				return 'error';
			}
		}

	 	return "screen";
	}
}
?>