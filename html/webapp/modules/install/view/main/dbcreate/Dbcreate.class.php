<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * adodb class
 */

/**
 * Install データベースを作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Main_Dbcreate extends Action
{
    // リクエストパラメータを受け取るため
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    var $error_flag = false;
    var $dbcreate_res_arr = array();
    
    var $error_mes = "";
    /**
     * Install データベースを作成
     *
     * @access  public
     */
    function execute()
    {
    	$this->installCompmain->setTitle();
    	$result = $this->_createDb();
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
    function _createDb() {
    	$result = true;
    	
    	if(!$this->installCompmain->getSessionDb($database, $dbhost, $dbusername, $dbpass, $dbname, $dbprefix, $dbpersist, $dsn)) {
    		// 接続エラー
    		$result = false;
    		$this->dbcreate_res_arr[] = INSTALL_IMG_NO . INSTALL_DBCHECK_SERVER_NOT_CONNECT;
    		$this->error_mes = INSTALL_DBCREATE_FAILED_CONNECT;
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
    		$this->dbcreate_res_arr[] = INSTALL_IMG_NO . INSTALL_DBCHECK_SERVER_NOT_CONNECT;
    		$this->error_mes = INSTALL_DBCREATE_FAILED_CONNECT;
    		return $result;
    	} else {
    		$this->dbcreate_res_arr[] = INSTALL_IMG_YES . INSTALL_DBCHECK_SERVER_CONNECT;
    	}
    	$append_str = "";
    	if(strstr($database, "mysql")) {
    		$server_info = $conn->ServerInfo();
			if(floatval($server_info["version"]) >= 4.01) {
				$append_str = " DEFAULT CHARACTER SET ".DATABASE_CHARSET;
				if(DATABASE_CHARSET == "utf8") {
					$append_str .= " COLLATE utf8_general_ci"; 
				}
				//$conn->Execute("SET NAMES ".DATABASE_CHARSET.";");
			}
		}
    	
    	$result_db =& $conn->Execute("CREATE DATABASE ". $dbname . "".$append_str, array());
    	
		//$result_db = $dbObject->execute("CREATE DATABASE FROM \"$dbname\"");
		if ($result_db === false) {
			$result = false;
    		$this->dbcreate_res_arr[] = INSTALL_IMG_NO . INSTALL_DBCREATE_FAILED_CREATE;
		} else {
			// 成功
			$this->dbcreate_res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_DBCREATE_SUCCESS_CREATE, $dbname);
		}
    	
    	return $result;
    }
}
?>
