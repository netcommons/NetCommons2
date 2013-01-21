<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_PhotoalbumExists extends Validator
{
    /**
     * フォトアルバム存在チェックバリデータ
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
		
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();		
		if (empty($attributes["photoalbum_id"]) &&
				($actionName == "photoalbum_view_edit_entry" ||
					$actionName == "photoalbum_action_edit_entry")) {
			return;
		}

        $photoalbumView =& $container->getComponent("photoalbumView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["photoalbum_id"])) {
        	$attributes["photoalbum_id"] = $photoalbumView->getCurrentPhotoalbumID();
        	$request->setParameter("photoalbum_id", $attributes["photoalbum_id"]);
		}

		if (empty($attributes["photoalbum_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $photoalbumView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$photoalbumView->photoalbumExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>