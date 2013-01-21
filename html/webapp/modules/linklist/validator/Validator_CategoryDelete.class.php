<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ削除権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_CategoryDelete extends Validator
{
    /**
     * カテゴリ登録権限チェックバリデータ
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
		if ($authID >= _AUTH_CHIEF) {
			return;
		}
		
		$container =& DIContainerFactory::getContainer();
        $linklistView =& $container->getComponent("linklistView");

		$linkCount = $linklistView->getLinkCount();
		if ($linkCount > 0) {
			return $errStr;
		}
 
        return;
    }
}
?>
