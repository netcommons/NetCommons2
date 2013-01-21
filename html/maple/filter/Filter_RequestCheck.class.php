<?php
 /**
 *POSTならば登録処理、GETならば表示処理
 *を行っているかどうかチェックするFilter
 *
 *[RequestCheck]
 *request = POST   (or GET)
 *のように指定
 *
 *default NOTE:action_nameがmodule名称_actionならば登録処理
 *        moduke名称_viewならば表示処理とみなす
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_RequestCheck extends Filter {
	
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_actionChain;
    
    var $_request;
     
    var $_className;
    
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_RequestCheck() {
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
        $this->_request =& $this->_container->getComponent("Request");
        
        $this->_className = get_class($this);
    	
    	$this->_prefilter();
    	
        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");
        

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}
	
	/**
     * プレフィルタ
     * 初期処理を行う
     * @access private
     */
    function _prefilter()
    {
    	$errorList =& $this->_actionChain->getCurErrorList();
    	if ($errorList->isExists()) {
    		//既にエラーがあればそのまま返却
    		return;	
    	}
    	//アクション名取得
		$action_name = $this->_actionChain->getCurActionName();
		$pathList = explode("_", $action_name);		
		$attributes = $this->getAttributes();
		if (isset($attributes["request"])) {
			if ($attributes["request"] == "BOTH" || $this->_request->getMethod() == $attributes["request"]) {
				//登録処理の場合、リファラチェック
				if ($this->_request->getMethod() == "POST" && $this->_refcheck()) {
					return;
				} else if($this->_request->getMethod() == "GET"){
					return;
				}
			}
		} else if ($this->_request->getMethod() == "POST" && isset($_FILES) && (0 < count($_FILES))) {
			//ファイルアップロード処理の場合、リファラチェック
    		if ($this->_refcheck()) {
            	return;
			}
		} else {
			//Default
			//system_view(action)_XXXX
			$i = 1;
			//if($pathList[1] == "system" && isset($pathList[2])) {
			//	$i = 2;
			//}		
			if($pathList[$i] != "action" && $pathList[$i] != "view") {
				//module名_action or module名_viewのどちらでもない場合、チェックしない
				return;
	    	}else if ($this->_request->getMethod() == "POST" && $pathList[$i] == "action") {
	    		//登録処理の場合、リファラチェック
	    		if (isset($attributes["refcheck"]) && $attributes["refcheck"] == "none") {
	    			return;
	    		}
	    		if ($this->_refcheck()) {
	            	return;
				}
	        } else if($this->_request->getMethod() == "GET" && $pathList[$i] == "view"){
	            return;
	        }
		}
    	//エラー
		$errorList->add("RquestCheck_Error", sprintf(_REQUESTCHECK_FAILURE,CURRENT_URL));
		$errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
		return;	
    	
    }
    
    /**
     * リファラチェック
     * @access private
     */
    function _refcheck()
    {
    	$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    	if ($ref != '' && strpos($ref, BASE_URL) === 0) {
        	return true;
		}
		// httpsの場合
		$ssl_base_url = preg_replace("/^http:\/\//i","https://", BASE_URL);
		if ($ref != '' && strpos($ref, $ssl_base_url) === 0) {
        	return true;
		}
		return false;
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
