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
class Todo_Validator_CategoryExists extends Validator
{
    /**
     * カテゴリ存在チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	if (!isset($attributes["prefix_id_name"])) {
    		return;
    	}

		if ($attributes["category_id"] !== "0" && empty($attributes["category_id"])) {
			if ($attributes["prefix_id_name"] == TODO_CATEGORY_ADD_PREFIX_NAME || $attributes["prefix_id_name"] == TODO_TASK_ADD_PREFIX_NAME) {
				return;
			} else {
				return $errStr;
			}
		}
    	if (!empty($attributes["todo"])) {
    		$attributes["todo_id"] = $attributes["todo"]["todo_id"];
    	}

		$container =& DIContainerFactory::getContainer();
		$todoView =& $container->getComponent("todoView");
		$count = $todoView->getCategoryCount($attributes["todo_id"], $attributes["category_id"]);
        if (empty($count)) {
			return $errStr;
		}

		return;
    }
}
?>