<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目番号チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_ItemSequence extends Validator
{
    /**
     * 項目番号チェックバリデータ
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
        $registrationView =& $container->getComponent("registrationView");
        $sequences = $registrationView->getItemSequence();
		if (!$sequences) {
			return $errStr;	
		}
		
		$dragItemID = $attributes["drag_item_id"];
		$dropItemID = $attributes["drop_item_id"];

		if ($attributes["position"] == "top") {
			$sequences[$dropItemID]--;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("drag_sequence", $sequences[$dragItemID]);
		$request->setParameter("drop_sequence", $sequences[$dropItemID]);
		
        return;
    }
}
?>