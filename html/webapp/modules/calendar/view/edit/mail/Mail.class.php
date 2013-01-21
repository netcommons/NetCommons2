<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール設定の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Edit_Mail extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;

    // 使用コンポーネントを受け取るため
	var $configView = null;

    // 値をセットするため
	var $mail_send = null;
	var $mail_subject = null;
	var $mail_body = null;
	var $mail_authority = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$config = $this->configView->getConfig($this->module_id, false);
		if ($config === false) {
    		return false;
    	}
    	if (defined($config["mail_send"]["conf_value"])) {
    		$this->mail_send = constant($config["mail_send"]["conf_value"]);
    	} else {
    		$this->mail_send = intval($config["mail_send"]["conf_value"]);
    	}
    	if (defined($config["mail_subject"]["conf_value"])) {
    		$this->mail_subject = constant($config["mail_subject"]["conf_value"]);
    	} else {
    		$this->mail_subject = $config["mail_subject"]["conf_value"];
    	}
   		$this->mail_body = $config["mail_body"]["conf_value"];

    	if (defined($config["mail_authority"]["conf_value"])) {
    		$this->mail_authority = constant($config["mail_authority"]["conf_value"]);
    	} else {
    		$this->mail_authority = $config["mail_authority"]["conf_value"];
    	}
       	return 'success';
    }
}
?>