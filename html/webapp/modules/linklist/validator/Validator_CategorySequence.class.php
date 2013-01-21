<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ番号チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_CategorySequence extends Validator
{
    /**
     * カテゴリ番号チェックバリデータ
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
        
        $container =& DIContainerFactory::getContainer();
        $linklistView =& $container->getComponent("linklistView");
        $sequences = $linklistView->getCategorySequence();
		if (!$sequences) {
			return $errStr;	
		}
		
		$dragCategoryID = $attributes["drag_category_id"];
		$dropCategoryID = $attributes["drop_category_id"];

		if ($attributes["position"] == "top") {
			$sequences[$dropCategoryID]--;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("drag_sequence", $sequences[$dragCategoryID]);
		$request->setParameter("drop_sequence", $sequences[$dropCategoryID]);
		
        return;
    }
}
?>