<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール存在チェック
 * モジュールID(string or array)を引数にとり、そのモジュールがインストールされているかどうかをチェックする
 * 
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Validator_ModuleExists extends Validator
{
	var $_container;
    var $_getdata;
    var $_modulesView;
    
    /**
     * モジュール存在チェック
     *
     * @param   mixed   $attributes チェックする値(module_id or module_id_array)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$this->_container =& DIContainerFactory::getContainer();
    	$this->_getdata =& $this->_container->getComponent("GetData");
    	$this->_modulesView   =& $this->_container->getComponent("modulesView");
    	if (isset($attributes)) {
        	if(is_array($attributes)) {
        		foreach($attributes as $module_id) {
        			$module_id = intval($module_id);
        			$module =& $this->_getModule($module_id);
	        		if($module === false) {
	        			return $errStr;	
	        		}
        		}
        	} else {
        		$module_id = intval($attributes);
        		$module =& $this->_getModule($module_id);
        		if($module === false) {
        			return $errStr;	
        		}
        	}
        } else {
        	return $errStr;	
    	}
        return ;
    }
    
    function &_getModule($module_id) {
    	$modules = $this->_getdata->getParameter("modules");
		if(!isset($modules[$module_id])) {
			$module =& $this->_modulesView->getModulesById($module_id);
		} else {
			$module =& $modules[$module_id];
		}
		if(count($module) == 0) {
			$ret = false;
			return 	$ret;
		}
		return $module;
    }
}
?>