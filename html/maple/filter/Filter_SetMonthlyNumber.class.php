<?php

/**
 * 月別一覧回数登録フィルタ
 * name         登録名称define名(Globalのdefineとする) 必須
 * view         Actionクラスのreturn名称(default:"success")
 * session      true or false(default:false)　trueの場合、同セッション、かつ、room_idが等しいならばインクリメントを行わない
 * パラメータにpage_id必須
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_SetMonthlyNumber extends Filter {
	
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_actionChain;
    
    var $_request;
     
    var $_response;
    
    var $_getdata;
    
    var $_session;
     
    var $_className;
    
    var $_errorList;
    
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_SetMonthlyNumber() {
		parent::Filter();
	}

	/**
	 * 月別一覧回数登録フィルタ
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
        $this->_getdata =& $this->_container->getComponent("GetData");
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
     * 初期処理を行う
     * @access private
     */
    function _prefilter()
    {
    }
    
    /**
     * ポストフィルタ
     * @access private
     */
    function _postfilter()
    {
    	$attributes = $this->getAttributes();
    	$page_id = $this->_request->getParameter("page_id");
    	$action_name = ($this->_request->getParameter(ACTION_KEY)) ? $this->_request->getParameter(ACTION_KEY) : DEFAULT_ACTION;;
    	
    	$user_id = $this->_session->getParameter("_user_id");
    	
    	if (!isset($attributes["view"])) {
    		//default値
    		$attributes["view"] = "success";
    	}
    	
    	$view = $this->_response->getView();
    	$name = $attributes["name"];
    	$time = timezone_date();
    	$year = substr($time, 0, 4);
    	$month = substr($time, 4, 2);
    	if ($action_name == DEFAULT_ACTION) {
    		//pages
    		$module_id = 0;
    	} else {
    		$modules_obj =& $this->_getdata->getParameter("modules");
    		$pathList = explode("_", $action_name);
    		if(isset($modules_obj[$pathList[0]])) {
    			$module_id = $modules_obj[$pathList[0]]['module_id'];
    		} else {
    			$module_id = 0;
    		}
    	}
    	$pages =& $this->_getdata->getParameter("pages");
    	if($name == "_login_number") {
    		//ログイン回数の場合、page_idが0でもOK
    		$room_id = 0;
    	}else if(!isset($name) || !isset($page_id) || $page_id == 0 || !isset($pages[$page_id])) {
    		//エラー
    		return;	
    	} else {
    		$room_id = $pages[$page_id]['room_id'];
    	}
    	//sessionチェック
    	$inc_flag = true;
    	if (isset($attributes["session"]) && $attributes["session"] == true) {
			$monthlynumber_session = $this->_session->getParameter(array("_session_common", "_monthlynumber", $room_id, $user_id, $year, $month));
			if ( isset($monthlynumber_session) ) {
    			$inc_flag = false;
    		}
    	}
    	if ($attributes["view"] == $view && $inc_flag && $user_id != "0") {
    		//ログインしている場合 インクリメント
    		$monthlynumberAction =& $this->_container->getComponent("monthlynumberAction");
    		$params = array(
    					"user_id" =>$user_id,
						"room_id" => $room_id,
						"module_id" => $module_id,
						"name" => $name,
						"year" => $year,
						"month" => $month
    				);
    		$monthlynumberAction->incrementMonthlynumber($params);
    		
    		if (isset($attributes["session"]) && $attributes["session"] == true) {
    			$this->_session->setParameter(array("_session_common", "_monthlynumber", $room_id, $user_id, $year, $month), 1);
    		}
    	}
    }
}
?>