<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール件名チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_MailSubject extends Validator
{
    /**
     * メール件名チェックバリデータ
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
		
		if (strlen(bin2hex($attributes["mail_subject"])) / 2 <= $params[0]) {
            return;
        } else {
            return $errStr;
        }
    }
}
?>