<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリスト件数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_LinklistCount extends Validator
{
    /**
     * リンクリスト件数チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if ($attributes["scroll"] == _ON) {
			return;
		}

		$container =& DIContainerFactory::getContainer();
        $linklistView =& $container->getComponent("linklistView");
		$linklistCount = $linklistView->getLinklistCount();
        if ($linklistCount == 0) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("linklistCount", $linklistCount);
		
        return;
    }
}
?>