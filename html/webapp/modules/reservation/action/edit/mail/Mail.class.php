<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Action_Edit_Mail extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
	var $mail_send = null;
	var $mail_subject = null;
	var $mail_body = null;
	var $mail_authority = null;

    // 使用コンポーネントを受け取るため
	var $configAction = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->mail_send = intval($this->mail_send);
		$result = $this->configAction->updConfigValue($this->module_id, "mail_send", $this->mail_send);
		if ($result == false) {
			return 'error';
		}
		if ($this->mail_send == _ON) {
			$result = $this->configAction->updConfigValue($this->module_id, "mail_subject", $this->mail_subject);
			if ($result == false) {
				return 'error';
			}
			$result = $this->configAction->updConfigValue($this->module_id, "mail_body", $this->mail_body);
			if ($result == false) {
				return 'error';
			}
			$result = $this->configAction->updConfigValue($this->module_id, "mail_authority", $this->mail_authority);
			if ($result == false) {
				return 'error';
			}
		}
        return 'success';
    }
}
?>