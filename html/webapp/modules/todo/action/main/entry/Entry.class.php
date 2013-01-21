<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Action_Main_Entry extends Action
{
	// リクエストパラメータを受け取るため
	var $task_id = null;
	var $progress = null;
	var $progressSuccess = null;
	var $state = null;

	// 使用コンポーネントを受け取るため
	var $todoAction = null;
	var $todoView = null;
	
	// 値をセットするため
	var $tasks = null;

	/**
	 * タスク登録アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if (!$this->todoAction->setTask()) {
			return 'error';
		}

		if ($this->state == _ON) {
			$this->progress = '100';
			$this->progressSuccess = _ON;
		}

		$this->progress = intval($this->progress);
		$this->progressSuccess = intval($this->progressSuccess);
		if ($this->progressSuccess == _ON) {
 			return 'progressSuccess';
		} else {
			return 'success';
		}
	}
}
?>