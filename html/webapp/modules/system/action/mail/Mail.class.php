<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システムConfig登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class System_Action_Mail extends Action
{
	
	// リクエストパラメータを受け取るため
	var $from = null;
	var $fromname = null;
	var $htmlmail = null;
	var $mailmethod = null;
	var $smtphost = null;
	var $smtpuser = null;
	var $smtppass = null;
	var $sendmailpath = null;
	
	//使用コンポーネント
	var $config = null;
	var $actionChain = null;
	
	// 値をセットするため
	var $errorList = null;
	
    /**
     * DB登録
     *
     * @access  public
     */
    function execute()
    { 
        $this->errorList =& $this->actionChain->getCurErrorList();

    	$method = ($this->mailmethod) ? $this->mailmethod : SYSTEM_DEFAULT_MAIL_METHOD;
		if (preg_match('/^smtp.*/', $method)) {
			// DNS, IP address, alias, Internatinal Domain..
			if (!$this->smtphost) {
				$this->errorList->add(get_class($this), SYSTEM_ACTION_NO_SMTPHOST);
        		return 'error';
			}
			// no check for smtppass because of no password possible
			if ($method == 'smtpauth') {
				if (!$this->smtpuser) {
					$this->errorList->add(get_class($this), SYSTEM_ACTION_NO_SMTPUSER);
        			return 'error';
				}
			}
		} else if ($method == "sendmail") {
			if (!$this->sendmailpath) {
				$this->errorList->add(get_class($this), SYSTEM_ACTION_NO_SENDMAILPATH);
        		return 'error';
			}
		}
		
		if ($this->from == null) return 'error'; // validated. see maple.ini.
		if (!$this->_update('from', $this->from)) return 'error';
		
		$value = ($this->fromname) ? $this->fromname : $this->from;
		if (!$this->_update('fromname', $value)) return 'error';
		
		$value = ($this->htmlmail) ? _ON : _OFF;
		if (!$this->_update('htmlmail', $value)) return 'error';
		
		if (!$this->_update('mailmethod', $method)) return 'error';
		
		$value = ($this->smtphost ? $this->smtphost : '');
		if (!$this->_update('smtphost', $value)) return 'error';

		$value = ($this->smtpuser ? $this->smtpuser : '');
		if (!$this->_update('smtpuser', $value)) return 'error';

		$value = ($this->smtppass ? $this->smtppass : '');
		if (!$this->_update('smtppass', $value)) return 'error';
		
		$value = ($this->sendmailpath ? $this->sendmailpath : SYSTEM_DEFAULT_MAIL_SENDMAILPATH);
		if (!$this->_update('sendmailpath', $value)) return 'error';
		
    	return 'success';
    }
    
    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _MAIL_CONF_CATID);
    	return $status;
    }
}
?>
