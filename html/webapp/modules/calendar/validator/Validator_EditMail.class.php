<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール設定チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_EditMail extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		list($mail_send, $mail_subject, $mail_body, $mail_authority) = $attributes;
		if ($mail_send == _OFF) {
			return;
		}
		if (empty($mail_subject)) {
			return sprintf($errStr, CALENDAR_TITLE_MAIL_SUBJECT);
		}
		if (empty($mail_body)) {
			return sprintf($errStr, CALENDAR_TITLE_MAIL_BODY);
		}

		if (!is_array($mail_authority)) {
			return sprintf($errStr, CALENDAR_TITLE_MAIL_AUTHORITY);
		}
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		$authorityID = min(array_keys($mail_authority));
		$request->setParameter("mail_authority", $authorityID);
    }
}
?>
