<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install データベース、およびパス・URLの設定
 * HTDOCS_DIR、アップロードディレクトリ、テーマディレクトリの設定も追加する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Main_General extends Action
{
    // リクエストパラメータを受け取るため
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    var $database_type_arr = array();
    var $setting_arr = array();
    var $_dsn_arr = array();
    var $detail_flag = null;
    
    /**
     * Install データベース、およびパス・URLの設定
     *
     * @access  public
     */
    function execute()
    {
    	$this->database_type_arr = $this->_getDatabaseType();
    	$this->setting_arr = $this->_getGeneralInf();
    	
    	//
    	// 詳細設定をONにして表示するかどうか
    	//
    	$base_dir = $this->installCompmain->getConfigDef("base_dir");
    	$fileuploads_dir = $this->installCompmain->getConfigDef("fileuploads_dir");
    	$htdocs_dir = $this->installCompmain->getConfigDef("htdocs_dir");
    	$style_dir = $this->installCompmain->getConfigDef("style_dir");
    	if($this->setting_arr['base_dir'] == $base_dir &&
    		$this->setting_arr['fileuploads_dir'] == $fileuploads_dir &&
    		$this->setting_arr['htdocs_dir'] == $htdocs_dir &&
    		$this->setting_arr['style_dir'] == $style_dir
    		) {
    		$this->detail_flag = _OFF;
    		
    	} else {
    		$this->detail_flag = _ON;
    	}
    	
    	$this->installCompmain->setTitle();
    	return 'success';
    }
    
    /**
     * 使用できるデータベースの種類取得
     * @return array
     * @access  private
     */
    function _getDatabaseType() {
    	return explode("|", INSTALL_DATABASE_LIST);
    }
    
    /**
     * 基本項目取得
     * session値>>install.ini.php>>install/config/define.inc.php
     * 
     * @return array
     * @access  private
     */
    function &_getGeneralInf() {
    	$setting_arr = array();
    	$setting_arr["sitename"] = $this->_getGeneralInfDetail("sitename");
    	$setting_arr["database"] = $this->_getGeneralInfDetail("database");
    	$setting_arr["dbhost"] = $this->_getGeneralInfDetail("dbhost");
    	$setting_arr["dbusername"] = $this->_getGeneralInfDetail("dbusername");
    	$setting_arr["dbpass"] = $this->_getGeneralInfDetail("dbpass");
    	$setting_arr["dbname"] = $this->_getGeneralInfDetail("dbname");
    	$setting_arr["dbprefix"] = $this->_getGeneralInfDetail("dbprefix");
    	if(preg_match("/_$/", $setting_arr["dbprefix"])) {
    		$setting_arr["dbprefix"] = substr($setting_arr["dbprefix"], 0,strlen($setting_arr["dbprefix"]) - 1);
    	}
    	$setting_arr["dbpersist"] = $this->_getGeneralInfDetail("dbpersist");
    	
    	$setting_arr["base_dir"] = $this->_getGeneralInfDetail("base_dir");
    	$setting_arr["base_url"] = $this->_getGeneralInfDetail("base_url");
    	
    	// 詳細設定用
    	$setting_arr["core_base_url"] = $this->_getGeneralInfDetail("core_base_url");
    	$setting_arr["htdocs_dir"] = $this->_getGeneralInfDetail("htdocs_dir");
    	$setting_arr["style_dir"] = $this->_getGeneralInfDetail("style_dir");
    	$setting_arr["fileuploads_dir"] = $this->_getGeneralInfDetail("fileuploads_dir");
    	
    	return $setting_arr;
    }
    
    function _getGeneralInfDetail($key) {
    	//
    	// sessionから取得
    	//
    	$value = $this->session->getParameter($key);
    	if(isset($value)) {
    		return $value;
    	}
    	if(defined("INSTALL_DEFAULT_SITE_TITLE") && $key == "sitename") {
    		return INSTALL_DEFAULT_SITE_TITLE;
    	}
    	//
    	// install.inc.phpから取得
    	//
    	if(defined("BASE_URL") && $key == "base_url" && BASE_URL != "http://") {
    		return BASE_URL;
    		//return transPathSeparator(BASE_URL);
    	}
    	if(defined("BASE_DIR") && $key == "base_dir") {
    		return BASE_DIR;
    		//return transPathSeparator(BASE_DIR);
    	}
    	if(defined("CORE_BASE_URL") && $key == "core_base_url" && CORE_BASE_URL != "http://") {
    		return CORE_BASE_URL;
    		//return transPathSeparator(CORE_BASE_URL);
    	}
    	if(defined("HTDOCS_DIR") && $key == "htdocs_dir") {
    		return HTDOCS_DIR;
    		//return transPathSeparator(HTDOCS_DIR);
    	}
    	if(defined("STYLE_DIR") && $key == "style_dir") {
    		return STYLE_DIR;
    		//return transPathSeparator(STYLE_DIR);
    	}
    	if(defined("FILEUPLOADS_DIR") && $key == "fileuploads_dir") {
    		$fileuploads_dir = FILEUPLOADS_DIR;
    		//$fileuploads_dir = transPathSeparator(FILEUPLOADS_DIR);
    		if ( substr($fileuploads_dir, -1) == "/" ) {
    			$fileuploads_dir = substr($fileuploads_dir, 0, -1);
	        }
    		return $fileuploads_dir;
    	}
    	if(defined("DATABASE_DSN")) {
    		// データソース名(DSN)から取得
    		$_dsn_arr =& $this->_getDataSource(DATABASE_DSN);
    		if($_dsn_arr != false) {
    			switch($key) {
		    		case "database":
		    			// データベースの種類
		    			$database = $_dsn_arr[0];
		    			return $database;
		    			//break;
		    		case "dbusername":
		    			// データベース-ID名
		    			$dbusername = $_dsn_arr[1];
		    			return $dbusername;
		    			//break;
		    		case "dbpass":
		    			// データベース-パスワード
		    			$dbpass = $_dsn_arr[2];
		    			return $dbpass;
		    			//break;
		    		case "dbhost":
		    			// データベース-ホスト名
		    			$dbhost = $_dsn_arr[3];
		    			return $dbhost;
		    			//break;
		    		case "dbname":
		    			// データベース名
		    			$dbname = $_dsn_arr[4];
		    			return $dbname;
		    			//break;
		    	}	
    		}
    	}
    	
    	if(defined("DATABASE_PREFIX") && $key == "dbprefix" && DATABASE_PREFIX != "") {
    		return DATABASE_PREFIX;
    	}
    	if(defined("DATABASE_PCONNECT") && $key == "dbpersist") {
    		return DATABASE_PCONNECT;
    	}
	    //
    	// install/config/define.inc.phpから取得
    	//
    	return $this->installCompmain->getConfigDef($key);
    }
    
    /**
     * データソースから値を取得
     * 
     * @return array
     * @access  private
     */
    function _getDataSource($dsn){
    	if(count($this->_dsn_arr) > 0) {
    		return $this->_dsn_arr;
    	}
    	
    	// 'DATABASE_DSN', "mysql://root:mysql@localhost/db_name"
		$pattern = "/^(.+?):\/\/(.+?):(.*?)@(.+?)\/(.+)$/";
		if(preg_match($pattern, $dsn, $matches)) {
			$database = $matches[1];
    		$dbusername = $matches[2];
    		$dbpass = $matches[3];
    		$dbhost = $matches[4];
    		$dbname = $matches[5];
    		$this->_dsn_arr = array($database, $dbusername, $dbpass, $dbhost, $dbname);
			return $this->_dsn_arr;
		}
		return false;
    }
}
?>
