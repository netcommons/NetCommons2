<?php
//
// $Id: Filter_Cache.class.php,v 1.7 2008/06/16 02:11:48 Ryuji.M Exp $
//
require_once MAPLE_DIR.'/nccore/SmartyTemplate.class.php';

/**
 * Cacheの削除するキー、読み込むキーを登録するフィルタ
 * 「read_cache」の値は、page_id,block_id,session_id,_auth_id,_user_auth_idのいずれかをカンマ区切りで指定
 * 				また、nocacheと指定することでキャッシュを行わない
 * 「clear_cache」の値は、module_id(module_dir or action_name),page_id,block_id,session_id,_auth_id,_user_auth_idのいずれかをカンマ区切りで指定
 * 				module_dir,action_nameは、お知らせならば、「announcement」、「announcement_view_main」といったように指定。
 * 				デフォルト：キャッシュをクリアしない。
 * 				また、allcacheと指定することですべてのキャッシュをクリアすることができる
 *  携帯用に「read_cache_mobile」、「clear_cache_mobile」を追加
 * 
 * 例1)
 * [Cache]
 * read_cache=_auth_id
 * ※現在URL＋_auth_id毎にキャッシュを読み込む
 * 
 * 例2)
 * clear_cache=module_id
 * ※指定アクションの動いているモジュールで使用されているキャッシュファイルをすべて削除
 * 
 * 例3)
 * clear_cache=block_id
 * ※ブロックIDが等しいキャッシュファイルをすべて削除→ブロックに配置してあるモジュールのキャッシュファイルをすべて削除
 * 
 * @author	Ryuji Masukawa
 **/
class Filter_Cache extends Filter {
	/**
	 * @var Cache_key
	 *
	 * @access	private
	 **/
	 var $_read_cache;
	 var $_clear_cache;
	 
	 var $_cache_expire;
	//var $page_id;
	//var $block_id;
	//var $session_id;
	//var $_auth_id;
	//var $_user_auth_id;
	
	var $_container;
	
	var $_log;

    var $_filterChain;
    
    var $_actionChain;
    
    var $_request;
     
    var $_response;
    
    var $_session;
     
    var $_className;
    
    var $attributes_postfix = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_Cache() {
		parent::Filter();
	}

	/**
	 * Cacheの削除、登録するキーを登録するフィルタ
	 *
	 * @access	public
	 **/
	function execute() {
		$this->_container =& DIContainerFactory::getContainer();
        $this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_request =& $this->_container->getComponent("Request");
        $this->_response =& $this->_container->getComponent("Response");
        $this->_session =& $this->_container->getComponent("Session");
        
        $this->_className = get_class($this);
        
        $_mobile_flag = $this->_session->getParameter("_mobile_flag");
    	$this->attributes_postfix = "";
    	if($_mobile_flag == _ON) {
    		$this->attributes_postfix = "_mobile";
    	}
    
        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");
        $this->_prefilter();

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}
	
	/**
     * プレフィルタ
     * キャッシュを読み込むキーを登録する
     * @access private
     */
    function _prefilter()
    {
    	$attributes = $this->getAttributes();
    	if (isset($attributes["read_cache".$this->attributes_postfix])) {
    		$this->_read_cache = explode(",", $attributes["read_cache".$this->attributes_postfix]);
    	}
    	if (isset($attributes["cache_expire"])) {
    		// キャッシュ有効期間
    		$this->_cache_expire = $attributes["cache_expire"];
    	} else {
    		$this->_cache_expire = _SMARTY_CACHE_EXPIRE;
    	}
    }
    
    /**
     * ポストフィルタ
     * キャッシュを削除する
     * @access private
     */
    function _postfilter()
    {
    	$attributes = $this->getAttributes();
    	if (isset($attributes["clear_cache".$this->attributes_postfix])) {
    		$this->_clear_cache = explode(",", $attributes["clear_cache".$this->attributes_postfix]);
    		$renderer =& SmartyTemplate::getInstance();
    		//if(isset($this->_clear_cache['allcache'])) {
    			$renderer->clear_cache();
    		//} else {
    		//	$renderer->clear_cache("clear");
    		//}
    	}
    }
    
    /**
     * 
     * キャッシュを読み込むキーをセットする
     * @access public
     */
    function setReadCache($read_cache)
    {
    	$this->_read_cache = $read_cache;
    }
    
    /**
     * 
     * キャッシュを削除キーをセットする
     * @access public
     */
    function setClearCache($clear_cache)
    {
    	$this->_clear_cache = $clear_cache;
    }
    
    /**
     * 
     * 有効期限を取得する
     * @access public
     */
    function &getReadCache()
    {
    	return $this->_read_cache;
    }
    
    
    /**
     * 
     * キャッシュを読み込むキーを取得する
     * @access public
     */
    function &getCacheExpire()
    {
    	return $this->_cache_expire;
    }
    
    /**
     * 
     * キャッシュを削除キーを取得する
     * @access public
     */
    function &getClearCache()
    {
    	return $this->_clear_cache;	
    }
}
?>