<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール送信先チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_MailSend extends Validator
{
    /**
     * メール送信先チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["mail_send"])) {
			return;
		}
		
		if (empty($attributes["regist_user_send"])
			&& empty($attributes["chief_send"])
			&& empty($attributes["rcpt_to"])) {
			return $errStr;
		}
		
		return;
    }
}
?>