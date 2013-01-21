<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_ItemView extends Validator
{
    /**
     * 項目参照権限チェックバリデータ
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

		if (empty($attributes["item_id"])) {
			$item = $registrationView->getDefaultItem();
		} else {
			$item = $registrationView->getItem();
		}
		if (empty($item)) {
        	return $errStr;
        }

        if (!empty($attributes["item_id"])
        		&& $item["registration_id"] != $attributes["registration_id"]) {
        	return $errStr;
        }
		        
		$request =& $container->getComponent("Request");
    	$request->setParameter("item", $item);

        return;
    }
}
?>