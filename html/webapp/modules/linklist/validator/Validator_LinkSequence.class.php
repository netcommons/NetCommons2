<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンク番号チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_LinkSequence extends Validator
{
    /**
     * リンク番号チェックバリデータ
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
		$request =& $container->getComponent("Request");
        $linklistView =& $container->getComponent("linklistView");
        if (!empty($attributes["drop_category_id"])) {
        	$attributes["drop_link_id"] = $linklistView->getMaxLinkID();
        	if (empty($attributes["drop_link_id"])) {
        		$attributes["drop_link_id"] = "0";
        		$drop["category_id"] = $attributes["drop_category_id"];
        		$drop["link_sequence"] = "0";
        	}
        	$request->setParameter("drop_link_id", $attributes["drop_link_id"]);
        }

        $sequences = $linklistView->getLinkSequence();
		if (!$sequences) {
			return $errStr;	
		}
		
		$dragLinkID = $attributes["drag_link_id"];
		$dropLinkID = $attributes["drop_link_id"];
		
		if (empty($dropLinkID)) {
			$sequences[$dropLinkID] = $drop;
		}
		
		if ($attributes["position"] == "top") {
			$sequences[$dropLinkID]["link_sequence"]--;
		}
		
		$request->setParameter("drag", $sequences[$dragLinkID]);
		$request->setParameter("drop", $sequences[$dropLinkID]);
		
        return;
    }
}
?>