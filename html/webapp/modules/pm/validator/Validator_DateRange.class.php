<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日付範囲チェックバリデータクラ
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_DateRange extends Validator
{
    /**
     * 日付チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$search_date_from = $attributes[0];
		$search_date_to = $attributes[1];
		
		if ($search_date_from == "" || $search_date_to == "") {
			return;
		}
		
		if ($search_date_to < $search_date_from) {
			return $errStr;
		}
		
        return;
  }
}
?>