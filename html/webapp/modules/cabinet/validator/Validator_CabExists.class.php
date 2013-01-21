<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネット存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_CabExists extends Validator
{
    /**
     * validate実行
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

        $cabinetView =& $container->getComponent("cabinetView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["cabinet_id"])) {
        	$attributes["cabinet_id"] = $cabinetView->getCurrentCabinetID();
        	$request->setParameter("cabinet_id", $attributes["cabinet_id"]);
		}

		if (empty($attributes["cabinet_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $cabinetView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$cabinetView->cabExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>
