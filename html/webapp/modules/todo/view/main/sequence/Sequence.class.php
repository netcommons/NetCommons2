<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク表示順変更画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_View_Main_sequence extends Action
{
    // 使用コンポーネントを受け取るため
    var $todoView = null;

    // 値をセットするため
    var $tasks = null;
    
    /**
     * タスク表示順変更画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->tasks = $this->todoView->getTasks();
        if ($this->tasks == false) {
        	return "error";
        }

        return "success";
    }
}
?>
