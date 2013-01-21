<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク番号チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Validator_TaskSequence extends Validator
{
    /**
     * タスク番号チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
        $container =& DIContainerFactory::getContainer();
        $todoView =& $container->getComponent("todoView");
        $sequences = $todoView->getTaskSequence();
		if (!$sequences) {
			return $errStr;	
		}
		
		$dragTaskID = $attributes["drag_task_id"];
		$dropTaskID = $attributes["drop_task_id"];

		if ($attributes["position"] == "top") {
			$sequences[$dropTaskID]--;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("drag_sequence", $sequences[$dragTaskID]);
		$request->setParameter("drop_sequence", $sequences[$dropTaskID]);
		
        return;
    }
}
?>