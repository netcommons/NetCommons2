<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install パス・URLのチェック
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Main_Urlcheck extends Action
{
    // リクエストパラメータを受け取るため
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    var $error_flag = false;
    var $url_res_arr = array();
    
    /**
     * Install パス・URLのチェック
     *
     * @access  public
     */
    function execute()
    {
    	$this->installCompmain->setTitle();
    	
    	$result = $this->_chkPaths();
    	if($result == false) {
    		$this->error_flag = true;
    	}
    	
    	$result = $this->_chkUrl();
    	if($result == false) {
    		$this->error_flag = true;
    	}
    	
    	if($this->error_flag == true) {
    		return 'error';	
    	}
    	
    	//
    	// TODO
    	// core_base_urlもindex.php、もしくは、その中に含まれる画像が読み込めるかどうかチェックしたほうがよい
    	//
    	return 'success';
    }
    
    /**
     * パスのチェック
     * @return boolean
     * @access  private
     */
    function _chkPaths() {
    	$result = true;
    	//
    	// base_dir
    	//
    	$base_dir = $this->session->getParameter("base_dir");
    	if(!$this->_chkPath($base_dir, "INSTALL_URLCHECK_ROOT_DIR")) {
    		$result = false;
    	}
    	
    	//
    	// fileuploads_dir
    	//
    	$fileuploads_dir = $this->session->getParameter("fileuploads_dir");
    	if(!$this->_chkPath($fileuploads_dir, "INSTALL_URLCHECK_UPLOADS_DIR")) {
    		$result = false;
    	}
    	
    	//
    	// htdocs_dir
    	//
    	$htdocs_dir = $this->session->getParameter("htdocs_dir");
    	if(!$this->_chkPath($htdocs_dir, "INSTALL_URLCHECK_HTDOCS_DIR")) {
    		$result = false;
    	}
    	
    	//
    	// style_dir
    	//
    	$style_dir = $this->session->getParameter("style_dir");
    	if(!$this->_chkPath($style_dir, "INSTALL_URLCHECK_THEME_DIR")) {
    		$result = false;
    	}
    	
    	return $result;
    }
    
    /**
     * パスのチェック
     * 	メンバ変数$url_res_arrにセット
     * @param string $check_path
     * @param string $constant_name
     * @return boolean
     * @access  private
     */
    function _chkPath($check_path, $constant_name) {
    	$result = true;
    	$name = constant($constant_name);
    	
    	if(!file_exists($check_path)) {
    		$result = false;
    		$this->url_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_URLCHECK_NOT_DETECT, $name);
    	} else {
    		if (!is_dir($check_path)) {
    			$result = false;
    			$this->url_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_URLCHECK_NOT_DIRECTORY, $name);
    		} else {
    			if($constant_name == "INSTALL_URLCHECK_ROOT_DIR") {
    				// ルートパスの場合、もう少し詳細にチェック
    				// webapp mapleのディレクトリがあるかどうかチェック
    				if (!is_dir($check_path."/webapp")) {
    					$result = false;
    					$this->url_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_URLCHECK_NOT_DETECT, $name);
    				}
    				
    				if (!is_dir($check_path."/maple")) {
    					$result = false;
    					$this->url_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_URLCHECK_NOT_DETECT, $name);
    				}
    			}
    			if($result == true) {
    				$this->url_res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_URLCHECK_SUCCESS_DETECT, $name);
    			}
    		}
    	}
    	return $result;
    }
    
    /**
     * BASE_URLのチェック
     * 	メンバ変数$url_res_arrにセット
     * @return boolean
     * @access  private
     */
    function _chkUrl() {
    	$result = true;
    	$base_url = $this->session->getParameter("base_url");
    	
    	if(preg_match('/^http[s]?:\/\/(.*)[^\/]+$/i',$base_url)){
	        $this->url_res_arr[] = INSTALL_IMG_YES.INSTALL_URLCHECK_SUCCESS_URL;
	    }else{
	    	$result = false;
	        $this->url_res_arr[] = INSTALL_IMG_NO.INSTALL_URLCHECK_FAILED_URL;
	    }
	    return $result;
    }
}
?>
