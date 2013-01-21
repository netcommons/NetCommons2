<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todo存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Validator_TodoExists extends Validator
{
    /**
     * Todo存在チェックバリデータ
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

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();		
		if (empty($attributes["todo_id"]) &&
				($actionName == "todo_view_edit_entry" ||
					$actionName == "todo_action_edit_entry")) {
			return;
		}

        $todoView =& $container->getComponent("todoView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["todo_id"])) {
			$session =& $container->getComponent("Session");
			$session->removeParameter("todo_edit". $attributes["block_id"]);

        	$attributes["todo_id"] = $todoView->getCurrentTodoID();
        	$request->setParameter("todo_id", $attributes["todo_id"]);
		}

		if (empty($attributes["todo_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $todoView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$todoView->todoExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>