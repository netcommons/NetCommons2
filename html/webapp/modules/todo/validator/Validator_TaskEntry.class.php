<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク登録権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Validator_TaskEntry extends Validator
{
    /**
     * タスク登録権限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (!$attributes["todo"]["task_authority"]) {
			return $errStr;
		} 
		
		$container =& DIContainerFactory::getContainer();
        $todoView =& $container->getComponent("todoView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["task_id"])) {
			$task = $todoView->getDefaultTask();
		} else {
			$task = $todoView->getTask();
		}
		if (empty($task)) {
			return $errStr;
		}
		$request->setParameter("task", $task);
		
		if (empty($attributes["task_id"])) {
			return;
		}

		if ($attributes["todo"]["todo_id"] != $task["todo_id"]) {
			return $errStr;
		}
		if (!$task["edit_authority"]) {
			return $errStr;
		}
 
        return;
    }
}
?>
