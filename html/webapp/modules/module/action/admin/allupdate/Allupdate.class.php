<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *  一括アップデート処理：グローバルhtdocsファイルコピー
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Module_Action_Admin_Allupdate extends Action
{
	// リクエストパラメータを受け取るため
	var $install_flag = null;
	
	//使用コンポーネント
	var $modulesView = null;
	var $moduleCompmain = null;
	var $configAction = null;
	
	//結果文字列
	var $result_str = "";
	var $module_name = null;
    
    /**
     * 一括アップデート処理：グローバルhtdocsファイルコピー
     *
     * @access  public
     */
    function execute()
    {
    	set_time_limit(MODULE_UPDATE_TIME_LIMIT);
    	$return_prefix = "";
    	if($this->install_flag == _ON) {
    		// インストーラから呼ばれた場合
    		$return_prefix = "install_";
    	}
    	$this->module_name = MODULE_GLOBAL_RESULT_TITLE;
    	//処理開始
    	$this->_setMes(MODULE_MES_GLOBAL_RESULT_START);
    	// ----------------------------------------------
		// --- CSSのhtdocsへのコピー				  ---
		// ----------------------------------------------
		$modules_dir = array(
			"common",
			"pages",
			"control",
			"comp",
			"dialog"
		);
		$this->_setMes(MODULE_MES_RESULT_REGISTJS_ST,1);
		if(!$this->moduleCompmain->registCommonFile($modules_dir)) {
			$this->_setMes(MODULE_MES_RESULT_REGISTJS_ER,1);
		} else {
			$this->_setMes(MODULE_MES_RESULT_REGISTJS_EN,1);
		}
		foreach($modules_dir as $dirname) {
			if(!$this->moduleCompmain->delHtdocsFilesByDirname($dirname,$this)) {
				$this->_setMes(MODULE_MES_GLOBAL_RESULT_ER);
				return $return_prefix . 'error';
			}
			if(!$this->moduleCompmain->copyHtdocsFilesByDirname($dirname,$this)) {
				$this->_setMes(MODULE_MES_GLOBAL_RESULT_ER);
				return $return_prefix . 'error';
			}
		}
		// ------------------------------------------------------------------------------
		// --- themeファイルのコピー処理			                                  ---
		// --- TODO:テーマ管理で行うほうが望ましいが、現状、こちらで実装			  ---
		// ------------------------------------------------------------------------------
		if(!$this->moduleCompmain->copyThemesFiles($this)) {
			$this->_setMes(MODULE_MES_GLOBAL_RESULT_ER);
			return $return_prefix . 'error';
		}
		// ----------------------------------------------
		// --- キャッシュクリア		 ---
		// ----------------------------------------------
		$cachemodules_dir = array(
			"pages",
			"control",
			"dialog"
		);
		foreach($cachemodules_dir as $dirname) {
			if($this->moduleCompmain->clearCacheByDirname($dirname)) {
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELCACHE_EN,"[".$dirname."]"),1);
			}
		}
		if($this->moduleCompmain->clearCacheMainByDirname()) {
			$this->_setMes(sprintf(MODULE_MES_RESULT_DELCACHE_EN,"["."main"."]"),1);
		}
		
		// ----------------------------------------------
		// --- バージョン情報書き換え　　　　		 ---
		// ----------------------------------------------
		$result = $this->configAction->updConfigValue(_SYS_CONF_MODID, "version", _NC_VERSION);
    	if ($result === false) {
    		return 'error';
    	}
		
		//処理正常終了
		$this->_setMes(MODULE_MES_GLOBAL_RESULT_END);
		return $return_prefix . 'success';
    }
    
    function _setMes($mes,$tabLine=0) {
    	$this->result_str .= $this->modulesView->getShowMes($mes,$tabLine);
    }
}
?>
