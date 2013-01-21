<?php

/**
 * エラーリストが存在した場合、リダイレクト画面表示
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Redirect extends Filter {
	
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_actionChain;
    
    var $_commonMain = null;
    
    var $_className;
    
    var $url = "";
    
   
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_Redirect() {
		parent::Filter();
	}

	/**
	 * Viewの処理を実行
	 *
	 * @access	public
	 **/
	function execute() {
		$this->_container =& DIContainerFactory::getContainer();
        $this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_commonMain =& $this->_container->getComponent("commonMain");
        
        $this->_className = get_class($this);
    
        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");
        $this->_prefilter();

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}
	
	/**
     * プレフィルタ
     * エラーリストが存在した場合、リダイレクト画面表示
     * @access private
     */
    function _prefilter()
    {
    	$errorList =& $this->_actionChain->getCurErrorList();
    	if ($errorList->isExists() && $errorList->getType() != VALIDATE_ERROR_NONEREDIRECT_TYPE) {
    		$this->_commonMain->redirectHeader($this->url);
    	}
    	/*
    	$errorList =& $this->_actionChain->getCurErrorList();
    	if ($errorList->isExists()) {
    		//エラーリストが存在した場合、リダイレクト画面表示
    		$config =& $this->_container->getComponent("configView");
			$meta = $config->getMetaHeader();
			$url = BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".DEFAULT_ACTION;
			//$url = htmlspecialchars(str_replace("?action=","?_sub_action=",str_replace("&","@",BASE_URL.INDEX_FILE_NAME.$this->_request->getStrParameters(false))), ENT_QUOTES);
			
			$renderer =& SmartyTemplate::getInstance();
			$renderer->assign('header_field',$meta);
			$renderer->assign('time',2);
			$renderer->assign('url',$url);
			$renderer->assign('lang_ifnotreload',sprintf(_IFNOTRELOAD,$url));    			
			$renderer->setErrorList($errorList);
			$main_template_dir = WEBAPP_DIR . "/templates/"."main/";
			
			//template_dirセット
			$renderer->setTemplateDir($main_template_dir);
		
			$result = $renderer->fetch("redirect.html",'redirect');
			print $result;
    		exit;	
    	}
    	*/
    }
    
    /**
     * ポストフィルタ
     * @access private
     */
    function _postfilter()
    {
    }
    
    /**
     * ポストフィルタ
     * @access private
     */
    function setUrl($url = "")
    {
    	$this->url = $url;
    }
}
?>
