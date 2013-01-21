<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install データ生成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Action_Main_Insertdata extends Action
{
    // リクエストパラメータを受け取るため
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    var $databaseSqlutility = null;
    
    // 値をセットするため
    var $error_flag = false;
    var $res_arr = array();
    
    /**
     * Install データ生成
     *
     * @access  public
     */
    function execute()
    {
    	ini_set("memory_limit", INSTALL_MEMORY_LIMIT);
    	
    	//
    	// デストリビューションの概念を導入した場合にtype値をリクエストパラメータに含めて、
    	// 様々な デストリビューションを実装できるしくみ
    	// 現在、デストリビューションの機能はなしで、「default」固定とする
    	//
		$type = "default";
		
    	$this->installCompmain->setTitle();
    	
    	// NetCommonsバージョンファイル読み込み
    	$base_dir = $this->session->getParameter("base_dir");
    	if (@file_exists($base_dir . "/webapp/config/version.php")) {
    		include_once WEBAPP_DIR . "/config/version.php";
        } else {
        	define("_NC_VERSION","2.1.0.0");	
    	}
        
    	list($result, $this->res_arr) = $this->installCompmain->executeSqlFile(INSTALL_CONFIG_DATA_FILENAME, $type);
    	if($result == false) {
    		$this->error_flag = true;
    		return 'error';	
    	}
    	
		//
		// 共通系のSQLを実行（common_insert.data.php）
		//
		$res_arr = array();
		list($result, $res_arr) = $this->installCompmain->executeSqlFile(INSTALL_INSERT_DATA_COMMON_FILENAME, $type);
		$this->res_arr = array_merge($this->res_arr, $res_arr);
    	if($result == false) {
    		$this->error_flag = true;
    		return 'error';	
    	}
    	
    	//
    	// セッションを閉じる
    	//
    	$this->session->close();
    	
    	return 'success';
    }
}
?>
