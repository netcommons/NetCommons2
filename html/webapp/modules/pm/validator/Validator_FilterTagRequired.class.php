<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タグ必須入力相関チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_FilterTagRequired extends Validator
{
    /**
     * タグ必須入力相関チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		
		$filter_actions = $attributes["0"];
		$filter_actions_params = $attributes["1"];
		
		if(is_array($filter_actions_params)){
			foreach($filter_actions_params as $k => $v){
				if(preg_match("/tag/", $k)){
					$k = (int)$k;
					if(is_array($filter_actions) && in_array($k, $filter_actions)) {
						if ($v == 0) {
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