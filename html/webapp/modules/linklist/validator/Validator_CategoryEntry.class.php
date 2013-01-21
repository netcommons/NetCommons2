<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ登録権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_CategoryEntry extends Validator
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
		if (!$attributes["linklist"]["category_authority"]) {
			return $errStr;
		} 
		
		if (empty($attributes["category_id"])) {
			return;
		}	
		
		$container =& DIContainerFactory::getContainer();
        $linklistView =& $container->getComponent("linklistView");

		$category = $linklistView->getCategory();
		if (empty($category)) {
			return $errStr;
		}
		
		if ($attributes["linklist"]["linklist_id"] != $category["linklist_id"]) {
			return $errStr;
		}
		if (!$category["edit_authority"]) {
			return $errStr;
		}
 
        return;
    }
}
?>
