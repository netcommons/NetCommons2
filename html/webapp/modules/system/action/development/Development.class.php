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

class System_Action_Development extends Action
{
	
	// リクエストパラメータを受け取るため
	var $php_debug = null;
	var $trace_log_level = null;
	var $sql_debug = null;
	var $smarty_debug = null;
	var $maple_debug = null;
	var $session_only = null;
	
	//使用コンポーネント
	var $config = null;
	var $session = null;
	
    /**
     * ログイン画面表示
     *
     * @access  public
     */
    function execute()
    {
        $this->session->setParameter("_php_debug", $this->php_debug);  
    	$this->session->setParameter("_trace_log_level", $this->trace_log_level);
		$this->session->setParameter("_sql_debug", $this->sql_debug);
		$this->session->setParameter("_smarty_debug", $this->smarty_debug);
		$this->session->setParameter("_maple_debug", $this->maple_debug);
		

		if ($this->session_only) {
			if (!$this->_update("use_db_debug", _OFF)) return 'error';
			$this->session->setParameter("_use_db_debug", _OFF);
		} else {
			if (!$this->_update("use_db_debug", _ON)) return 'error';
			$this->session->setParameter("_use_db_debug", _ON);
			
			$value = ($this->php_debug) ? $this->php_debug : _OFF;
			if (!$this->_update("php_debug", $value)) return 'error';
			
			$value = ($this->trace_log_level) ? $this->trace_log_level : LEVEL_ERROR;
			if (!$this->_update("trace_log_level", $value)) return 'error';
			
			$value = ($this->sql_debug) ? $this->sql_debug : _OFF;
			if (!$this->_update("sql_debug", $value)) return 'error';
			
			$value = ($this->smarty_debug) ? $this->smarty_debug : _OFF;
			if (!$this->_update("smarty_debug", $value)) return 'error';
			
			$value = ($this->maple_debug) ? $this->maple_debug : _OFF;
			if (!$this->_update("maple_debug", $value)) return 'error';
		}

    	return 'success';
    }
    
    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _DEBUG_CONF_CATID);
    	return $status;
    }
}
?>
