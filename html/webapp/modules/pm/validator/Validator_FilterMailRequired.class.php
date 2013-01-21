<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メールアドレース必要チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_FilterMailRequired extends Validator
{
    /**
     * メールアドレース必要チェックバリデータ
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

		foreach($filter_actions_params as $key => $value){
			if(preg_match("/mail/", $key)){
				$key = (int)$key;	
				if(in_array($key,$filter_actions)){
					if (empty($value)) {
						return $errStr;
					}
				}
			}	
		}
			
        return;
  }
}
?>