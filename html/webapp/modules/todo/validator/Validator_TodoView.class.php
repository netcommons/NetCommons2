<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todo参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Validator_TodoView extends Validator
{
    /**
     * Todo参照権限チェックバリデータ
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

		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");

		$request =& $container->getComponent("Request");
		$prefix_id_name = $request->getParameter("prefix_id_name");

		if ($authID < _AUTH_CHIEF &&
				$prefix_id_name == TODO_REFERENCE_PREFIX_NAME.$attributes['todo_id']) {
			return $errStr;
		}

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($prefix_id_name) &&
				$actionName == "todo_view_main_init") {
			$request =& $container->getComponent("Request");
			$request->setParameter("theme_name", "system");
		}

        $todoView =& $container->getComponent("todoView");
		if (empty($attributes['todo_id'])) {
			$todo = $todoView->getDefaultTodo();
		} elseif ($prefix_id_name == TODO_REFERENCE_PREFIX_NAME.$attributes['todo_id'] ||
					$actionName == "todo_view_edit_entry" || $actionName == "todo_action_edit_entry" || $actionName == "todo_view_edit_category_init") {
			$todo = $todoView->getTodo();
		} else {
			$todo = $todoView->getCurrentTodo();

		}

		if (empty($todo)) {
        	return $errStr;
        }

		if (!empty($attributes['todo_id']) && $authID < _AUTH_CHIEF && $todo['todo_id'] != $attributes['todo_id']) {
			return $errStr;
		}

		$request =& $container->getComponent("Request");
		$request->setParameter("todo", $todo);

        return;
    }
}
?>
