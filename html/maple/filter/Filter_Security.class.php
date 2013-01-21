<?php

/**
 * Securityチェックを行うFilter
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Security extends Filter {
	
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_actionChain;
    
    var $_request;
     
    var $_response;
    
    var $_session;
     
    var $_className;
    
    var $_errorList;
    
    var $_SecurityManager = null;
    
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_Security() {
		parent::Filter();
	}

	/**
	 * Configファイルの設定を行うFilter
	 *
	 * @access	public
	 **/
	function execute() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_request =& $this->_container->getComponent("Request");
        $this->_session =& $this->_container->getComponent("Session");
        $this->_errorList =& $this->_actionChain->getCurErrorList();
        $this->_className = get_class($this);
    	
    	$this->_prefilter();
    	
        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");
        

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}
	
	/**
     * プレフィルタ
     * @access private
     */
    function _prefilter()
    {
    	$actionChain =& $this->_container->getComponent("ActionChain");
        $action_name = $actionChain->getCurActionName();
        if($action_name == "pages_action_rescue" || $action_name == "pages_view_rescue") {
        	// レスキュー画面は表示させる
        	return;	
        }
        
    	$getdata =& $this->_container->getComponent("GetData");
    	$config =& $getdata->getParameter("config");
    	
    	if(isset($config[_SECURITY_CONF_CATID]['security_level']) &&
			$config[_SECURITY_CONF_CATID]['security_level']['conf_value'] != _SECURITY_LEVEL_NONE) {
			
			require_once MAPLE_DIR.'/nccore/SecurityManager.class.php';
			$this->_SecurityManager =& new SecurityManager($config);
			$this->_container->register($this->_SecurityManager, "securityManager");
			// ----------------------------------------------
			// ---IP拒否チェック                          ---
			// ----------------------------------------------
			$this->_SecurityManager->chkEnableBadips();
			
			// ----------------------------------------------
			// ---IP変動を禁止するベース権限チェック      ---
			// ----------------------------------------------
			if(!$this->_SecurityManager->chkGroupsDenyipmove()) {
				// エラー
				// ログアウトし、リダイレクト
				// 強制ログアウト
				$this->_session->close();	
				return;	
			}
			
		}
		// ----------------------------------------------
		// ---信用できるIP                            ---
		// ----------------------------------------------
		$is_reliable = false;
		$reliable_ips = $config[_SECURITY_CONF_CATID]['reliable_ips']['conf_value'];
		if($reliable_ips != "") {
			$reliable_ips = unserialize( $reliable_ips ) ;
			foreach( $reliable_ips as $reliable_ip ) {
				if( ! empty( $reliable_ip ) && preg_match( '/'.$reliable_ip.'/' , $_SERVER['REMOTE_ADDR'] ) ) {
					// 正常終了
					$is_reliable = true;
					break;
				}
			}
		}
		
		// ----------------------------------------------
		// ---リクエストチェック-変換                 ---
		// ----------------------------------------------
		if(!$this->_request->chkRequest(($is_reliable == false && $config[_SECURITY_CONF_CATID]['security_level']['conf_value'] != _SECURITY_LEVEL_NONE))) {
			// エラー
			// ログアウトし、リダイレクト
			// 強制ログアウト
			$this->_session->close();
			return;
		}
		
		// ----------------------------------------------
		// ---Dos攻撃チェック                         ---
		// ----------------------------------------------
		if($is_reliable == false && $config[_SECURITY_CONF_CATID]['security_level']['conf_value'] != _SECURITY_LEVEL_NONE) {
			$attributes = $this->getAttributes();
			if(isset($attributes["dos_attack"]) && $attributes["dos_attack"] == _ON) {
				if(!$this->_SecurityManager->chkDosAttack()) {
					// エラー
					// ログアウトし、リダイレクト
					// 強制ログアウト
					$this->_session->close();	
					return;	
				}
			}
		}
		unset($config[_SECURITY_CONF_CATID]);
    }
    
    /**
     * ポストフィルタ
     * @access private
     */
    function _postfilter()
    {
    	
    }
}
?>
