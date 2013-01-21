<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答一覧参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_SummaryListView extends Validator
{
    /**
     * 解答一覧参照権限チェックバリデータ
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
		
		if (empty($attributes["answer_user_id"]) &&
				$session->getParameter("_auth_id") < _AUTH_CHIEF) {
			return $errStr;
		}

		if (!empty($attributes["answer_user_id"]) &&
				$attributes["answer_user_id"] != $session->getParameter("_user_id")) {
			return $errStr;
		} 

        return;
    }
}
?>