<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メールアドレースチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_FilterMailFormat extends Validator
{
    /**
     * メールアドレースチェックバリデータ
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
				if (empty($value)) {
					return;
				}
				if (preg_match("/^[a-zA-Z0-9\"\._\?\+\/-]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $value)) {
					return;
				} else {
					return $errStr;
				}
			}	
		}
			
        return;
  }
}
?>