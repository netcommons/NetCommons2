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

class System_Action_Server extends Action
{

	// リクエストパラメータを受け取るため
	var $memory_limit = null;
	var $script_compress_gzip = null;
	var $use_mysession = null;
	var $session_name = null;
	var $proxy_mode = null;
	var $proxy_host = null;
	var $proxy_port = null;
	var $proxy_user = null;
	var $proxy_pass = null;
	var $use_permalink = null;
	var $ldap_uses = null;
	var $ldap_server = null;
	var $ldap_domain = null;

	//使用コンポーネント
	var $config = null;
	var $configView = null;
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

        $value = ($this->memory_limit) ? $this->memory_limit . 'M' : SYSTEM_DEFAULT_MEMROY_LIMIT;
    	if (!$this->_update('memory_limit', $value)) return 'error';

    	$value = ($this->script_compress_gzip) ? intval($this->script_compress_gzip) : _OFF;
    	if (!$this->_update('script_compress_gzip', $value)) return 'error';

/*
        if (intval($this->use_mysession) == _ON) {
        	if (!$this->session_name) {
        		$this->errorList->add(get_class($this), SYSTEM_ACTION_NO_SESSION_NAME);
        		return 'error';
        	}
*/
    	$value = ($this->session_name) ? $this->session_name : SYSTEM_DEFAULT_SESSION_NAME;
        if (!$this->_update('session_name', $value)) return 'error';
/*
        	if (!$this->_update('use_mysession', _ON)) return 'error';
        } else {
        	if (!$this->_update('use_mysession', _OFF)) return 'error';
        }
*/
        $value = ($this->proxy_host) ? $this->proxy_host : "";
        if (!$this->_update('proxy_host', $value)) return 'error';

        $value = ($this->proxy_port) ? $this->proxy_port : SYSTEM_DEFAULT_PROXY_PORT;
        if (!$this->_update('proxy_port', $this->proxy_port)) return 'error';

        if ($this->proxy_user) {
        	if (!$this->_update('proxy_user', $this->proxy_user))
        	    return 'error';
        	$value = ($this->proxy_pass != null) ? $this->proxy_pass : '';
        	if (!$this->_update('proxy_pass', $value)) return 'error';
        }

        $value = ($this->proxy_mode) ? _ON : _OFF;
        if (!$this->_update('proxy_mode', $value)) return 'error';

        // htaccess書き込み
        $value = ($this->use_permalink) ? _ON : _OFF;
        $config = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "use_permalink");
        $htaccess_path = START_INDEX_DIR . "/" . SYSTEM_HTACCESS_FILENAME;
        if(isset($config["conf_value"]) && $config["conf_value"] != $value && $value == _ON) {
        	$writing_flag = true;
	    	if(@file_exists($htaccess_path)) {
	    		@chmod($htaccess_path, 0777);
	    		if (!is_writeable($htaccess_path)) {
	    			// 書き込み不可
	    			$writing_flag = false;
	    		} else {
	    			// backup作成
	    			$time = timezone_date();
	    			@copy($htaccess_path, $htaccess_path.$time);
	    		}
	    	}
	    	if($writing_flag == true) {
	    		if (! $file = fopen($htaccess_path,"w") ) {
			        $writing_flag = false;
			    }
    		}
    		if($writing_flag == true) {
    			// 書き込み
    			$writing_data = "";
    			$url = parse_url(BASE_URL);
    			if ( isset( $url['path'] ) ) {
					$path = rtrim($url['path'], '/'). '/';
				} else {
					$path = '/';
				}
    			$htaccess_data_file_path = MODULE_DIR.'/system/config/'.SYSTEM_HTACCESS_DATA_FILENAME;

    			include_once $htaccess_data_file_path;

    			fwrite($file, $writing_data);
    			fclose($file);
    			@chmod($htaccess_path, 0444);	//0444
    		} else {
    			$this->errorList->add(get_class($this), SYSTEM_NO_WRITING);
        		return 'error';
    		}
        } else if(@file_exists($htaccess_path) && $config["conf_value"] != $value && $value == _OFF) {
        	@chmod($htaccess_path, 0777);
        	//unlink($htaccess_path);
        	$time = timezone_date();
	    	@rename($htaccess_path, $htaccess_path.$time);
        }

    	if (!$this->_update('use_permalink', $value)) return 'error';

    	//LDAP書き込み
    	$value = ($this->ldap_uses) ? _ON : _OFF;
        if (!$this->_update('ldap_uses', $value)) return 'error';

        $value = ($this->ldap_server) ? $this->ldap_server : '';
        if (!$this->_update('ldap_server', $value)) return 'error';

        $value = ($this->ldap_domain) ? $this->ldap_domain : '';
        if (!$this->_update('ldap_domain', $value)) return 'error';

    	return 'success';
    }

    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _SERVER_CONF_CATID);
    	return $status;
    }
}
?>
