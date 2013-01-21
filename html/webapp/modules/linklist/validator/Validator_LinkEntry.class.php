<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンク登録権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_LinkEntry extends Validator
{
    /**
     * リンク登録権限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (!$attributes["linklist"]["link_authority"]) {
			return $errStr;
		} 
		
		$container =& DIContainerFactory::getContainer();
        $linklistView =& $container->getComponent("linklistView");

		if (!empty($attributes["category_id"])
				&& !$linklistView->categoryExists()) {
			return $errStr;
		}
		
		if (empty($attributes["link_id"])) {
			return;
		}	
				
		$link = $linklistView->getLink();
		if (empty($link)) {
			return $errStr;
		}
		if ($attributes["linklist"]["linklist_id"] != $link["linklist_id"]) {
			return $errStr;
		}
		if (!$link["edit_authority"]) {
			return $errStr;
		}
 
        return;
    }
}
?>
