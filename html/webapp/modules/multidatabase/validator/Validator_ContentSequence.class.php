<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ番号チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_ContentSequence extends Validator
{
    /**
     * コンテンツ番号チェックバリデータ
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
        $mdbView =& $container->getComponent("mdbView");
        $sequences = $mdbView->getContentSequence();
		if (!$sequences) {
			return $errStr;	
		}
		
		$drag_content_id = $attributes["drag_content_id"];
		$drop_content_id = $attributes["drop_content_id"];

		if ($attributes["position"] == "top") {
			$sequences[$drop_content_id]--;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("drag_sequence", $sequences[$drag_content_id]);
		$request->setParameter("drop_sequence", $sequences[$drop_content_id]);
		
        return;
    }
}
?>