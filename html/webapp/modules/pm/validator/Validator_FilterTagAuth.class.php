<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フィルタのタグ権限バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_FilterTagAuth extends Validator
{
    /**
     *フィルタのタグ権限バリデータ
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
 		$request =& $container->getComponent("Request");
 		$pmView =& $container->getComponent("pmView");
		
		$filter_actions = $attributes["0"];
		$filter_actions_params = $attributes["1"];
		
		if(is_array($filter_actions_params)){
			foreach($filter_actions_params as $k => $v){
				if(preg_match("/tag/", $k)){
					$k = (int)$k;
					if(is_array($filter_actions) && in_array($k, $filter_actions)) {
						$request->setParameter("tag_id", $v);
						if(!empty($v) && !$pmView->checkTagAuth()){
							return $errStr;
						}
					}
				}
			}
		}
		
        return;
    }
}
?>