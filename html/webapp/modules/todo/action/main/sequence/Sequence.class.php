<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク順序変更アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Action_Main_Sequence extends Action
{
    // 使用コンポーネントを受け取るため
    var $todoAction = null;

    /**
     * タスク順序変更アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->todoAction->updateTaskSequence()) {
			return "error";
		}
		
		return "success";
    }
}
?>