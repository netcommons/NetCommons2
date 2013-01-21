<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install イントロダクション表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Main_Permission extends Action
{
    // リクエストパラメータを受け取るため
    
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    var $permission_error_flag = false;
    var $permission_res_arr = array();
    
    /**
     * Install アクセス権のチェック
     *
     * @access  public
     */
    function execute()
    {
    	$this->installCompmain->setTitle();
    	$result = $this->_chkPermissions();
    	if($result == false) {
    		$this->permission_error_flag = true;
    		return 'error';
    	}
    	
    	return 'success';
    }
    
    
    /**
     * 存在チェック、アクセス権のチェック
     * @return boolean
     * @access  private
     */
    function _chkPermissions() {
    	$result = true;
    	//
    	// install.inc.php
    	//
    	//if(!$this->_chkPermission("install.inc.php", transPathSeparator(INSTALL_INC_DIR))) {
    	if(!$this->_chkPermission("install.inc.php", INSTALL_INC_DIR)) {
    		$result = false;
    	}
    	
    	//
    	// htdocsディレクトリ
    	//
    	$fileuploads_dir = $this->session->getParameter("htdocs_dir");
    	if(!$this->_chkPermission($fileuploads_dir)) {
    		$result = false;
    	}
    	
    	//
    	// uploadsディレクトリ
    	//
    	$fileuploads_dir = $this->session->getParameter("fileuploads_dir");
    	if(!$this->_chkPermission($fileuploads_dir)) {
    		$result = false;
    	}
    	
    	//
    	// templates_cディレクトリ
    	//
    	$templates_c_dir = $this->session->getParameter("base_dir") . "/webapp/templates_c";
    	if(!$this->_chkPermission($templates_c_dir)) {
    		$result = false;
    	}
    	
    	return $result;
    }
    /**
     * 存在チェック、アクセス権のチェック
     * 	メンバ変数permission_res_arrにセット
     * @param  string $dir_name or file_name
     * @param  string $path default ""
     * @return boolean
     * @access  private
     */
    function _chkPermission($name, $path="") {
    	$result = true;
    	if($path != "") {
    		$check_path = $path . "/". $name;
    	} else {
    		$check_path	= $name;
    	}
    	$base_name = basename($name);
    	if(is_dir($check_path)) {
    		$base_name .= "/";
    	}
    	if(!file_exists($check_path)) {
    		$result = false;
    		$this->permission_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_PATH_NOT_EXIST, $name);
    	} else {
    		if (!is_writeable($check_path)) {
    			$result = false;
    			if(is_dir($check_path)) {
    				$this->permission_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_PERMISSION_NOT_WRITE_DIRECTORY, $base_name);
    			} else {
    				$this->permission_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_PERMISSION_NOT_WRITE_FILE, $base_name);
    			}
    			//$this->permission_res_arr[] = INSTALL_IMG_NO . INSTALL_FAILED_READ_INI;
    		} else {
    			$this->permission_res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_WRITE_FILE, $base_name);
    		}
    	}
    	return $result;
    }
}
?>