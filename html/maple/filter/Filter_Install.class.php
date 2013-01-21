<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * インストール用Filter
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Install extends Filter
{
    var $_classname = "Filter_Install";

    var $_container = null;
    var $_log = null;
    var $_filterChain = null;
    var $_session = null;
    var $_request = null;
    

    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_Install()
    {
        parent::Filter();
    }

    /**
     * Mobile用クラス実行
     *
     * @access  public
     * 
     */
    function execute()
    {
        $this->_container =& DIContainerFactory::getContainer();
        $this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        //$this->_actionChain =& $this->_container->getComponent("ActionChain");
        //$this->_db =& $this->_container->getComponent("DbObject");
        $this->_session =& $this->_container->getComponent("Session");
        $this->_request =& $this->_container->getComponent("Request");
		

        $this->_log->trace("{$this->_classname}の前処理が実行されました", "{$this->_classname}#execute");
        $this->_preFilter();
        
        $this->_filterChain->execute();

        $this->_log->trace("{$this->_classname}の後処理が実行されました", "{$this->_classname}#execute");
        $this->_postFilter();
    }
    
    /**
     * プレフィルタ
     *
     * @access  private
     */
    function _preFilter()
    {
    	$this->_request->setParameter("_header", _OFF);
    	// セキュリティフィルタを通さないため
    	$this->_request->chkRequest();
    	
    	$select_lang = $this->_request->getParameter("select_lang");
		if(isset($select_lang)) {
			$this->_session->setParameter("_lang", $select_lang);
		}
		$_lang = $this->_session->getParameter("_lang");
		if(!isset($_lang)) {
			if(defined("INSTALL_DEFAULT_LANG")) {
				$this->_session->setParameter("_lang", INSTALL_DEFAULT_LANG);
			} else {
				$this->_session->setParameter("_lang", "japanese");
			}
		}
    }

    /**
     * ポストフィルタ
     *
     * @access  private
     */
    function _postFilter()
    {
    }

}
?>