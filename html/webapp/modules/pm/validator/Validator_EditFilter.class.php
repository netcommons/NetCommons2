<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限設定チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_EditFilter extends Validator
{
    /**
     *  権限設定チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$filter_id = $attributes["filter_id"];
		
		if(empty($filter_id)){
			return;
		}
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
        $pmView =& $container->getComponent("pmView");
		
		$request->setParameter("filter_id", $filter_id);
		
		if (!$pmView->checkFilterAuth()) {
			return $errStr;
		}
		
        return;
    }
}
?>
