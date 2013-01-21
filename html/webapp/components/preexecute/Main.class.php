<?php
/**
 * 自サイトのexecuteを呼ぶ際の前処理
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Preexecute_Main {
	/**
     * @var 各セクションの値を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_config;
    
    var $_actionChain;
    var $_filterChain;
    var $_request;
    var $_response;
    var $_session;
    
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Preexecute_Main() {
		$this->_config     = array();
	}
	
	/**
	 * 
	 * @param string:action_name array:params(reqest_parameters)
	 * @access	public
	 */
	function preExecute($actionName, $params=array(), $request_flag=false, $cur_action_name = null) {		
		
		//
        // DIContainerを生成する
        //
        //$container = new DIContainer();
        
        //$request->dispatchAction();
        //$actionName = $request->getParameter(ACTION_KEY);
        
        //
        // 初期ActionをActionChainにセット
        //
        $container =& DIContainerFactory::getContainer();
        //$container =& Controller::_createDIContainer();
        $this->_actionChain =& $container->getComponent("ActionChain");
        if($cur_action_name == null) {
       		$cur_action_name = $this->_actionChain->getCurActionName();
        }
        
        $this->_filterChain =& $container->getComponent("FilterChain");
        $this->_request =& $container->getComponent("Request");
        $this->_response =& $container->getComponent("Response");
        //$this->_session =& $container->getComponent("Session");
        
        //$actionChain =& $container->getComponent("ActionChain");
        $output = isset($params["_output"]) ? $params["_output"] : _ON;
        
        //現在のindex取得
    	//$index = $actionChain->getIndex();
        
        //存在チェック
        //list ($className, $filename) = $this->_actionChain->makeNames($actionName, true);
        //if (!$className) {
        //	
        //	return false;
        //}
        
        //Action_Add
        $buf_recursive_action = $this->_actionChain->getRecursive();
        $this->_actionChain->setRecursive($cur_action_name);
        $this->_actionChain->add($actionName);
        $errorList =& $this->_actionChain->getCurErrorList();
    	if ($errorList->isExists()) {
    		$commonMain =& $container->getComponent("commonMain");
    		$page_id = $this->_request->getParameter("page_id");
    		$url = BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".DEFAULT_ACTION."&page_id=".$page_id;
    		$commonMain->redirectHeader($url);
    	}
        $this->_actionChain->next();
        $addCount = 1;
        
        //
        // リクエストパラメータを取得
        //
        $pre_params = $this->_request->getParameters();
        //$pre_session = $this->_session->getParameters();
        
        if(!isset($params[ACTION_KEY])) {
        	$params[ACTION_KEY] = $actionName;
        }
		
		//main_page_idセット
		//$params['main_page_id'] = $pre_params['main_page_id'];
		if(!$request_flag) {
			$this->_request->clear();
			$this->_request->setParameters($params);
		} else {
			foreach($params as $key=>$param) {
				$this->_request->setParameter($key, $param);
			}
		}
        //$this->_actionChain = new ActionChain;
        //$this->_actionChain->add($actionName);
        
    	$configUtils = new ConfigExtraUtils;
    	$list_arr = $this->_filterChain->copy();
    	$this->_filterChain->clear();
    	//
        // 実行すべきActionがある限り繰り返す
        //
        while ($this->_actionChain->hasNext()) {
            //
	        // 設定ファイルを読み込む
	        //
	        $configUtils->execute(true); 
            //
            // 設定ファイルを元にFilterChainを組み立てて、実行
            //
            //$this->_filterChain =& $container->getComponent("FilterChain");
            $this->_filterChain->build($configUtils);
            $this->_filterChain->execute();
            $this->_filterChain->clear();

            //
            // 後始末および次のActionへ
            //
            $configUtils->clear();

            $this->_actionChain->next();
            $addCount++;
            
        }
        
        $this->_actionChain->setRecursive($buf_recursive_action);
        
        //元に戻す
        for($i=1; $i < $addCount; $i++) {
        	$this->_actionChain->array_pop();
        	$this->_actionChain->previous();
        }
        $this->_actionChain->previous();
        
        $this->_filterChain->paste($list_arr);
        $this->_request->clear();
        $this->_request->setParameters($pre_params);
        //$this->_session->setParameters($pre_session);
        
		if(isset($output) && $output == 0) {
        	return $this->_response->getResult();
		} else {
			return $this->_response->getView();
		}
	}
	
}
?>
