<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 差出人、件名、キーワード相関チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_FilterRequired extends Validator
{
    /**
     * 差出人、件名、キーワード相関チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$senders = $attributes[0];
		$subject = $attributes[1];
		$keyword_list = $attributes[2];

		if (empty($senders) && empty($subject) && empty($keyword_list)) {
			return $errStr;
		}
			
        return;
  }
}
?>