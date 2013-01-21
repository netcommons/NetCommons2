<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install データベース、およびパス・URLの設定セッション登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Action_Main_General extends Action
{
    // リクエストパラメータを受け取るため
    var $sitename = null;
    var $database = null;
    var $dbhost = null;
    var $dbusername = null;
    var $dbpass = null;
    var $dbname = null;
    var $dbprefix = null;
    var $dbpersist = null;
    var $base_url = null;
    var $base_dir = null;
    var $core_base_url = null;
    var $fileuploads_dir = null;
    var $htdocs_dir_select = null;
    var $htdocs_dir = null;
    var $style_dir = null;
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    
    /**
     * Install データベース、およびパス・URLの設定セッション登録
     *
     * @access  public
     */
    function execute()
    {
    	$this->session->setParameter("sitename", $this->sitename);
    	$this->session->setParameter("database", $this->database);
    	$this->session->setParameter("dbhost", $this->dbhost);
    	$this->session->setParameter("dbusername", $this->dbusername);
    	$this->session->setParameter("dbpass", $this->dbpass);
    	$this->session->setParameter("dbname", $this->dbname);
    	$this->session->setParameter("dbprefix", $this->dbprefix);
    	$this->session->setParameter("dbpersist", $this->dbpersist);
    	$this->session->setParameter("base_url", $this->base_url);
    	$this->session->setParameter("base_dir", $this->base_dir);
    	$this->session->setParameter("fileuploads_dir", $this->fileuploads_dir);
    	$this->session->setParameter("htdocs_dir_select", $this->htdocs_dir_select);
    	
    	if($this->htdocs_dir_select == "0" || $this->base_url."/htdocs" == $this->core_base_url) {
    		$this->session->setParameter("core_base_url", $this->base_url);
    	} else {
    		$this->session->setParameter("core_base_url", $this->core_base_url);
    	}
    	if($this->htdocs_dir_select != "0" && START_INDEX_DIR."/htdocs" == $this->htdocs_dir) {
    		$this->session->setParameter("htdocs_dir", dirname(START_INDEX_DIR));
    	} else {
    		$this->session->setParameter("htdocs_dir", $this->htdocs_dir);
    	}
    	if($this->htdocs_dir_select == "1") {
    		$this->session->setParameter("style_dir", $this->base_dir . '/webapp/style');
    	} else {
    		$this->session->setParameter("style_dir", $this->style_dir);
    	}
    	
    	// 詳細設定の項目すべてがデフォルトと同じならば詳細設定は設定していない
    	$base_dir = $this->installCompmain->getConfigDef("base_dir");
    	$fileuploads_dir = $this->installCompmain->getConfigDef("fileuploads_dir");
    	$htdocs_dir = $this->installCompmain->getConfigDef("htdocs_dir");
    	$style_dir = $this->installCompmain->getConfigDef("style_dir");
    	if($this->base_dir == $base_dir &&
    		$this->fileuploads_dir == $fileuploads_dir &&
    		$this->htdocs_dir == $htdocs_dir &&
    		$this->style_dir == $style_dir
    		) {
    		$this->session->setParameter("detail_flag", _OFF);
    		
    	} else {
    		$this->session->setParameter("detail_flag", _ON);
    	}
    	return 'success';
    }
}
?>
