<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_MultidatabaseExists extends Validator
{
    /**
     * 汎用データベース存在チェックバリデータ
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
		if (empty($attributes["multidatabase_id"]) &&
				($actionName == "multidatabase_view_edit_create" ||
					$actionName == "multidatabase_action_edit_create")) {
			return;
		}

        $mdbView =& $container->getComponent("mdbView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["multidatabase_id"])) {
        	$attributes['multidatabase_id'] = $mdbView->getCurrentMdbId();
        	$request->setParameter("multidatabase_id", $attributes['multidatabase_id']);
		}

		if (empty($attributes['multidatabase_id'])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $mdbView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$mdbView->mdbExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>