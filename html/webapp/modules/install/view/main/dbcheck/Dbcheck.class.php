<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * adodb class
 */

/**
 * Install データベースをチェック
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Main_Dbcheck extends Action
{
    // リクエストパラメータを受け取るため
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    var $error_flag = false;
    var $reload_flag = false;
    var $db_create_flag = false;
    var $dbcheck_res_arr = array();
    
    
    /**
     * Install データベースをチェック
     *
     * @access  public
     */
    function execute()
    {
    	error_reporting(0);
    	$this->installCompmain->setTitle();
    	$result = $this->_chkDb();
    	if($result == false) {
    		$this->error_flag = true;
    	}
    	
    	return 'success';
    }
    
    /**
     * DBのチェック
     * @return boolean
     * @access  private
     */
    function _chkDb() {
    	$result = true;
    	if(!$this->installCompmain->getSessionDb($database, $dbhost, $dbusername, $dbpass, $dbname, $dbprefix, $dbpersist, $dsn)) {
    		// 接続エラー
    		$result = false;
    		$this->dbcheck_res_arr[] = INSTALL_IMG_NO . INSTALL_DBCHECK_SERVER_NOT_CONNECT;
    		return $result;
    	}
    	
    	//$base_dir = $this->session->getParameter("base_dir");
    	//include_once $base_dir.'/maple/nccore/db/DbObjectAdodb.class.php';
    	include_once BASE_DIR.'/maple/nccore/db/DbObjectAdodb.class.php';
    	
    	
    	$conn =& NewADOConnection($database);
    	
    	// 接続チェック
    	if(!$conn->Connect($dbhost, $dbusername, $dbpass)) {
    		// 接続エラー
    		$result = false;
    		$this->dbcheck_res_arr[] = INSTALL_IMG_NO . INSTALL_DBCHECK_SERVER_NOT_CONNECT;
    		return $result;
    	} else {
    		$this->dbcheck_res_arr[] = INSTALL_IMG_YES . INSTALL_DBCHECK_SERVER_CONNECT;
    	}
    	
    	$dbObject = new DbObjectAdodb();
    	$dbObject->setPrefix($dbprefix);
    	$dbObject->setDsn($dsn);
    	$conn_result = @$dbObject->connect();
		if ($conn_result == false) {
			// DBが存在しない場合、CREATE DATABASE
			$this->dbcheck_res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_DBCHECK_NOT_CONNECT, $dbname) .
				"<div class=\"install_checkdb_createdb\">".INSTALL_DBCHECK_NOT_FOUND."</div>".
				"<div class=\"install_checkdb_createdb bold\">&nbsp;&nbsp;".$dbname."</div>".
				"<div class=\"install_checkdb_createdb\">".INSTALL_DBCHECK_CONFIRM_CREATE_DB."</div>";
			$this->db_create_flag = true;
            $this->reload_flag = true;
			$result = false;
		} else {
			// DBが存在する場合
			$result_db = $dbObject->execute("SELECT * FROM {modules}", array(), 1);
			if ($result_db === false) {
				// モジュールテーブルが存在しない場合、正常終了
				// 1.1と同じテーブル名称のものを指定している
				$this->dbcheck_res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_DBCHECK_CONNECT, $dbname);
			} else {
				// モジュールテーブルが存在する場合
				$this->dbcheck_res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_DBCHECK_CONNECT, $dbname);
				$this->dbcheck_res_arr[] = INSTALL_IMG_NO . INSTALL_DBCHECK_EXIST_TABLE;
				$this->reload_flag = true;
				$result = false;
			}
			
		}
    	
    	return $result;
    }
}
?>
