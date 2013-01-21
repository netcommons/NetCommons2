<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール送信先アドレスチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_RcptToAddress extends Validator
{
    /**
     * メール送信先アドレスチェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["rcpt_to"])) {
			return;
		}
		
		$rcptToes = explode(REGISTRATION_RCPT_TO_SEPARATOR, $attributes["rcpt_to"]);
		foreach ($rcptToes as $rcptTo) {
			if (!preg_match("/^[a-zA-Z0-9\"\._\?\+\/-]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $rcptTo)) {
            	return $errStr;
			}
		}
    }
}
?>