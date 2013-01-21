<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install install.inc.php書き込み処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Action_Main_Saveini extends Action
{
    // リクエストパラメータを受け取るため
    
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    var $fileAction = null;
    
    // 値をセットするため
    var $setting_error_flag = false;
    var $setting_res_arr = array();
    
    /**
     * Install install.inc.php書き込み処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->installCompmain->setTitle();
    	//
    	// install.inc.phpへの書き込み
    	//
    	$result = $this->_writeInifile();
    	if($result == false) {
    		$this->setting_error_flag = true;
    		return 'error';
    	}
    	
    	return 'success';
    }
    
    /**
     * install.inc.phpへの書き込み
     * 
     * @return boolean
     * @access  private
     */
    function _writeInifile() {
    	$copy_path = INSTALL_INC_DIR . "/". "install.inc.php";
    	$path = WEBAPP_DIR . "/config/". "install.inc.dist.php";
    	
    	// unlink
    	//@unlink($copy_path);
    	
    	if(!$this->fileAction->copyFile($path, $copy_path)) {
    		$this->setting_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_NOT_WRITE, "install.inc.php");
    		return false;	
    	} else {
    		// 上書き成功
    		$this->setting_res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_OVERWRITTEN, "install.inc.dist.php", "install.inc.php");
    	}
    	// ファイルのステータスのキャッシュをクリア
    	clearstatcache();
    	
    	$rewrite = array();
    	if(!$this->installCompmain->getSessionDb($database, $dbhost, $dbusername, $dbpass, $dbname, $dbprefix, $dbpersist, $dsn)) {
    		$this->setting_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_NOT_WRITE, "install.inc.php");
    		return false;
    	}
    	
    	$rewrite["DATABASE_DSN"] = $this->_quoteString($dsn);
    	
    	//$rewrite["DATABASE_PREFIX"] = $dbprefix;
    	$rewrite["DATABASE_PREFIX"] = $this->_quoteString($dbprefix);
    	
    	$rewrite["DATABASE_PCONNECT"] = intval($dbpersist);
    	$rewrite["BASE_URL"] = $this->_quoteString($this->session->getParameter("base_url"));
    	
    	$rewrite["BASE_DIR"] = $this->session->getParameter("base_dir");
    	$rewrite["FILEUPLOADS_DIR"] = $this->session->getParameter("fileuploads_dir") . "/";	// 最後に「/」をつける
    	$rewrite["HTDOCS_DIR"] = $this->session->getParameter("htdocs_dir");
    	$rewrite["STYLE_DIR"] = $this->session->getParameter("style_dir");
    	$rewrite["CORE_BASE_URL"] = $this->session->getParameter("core_base_url");
    	//$rewrite["DATABASE_CHARSET"] = $this->session->getParameter("charset");
    	
    	// 整形
    	if($rewrite["BASE_DIR"] == dirname(START_INDEX_DIR)) {
    		$rewrite["BASE_DIR"] = "dirname(START_INDEX_DIR)";
    	} else {
    		$rewrite["BASE_DIR"] = $this->_quoteString($rewrite["BASE_DIR"]);
    	}
    	
    	if($rewrite["FILEUPLOADS_DIR"] == dirname(INSTALL_INC_DIR) . '/uploads/') {
    		$rewrite["FILEUPLOADS_DIR"] = "dirname(INSTALL_INC_DIR) . '/uploads/'";
    	} else {
    		$rewrite["FILEUPLOADS_DIR"] = $this->_quoteString($rewrite["FILEUPLOADS_DIR"]);
    	}
    	if($rewrite["HTDOCS_DIR"] == START_INDEX_DIR) {
    		$rewrite["HTDOCS_DIR"] = "START_INDEX_DIR";
    	} else {
    		$rewrite["HTDOCS_DIR"] = $this->_quoteString($rewrite["HTDOCS_DIR"]);
    	}
    	if($rewrite["STYLE_DIR"] == $this->session->getParameter("base_dir") . '/webapp/style') {
    		$rewrite["STYLE_DIR"] = "BASE_DIR . '/webapp/style'";
    	} else {
    		$rewrite["STYLE_DIR"] = $this->_quoteString($rewrite["STYLE_DIR"]);
    	}
    	if($rewrite["CORE_BASE_URL"] == $this->session->getParameter("base_url") || $rewrite["CORE_BASE_URL"] == "http://") {
    		$rewrite["CORE_BASE_URL"] = "BASE_URL";
    	} else {
    		$rewrite["CORE_BASE_URL"] = $this->_quoteString($rewrite["CORE_BASE_URL"]);
    	}
    	
    	if(!$this->_doRewrite($copy_path, $rewrite)) {
    		// エラー
    		return false;	
    	}
    	return true;
    }
    
    function _quoteString($str)
    {
    	$str = "'" . addslashes($str) . "'";
        //$str = "'".str_replace('\\"', '"', addslashes($str))."'";
        return $str;
    }
    
    /**
     * 書き込み処理
     * @param string $path
     * @param array  $rewrite
     * @return boolean
     * @access  private
     */
    function _doRewrite($path, &$rewrite){
        if ( ! $file = fopen($path,"r") ) {
        	$this->setting_res_arr[] = INSTALL_IMG_NO.INSTALL_FAILED_READ_INI;
            return false;
        }
        $content = fread($file, filesize($path) );
        fclose($file);
        $error_flag = false;

        foreach($rewrite as $key => $val){
            if(is_int($val) &&
             preg_match("/(define\()([\"'])(".$key.")\\2,\s*([0-9]+)\s*\);/",$content)){

                $content = preg_replace("/(define\()([\"'])(".$key.")\\2,\s*([0-9]+)\s*\);/"
                , "define('".$key."', ".$val.");"
                , $content);
                $this->setting_res_arr[] = INSTALL_IMG_YES.sprintf(INSTALL_CONST_WRITTEN, "<b>$key</b>", $val)."\n";
            } else if(preg_match("/(define\()([\"'])(".$key.")\\2,\s*([\"']??)(.*?)\\4\s*\);/",$content)){
                $content = preg_replace("/(define\()([\"'])(".$key.")\\2,\s*([\"']??)(.*?)\\4\s*\);/"
                , "define('".$key."', ".$val.");"
                , $content);
                $this->setting_res_arr[] = INSTALL_IMG_YES.sprintf(INSTALL_CONST_WRITTEN, "<b>$key</b>", $val)."\n";
            } else {
                $error_flag = true;
                $this->setting_res_arr[] = INSTALL_IMG_NO.sprintf(INSTALL_CONST_FAILED_WRITING, "<b>$key</b>")."\n";
            }
        }

        if ( !$file = fopen($path,"w") ) {
        	$this->setting_res_arr[] = INSTALL_IMG_NO.INSTALL_FAILED_READ_INI;
            return false;
        }

        if ( fwrite($file,$content) == -1 ) {
            fclose($file);
            $this->setting_res_arr[] = INSTALL_IMG_NO.INSTALL_FAILED_WRITE_INI;
            return false;
        }

        fclose($file);
        if($error_flag) {
        	return 	false;
        }
        return true;
    }
}
?>
