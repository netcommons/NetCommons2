<?php

/**
 *認証を行うFilter
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_AuthCheck extends Filter {

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
	function Filter_AuthCheck() {
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
        $this->_session =& $this->_container->getComponent("Session");

        $this->_className = get_class($this);

        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");
        $this->_prefilter();

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}

	/**
     * プレフィルタ
     * 認証処理を行う
     * @access private
     */
    function _prefilter()
    {
    	$errorList =& $this->_actionChain->getCurErrorList();
    	$action_name = $this->_request->getParameter(ACTION_KEY);
    	$block_id = ($this->_request->getParameter("block_id")) ? $this->_request->getParameter("block_id") : 0;
    	$page_id = ($this->_request->getParameter("page_id")) ? $this->_request->getParameter("page_id") : 0;
    	if ($errorList->isExists()) {
    		//既にエラーがあればそのまま返却
    		return;
    	}

    	$attributes = $this->getAttributes();
    	$recursive_action_name = $this->_actionChain->getRecursive();
		//再帰的にきた場合はデフォルト　チェックしない
    	// TODO:現状、固定値でpreExecuteでpagesアクションならば、AuthCheckを行うようにしているが
    	//      設定で変更できるようにするほうが望ましい
    	$check_flag = true;
    	if($recursive_action_name != "") {
    		$check_flag = false;
    		$recursive_pathList = explode("_", $recursive_action_name);
    		if(isset($recursive_pathList[0]) && $recursive_pathList[0] == "pages" && isset($recursive_pathList[1]) && $recursive_pathList[1] == "view") {
    			$check_flag = true;
    		}
    	}

		$url = defined("CURRENT_URL") ? CURRENT_URL : '';//BASE_URL.INDEX_FILE_NAME.$this->_request->getStrParameters();
    	//$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
    	//$action =  $this->_request->getParameter(ACTION_KEY);

	    //if($action_name == "encryption_view_publickey" || $action_name == "headerinc_view_main") {
	    //TODO:common下すべてチェックを通さないでも問題ないか要チェック
	    $pathList = explode("_", $action_name);
	    if($check_flag == false || $pathList[0] == "common" || $action_name == "encryption_view_publickey" || $action_name == "headerinc_view_main") {
	    	//共通クラス、公開鍵取得アクション,またはヘッダー取得処理なのでチェックしない
	    	return true;
	    } else {
	    	//権限チェック
	    	$authCheck =& $this->_container->getComponent("authCheck");
	    	$_user_id = $this->_session->getParameter("_user_id");
			if(!$authCheck->AuthCheck($action_name,$page_id,$block_id)) {
				if($_user_id == "0") {
					//ログインしてない場合のエラーメッセージ変更
					$errorList->add("Auth_Error", _LOGINAGAIN_MES);
					// リダイレクト先セット
					if($this->_filterChain->hasFilterByName("Redirect")) {
						$redirect =& $this->_filterChain->getFilterByName("Redirect");
						if(	$redirect) {
							$system_flag = $this->_session->getParameter("_system_flag");
							if($system_flag) {
								$redirect_url = "?_sub_action=control_view_main";
								$current_page_id = $this->_request->getParameter("current_page_id");
								if($current_page_id != null && $current_page_id != 0) {
									$redirect_url .= "@current_page_id=". intval($current_page_id);
								}
							} else {
								$redirect_url = "?_sub_action=" . DEFAULT_ACTION;
								$page_id = $this->_request->getParameter("page_id");
								if($page_id != null && $page_id != 0) {
									if($page_id != $this->_session->getParameter("_headercolumn_page_id") &&
									$page_id != $this->_session->getParameter("_leftcolumn_page_id") &&
									$page_id != $this->_session->getParameter("_rightcolumn_page_id")) {
										$redirect_url .= "@page_id=". intval($page_id);
									} else {
										$redirect_url .= "@page_id=". intval($this->_session->getParameter("_main_page_id"));
									}
								}
								$active_action = $this->_request->getParameter("active_action");
								if(isset($active_action)) {
									$parameters =& $this->_request->getParameters();
									foreach($parameters as $key => $parameter) {
										if($key != "page_id" && $key != "action" && !preg_match("/^_/", $key)) {
											$redirect_url .= "@".$key."=". urlencode($parameter);
										}
									}
								}
								if($block_id > 0) {
									$commonMain =& $this->_container->getComponent("commonMain");
									$redirect_url .= '@@'.$commonMain->getTopId($block_id);
								}
							}
							$redirect->setUrl(BASE_URL.INDEX_FILE_NAME."?action=login_view_main_init&error_mes="._ON."&_redirect_url=".$redirect_url);
						}
					}
				} else {
					$errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE,$url));
				}
				$errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
			} else if($action_name != "search_view_main_center" && $pathList[0] != "pages" &&
					$this->_session->getParameter("_system_flag") == _OFF){
				$getdata =& $this->_container->getComponent("GetData");
				$authoritiesView =& $this->_container->getComponent("authoritiesView");
				$pages = $getdata->getParameter("pages");
				$module_id = $this->_request->getParameter("module_id");
				if(isset($pages[$page_id]) && $pages[$page_id]['private_flag'] == _ON && isset($module_id) && $module_id > 0 &&
					$pages[$page_id]['insert_user_id'] == $_user_id) {
					$modulesView =& $this->_container->getComponent("modulesView");
					$module = $modulesView->getModulesById($module_id);
					if((!isset($module) || $module['disposition_flag'] == _ON) && $block_id != 0 ) {
						//
						// プライベートスペースに配置できるかどうか
						// 配置できない場合、ブロック内部にエラーメッセージを出す。
						// ブロック移動、削除は可能。
						//
						$where_params = array("{modules}.module_id" => $module_id);
						$authorities = $authoritiesView->getAuthoritiesModulesLinkByAuthorityId($this->_session->getParameter("_role_auth_id"), $where_params);
						if($authorities === false || !isset($authorities[0]) || $authorities[0]['authority_id'] === null) {
							$errorList->add("Auth_Error", _ACCESS_PRIVATE_SPACE);
							$errorList->setType(VALIDATE_ERROR_NONEREDIRECT_TYPE);
						}
					}
				}
			}
	    }
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
