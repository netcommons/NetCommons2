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

class Security_Action_Security extends Action
{

	// リクエストパラメータを受け取るため
	var $security_check_set = null;
	var $security_level = null;
	var $log_level = null;
	var $san_nullbyte = null;
	var $contami_action = null;
	var $isocom_action = null;
	var $union_action = null;
	var $file_dotdot = null;
	var $dos_expire = null;
	var $dos_f5count = null;
	var $dos_f5action = null;
	var $reliable_ips = null;
	var $dos_crcount = null;
	var $dos_craction = null;
	var $dos_crsafe = null;
	var $bip_except = null;
	var $bip_except_admin = null;
	var $bip_except_chief = null;
	var $bip_except_moderate = null;
	var $bip_except_general = null;
	var $bip_except_guest = null;
	var $enable_badips = null;
	var $bad_ips = null;
	var $censor_enable = null;
	var $censor_words = null;
	var $censor_replace = null;
/*
	var $rejectionAttach_action = null;
*/
	var $extension = null;
	var $allow_extension = null;
	var $deny_extension = null;
/*
	var $imageCheck_action = null;
*/
	var $passwd_disabling_bip = null;
	var $repasswd_disabling_bip = null;
/*
	var $db_prefix = null;
*/
	var $groups_denyipmove = null;
	var $groups_denyipmove_admin = null;
	var $groups_denyipmove_chief = null;
	var $groups_denyipmove_moderate = null;
	var $groups_denyipmove_general = null;
	var $groups_denyipmove_guest = null;

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

        // sanity check for null value
        $value = ($this->security_check_set == _OFF) ? _OFF : ($this->security_level);
		if (!$this->_update('security_level', intval($value))) return 'error';

        $value = ($this->log_level);
		if (!$this->_update('log_level', intval($value))) return 'error';

		$value = ($this->san_nullbyte);
		if (!$this->_update('san_nullbyte', intval($value))) return 'error';

		$value = ($this->contami_action);
		if (!$this->_update('contami_action', intval($value))) return 'error';

		$value = ($this->isocom_action);
		if (!$this->_update('isocom_action', intval($value))) return 'error';

		$value = ($this->union_action);
		if (!$this->_update('union_action', intval($value))) return 'error';

		$value = ($this->file_dotdot);
		if (!$this->_update('file_dotdot', intval($value))) return 'error';

		$value = ($this->dos_expire)? $this->dos_expire : SECURITY_DEFAULT_DOS_EXPIRE;
		if (!$this->_update('dos_expire', intval($value))) return 'error';

		$value = ($this->dos_f5count) ? $this->dos_f5count : SECURITY_DEFAULT_DOS_F5COUNT;
		if (!$this->_update('dos_f5count', intval($value))) return 'error';

		$value = ($this->dos_f5action);
		if (!$this->_update('dos_f5action', intval($value))) return 'error';

		$value = "";
		if(!empty($this->reliable_ips)) {
			$value = explode("|", (($this->reliable_ips) ? $this->reliable_ips : SECURITY_DEFAULT_RELIABLE_IPS));
			if (!$this->_update('reliable_ips', serialize($value))) return 'error';
		} else {
			if (!$this->_update('reliable_ips', $value)) return 'error';
		}

		$value = ($this->dos_crcount) ? $this->dos_crcount : SECURITY_DEFAULT_DOS_CRCOUNT;
		if (!$this->_update('dos_crcount', intval($value))) return 'error';

		$value = ($this->dos_craction);
		if (!$this->_update('dos_craction', intval($value))) return 'error';

		$value = ($this->dos_crsafe) ? $this->dos_crsafe : SECURITY_DEFAULT_DOS_CRSAFE;
		if (!$this->_update('dos_crsafe', $value)) return 'error';

		$bip_except = array();
		$value = ($this->bip_except_admin) ? $this->bip_except_admin : SECURITY_DEFAULT_BIP_EXCEPT_ADMIN;
		if ($value == _ON) $bip_except[] = strval(_AUTH_ADMIN);
		$value = ($this->bip_except_chief) ? $this->bip_except_chief : SECURITY_DEFAULT_BIP_EXCEPT_CHIEF;
		if ($value == _ON) {
			$bip_except[] = strval(_AUTH_CHIEF);
		}
		$value = ($this->bip_except_moderate) ? $this->bip_except_moderate : SECURITY_DEFAULT_BIP_EXCEPT_MODERATE;
		if ($value == _ON) {
			$bip_except[] = strval(_AUTH_MODERATE);
		}
		$value = ($this->bip_except_general) ? $this->bip_except_general : SECURITY_DEFAULT_BIP_EXCEPT_GENERAL;
		if ($value == _ON) {
			$bip_except[] = strval(_AUTH_GENERAL);
		}
		$value = ($this->bip_except_guest) ? $this->bip_except_guest : SECURITY_DEFAULT_BIP_EXCEPT_GUEST;
		if ($value == _ON) {
			$bip_except[] = strval(_AUTH_GUEST);
		}
		if (empty($bip_except)) {
			$bip_except = "";
			if (!$this->_update('bip_except', $bip_except)) return 'error';
		} else {
			if (!$this->_update('bip_except', serialize($bip_except))) return 'error';
		}

