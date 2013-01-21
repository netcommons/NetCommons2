<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンク存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_LinkExists extends Validator
{
    /**
     * リンク存在チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["link_id"])) {
			return $errStr;
		}
		
		$container =& DIContainerFactory::getContainer();
        $linklistView =& $container->getComponent("linklistView");
		if (!$linklistView->linkExists()) {
			return $errStr;
		}
 
        return;
    }
}
?>
