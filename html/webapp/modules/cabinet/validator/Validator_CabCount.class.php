<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネット件数チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_CabCount extends Validator
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
		if ($attributes["scroll"] == _ON) {
			return;
		}

		$container =& DIContainerFactory::getContainer();
        $cabinetView =& $container->getComponent("cabinetView");
		$cabinetCount = $cabinetView->getCabCount();
        if ($cabinetCount == 0) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("cabinetCount", $cabinetCount);
		
        return;
    }
}
?>