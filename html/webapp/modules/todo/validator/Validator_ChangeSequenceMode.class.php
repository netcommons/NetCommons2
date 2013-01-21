<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示順編集画面権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Validator_ChangeSequenceMode extends Validator
{
    /**
     * 表示順編集画面権限チェックバリデータ
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
    	if ($session->getParameter("_auth_id") < _AUTH_CHIEF) {
    		return _INVALID_AUTH;
    	}

		if (!$attributes["todo"]["task_authority"]) {
			return $errStr;
		}
 		
 		if ($attributes["todo"]["default_sort"] != TODO_NONE) {
			return $errStr;
		}

		return;
    }
}
?>
