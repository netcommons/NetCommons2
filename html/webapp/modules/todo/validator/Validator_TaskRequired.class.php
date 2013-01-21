<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク必須項目チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Validator_TaskRequired extends Validator
{
    /**
     * タスク必須項目チェックバリデータ
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
		$filterChain =& $container->getComponent("FilterChain"); 
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
        	
    	if (isset($attributes["task_value"])
				&& empty($attributes["task_value"])) {
			$errStr = sprintf($errStr, $smartyAssign->getLang("todo_task_value"));
			return $errStr;
		} 
 
        return;
    }
}
?>