		$value = ($this->enable_badips);
		if (!$this->_update('enable_badips', intval($value))) return 'error';

		$value = "";
		if(!empty($this->bad_ips)) {
			$value = explode("|", (($this->bad_ips) ? $this->bad_ips : SECURITY_DEFAULT_BAD_IPS));
			if (!$this->_update('bad_ips', serialize($value))) return 'error';
		} else {
			if (!$this->_update('bad_ips', $value)) return 'error';
		}

		$value = ($this->censor_enable);
		if (!$this->_update('censor_enable', intval($value))) return 'error';

		$value = ($this->censor_words) ? $this->censor_words : SECURITY_DEFAULT_CENSOR_WORDS;
		if (!$this->_update('censor_words', $value)) return 'error';

		$value = ($this->censor_replace) ? $this->censor_replace : SECURITY_DEFAULT_CENSOR_REPLACE;
		if (!$this->_update('censor_replace', $value)) return 'error';

/*
		$value = ($this->extension) ? $this->extension : SECURITY_DEFAULT_EXTENSION;
		if (!$this->_update('extension', $value)) return 'error';
*/

		$value = ($this->allow_extension) ? $this->allow_extension : SECURITY_DEFAULT_ALLOW_EXTENSION;
		if (!$this->_update('allow_extension', $value)) return 'error';

/*
		$value = ($this->deny_extension) ? $this->deny_extension : SECURITY_DEFAULT_DENY_EXTENSION;
		if (!$this->_update('deny_extension', $value)) return 'error';
*/

		$groups_denyipmove = array();
		$value = ($this->groups_denyipmove_admin) ? $this->groups_denyipmove_admin : SECURITY_DEFAULT_GROUPS_DENYIPMOVE_ADMIN;
		if ($value == _ON) $groups_denyipmove[] = strval(_AUTH_ADMIN);
		$value = ($this->groups_denyipmove_chief) ? $this->groups_denyipmove_chief : SECURITY_DEFAULT_GROUPS_DENYIPMOVE_CHIEF;
		if ($value == _ON) {
			$groups_denyipmove[] = strval(_AUTH_CHIEF);
		}
		$value = ($this->groups_denyipmove_moderate) ? $this->groups_denyipmove_moderate : SECURITY_DEFAULT_GROUPS_DENYIPMOVE_MODERATE;
		if ($value == _ON) {
			$groups_denyipmove[] = strval(_AUTH_MODERATE);
		}
		$value = ($this->groups_denyipmove_general) ? $this->groups_denyipmove_general : SECURITY_DEFAULT_GROUPS_DENYIPMOVE_GENERAL;
		if ($value == _ON) {
			$groups_denyipmove[] = strval(_AUTH_GENERAL);
		}
		$value = ($this->groups_denyipmove_guest) ? $this->groups_denyipmove_guest : SECURITY_DEFAULT_GROUPS_DENYIPMOVE_GUEST;
		if ($value == _ON) {
			$groups_denyipmove[] = strval(_AUTH_GUEST);
		}
		if (empty($groups_denyipmove)) {
			$groups_denyipmove = "";
			if (!$this->_update('groups_denyipmove', $groups_denyipmove)) return 'error';
		} else {
			if (!$this->_update('groups_denyipmove', serialize($groups_denyipmove))) return 'error';
		}

        $value = ($this->passwd_disabling_bip) ? $this->passwd_disabling_bip : SECURITY_DEFAULT_PASSWD_DISABLING_BIP;
        $password = ($this->repasswd_disabling_bip) ? $this->repasswd_disabling_bip : SECURITY_DEFAULT_PASSWD_DISABLING_BIP;
        if (strcmp($value, $password) != 0) {
    		$this->errorList->add(get_class($this), sprintf(RESCUE_PASSWORD_ERROR));
	        return 'error';
        }
        if (!$this->_update('passwd_disabling_bip', $value)) return 'error';

        return 'success';
    }

    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _SECURITY_CONF_CATID);
    	if (!$status) {
    		$this->errorList->add(get_class($this), sprintf(_INVALID_UPDATEDB, $name));
    	}
    	return $status;
    }
}
?>
