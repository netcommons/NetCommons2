<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install テーブル作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Action_Main_Tbcreate extends Action
{
    // リクエストパラメータを受け取るため
    
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    //var $commonMain = null;
    var $databaseSqlutility = null;
    
    // 値をセットするため
    var $error_flag = false;
    var $res_arr = array();
    
    /**
     * Install テーブル作成
     *
     * @access  public
     */
    function execute()
    {
    	$this->installCompmain->setTitle();
    	//
    	// install.inc.phpへの書き込み
    	//
    	$result = $this->_createTable();
    	if($result == false) {
    		$this->error_flag = true;
    		return 'error';
    	}
    	
    	return 'success';
    }
    
    /**
     * テーブル作成
     * 
     * @return boolean
     * @access  private
     */
    function _createTable() {
    	$base_dir = $this->session->getParameter("base_dir");
    	
    	if(!$this->installCompmain->getSessionDb($database, $dbhost, $dbusername, $dbpass, $dbname, $dbprefix, $dbpersist, $dsn)) {
    		return false;
    	}
    	
    	//
    	// 共通テーブルCreate
    	//
    	$file_path = "/install/sql/".$database."/"._SYS_TABLE_INI;
    	if (!@file_exists(MODULE_DIR.$file_path)) {
    		// mysqliならば、mysqlがあればそちらを使う
    		if($database != "mysqli") {
    			return false;	
    		} 
    		$database = "mysql";
    		$file_path = "/install/sql/".$database."/"._SYS_TABLE_INI;
    		if (!@file_exists(MODULE_DIR.$file_path)) {
    			return false;	
    		}
    	}
    	
    	//モジュールに使用するテーブルあり
 	    // SQLファイルの読み込み
 	    $handle = fopen(MODULE_DIR.$file_path, 'r');
		$sql_query = fread($handle, filesize(MODULE_DIR.$file_path));
		fclose($handle);
		$sql_query = trim($sql_query);
		// SQLユーティリティクラスにて各クエリを配列に格納する
		$this->databaseSqlutility->splitMySqlFile($pieces, $sql_query);
		//
		// DB接続
		//	
    	//include_once $base_dir.'/maple/nccore/db/DbObjectAdodb.class.php';
    	include_once BASE_DIR.'/maple/nccore/db/DbObjectAdodb.class.php';
    	
    	$dbObject = new DbObjectAdodb();
    	$dbObject->setPrefix($dbprefix);
    	$dbObject->setDsn($dsn);
    	$conn_result = @$dbObject->connect();
    	
		if ($conn_result == false) {
			// DBが存在しない場合、CREATE DATABASE
			$this->res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_DBCHECK_NOT_CONNECT, $dbname) .
				"<div class=\"install_checkdb_createdb\">".INSTALL_DBCHECK_NOT_FOUND."</div>".
				"<div class=\"install_checkdb_createdb bold\">&nbsp;&nbsp;".$dbname."</div>".
				"<div class=\"install_checkdb_createdb\">".INSTALL_DBCHECK_CONFIRM_CREATE_DB."</div>";
			return false;
		}
		// DBが存在する場合
		$result = true;
		foreach ($pieces as $piece) {
			// SQLユーティリティクラスにてテーブル名にプレフィックスをつける
			// 配列としてリターンされ、				
            // 	[0] プレフィックスをつけたクエリ
            // 	[4] プレフィックスをつけないテーブル名
			// が格納されている
			$prefixed_query = $this->databaseSqlutility->prefixQuery($piece, $dbObject->getPrefix());
			if ( !$prefixed_query ) {
				$this->res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_TBCREATE_FAILED_CREATE, $dbObject->getPrefix().$prefixed_query[4]);
				$result = false;
				continue;
			}
			// 実行
			if ( !$dbObject->execute($prefixed_query[0]) ) {
				$this->res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_TBCREATE_FAILED_CREATE, $dbObject->getPrefix().$prefixed_query[4]);
				$result = false;
				continue;
			} else {
				// 成功
				$this->res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_TBCREATE_SUCCESS_CREATE, $dbObject->getPrefix().$prefixed_query[4]);
			}
		}		
		return $result;
    	
    }
}
?>
