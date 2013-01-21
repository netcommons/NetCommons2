<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */


/**
 * Token処理を行うFilter
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

require_once MAPLE_DIR.'/nccore/TokenExtra.class.php';

/**
 * Token処理を行うFilter
 *
 * @package     Maple.filter
 * @author      Ryuji.M
 * @copyright  2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */
class Filter_TokenExtra extends Filter
{
	var $_token = null;
	var $_actionChain = null;
	var $_container = null;
	var $_request = null;
	var $_session = null;
	
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_TokenExtra()
    {
        parent::Filter();
    }

    /**
     * Token処理を行う
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->trace("Filter_TokenExtraの前処理が実行されました", "Filter_TokenExtra#execute");

        $this->_container =& DIContainerFactory::getContainer();

        $this->_session =& $this->_container->getComponent("Session");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
		
		$this->_token =& $this->_container->getComponent("Token");
		if(!isset($this->_token)) {
        	$this->_token =& new TokenExtra;
        	$this->_container->register($this->_token, "Token");
		}
        $this->_token->setSession($this->_session);

		$action_name = $this->_actionChain->getCurActionName();
		$this->_request =& $this->_container->getComponent("Request");
		
		//メイン処理
		$this->setToken($action_name);
        
        $filterChain =& $this->_container->getComponent("FilterChain");
        $filterChain->execute();

        $log->trace("Filter_TokenExtraの後処理が実行されました", "Filter_TokenExtra#execute");
    }
    
    /**
     * Tokenのメイン処理を行う
     * TODO:トークンの効果があるかどうか要テストを実施する必要性あり。
     * @access  public
     * @since   3.0.0
     */
    function setToken($action_name)
    {
    	$attributes = $this->getAttributes();
    	
    	// モジュール単位でbuildするかどうかのフラグ:デフォルトsession_id単位でtokenを保持にbuildしない
    	// タブで複数のウィンドウを立ち上げた場合でも、エラーとならないように処理するため、セキュリティを緩める
    	$mod_build = (isset($attributes["mod_build"]) && $attributes["mod_build"] == _ON) ? _ON : _OFF;
    	
    	//nobuildが指定された場合は、tokenの削除、追加も行わない
    	if (isset($attributes["mode"]) && $attributes["mode"]=="nobuild") {
    		return;
    	}
		
    	//nobuild_parameterが設定されていれば
    	//設定されたパラメータに値があればbuildしない
    	if (isset($attributes["nobuild_parameter"])) {
    		$nobuild_parameter = $this->_request->getParameter($attributes["nobuild_parameter"]);
    		if(isset($nobuild_parameter)) {
    			return;	
    		}
    	}
    	
    	$page_id = $this->_request->getParameter("page_id");
    	$block_id = $this->_request->getParameter("block_id");
    	$module_id = $this->_request->getParameter("module_id");
    	$system_flag = $this->_session->getParameter("_system_flag");
    	$id = $this->_session->getParameter("_id");
    	if($action_name == DEFAULT_ACTION) {
    		/*
    		 * 管理系のtoken削除
    		*/
    		$this->_token->setName("system");
    		$this->_token->remove();
    	} else if($action_name == "control_view_main"){
    		/*
    		 * 一般系のToken削除
    		*/
    		$this->_token->setName("general");
    		$this->_token->remove();
    	}
    	
    	$pathList = explode("_", $action_name);
    	if (isset($attributes["action"])) {
    		$token_prefix = $attributes["action"];
    	} else {
    		//action_nameをTokenにするように修正
    		$token_prefix = $action_name;
    		//$token_prefix .= $pathList[0];
    	}
    	$pages_flag = (strncmp("pages_", $action_name, 6) == 0) ? true : false;
    	$remove_sameid = false;
    	if($pages_flag && $action_name != "pages_view_rescue" && $action_name != "pages_action_rescue") {
        //if(preg_match("/^pages_/",$action_name) && $action_name != "pages_view_rescue" && $action_name != "pages_action_rescue") {
        	$token_value = array($page_id, $token_prefix);
        }else if($action_name == "control_view_main") {
        	$token_value = array($token_prefix);
        } else {
        	if(!$mod_build) {
    			$token_value = array("site");
        	} else if($system_flag) {
        		$token_value = array("system", $id, $token_prefix);
        	} else {
        		$token_value = array("general", $id, $token_prefix);
        	}
        	//同じブロックでbuildする場合、同じブロックのtokenをすべて削除してから作成するかどうかのフラグ
        	//default:true
        	$remove_sameid = true;
        	if (isset($attributes["remove_sameid"])) {
        		$remove_sameid = $attributes["remove_sameid"];
        	}
        }

        $this->_token->setName($token_value);
             
        $modeArray = array();
        
        
        if (isset($attributes["mode"])) {
            $modeArray = explode(",", $attributes["mode"]);
            foreach ($modeArray as $key => $value) {
                $modeArray[$key] = trim($value);
            }
        } else {
            $modeArray[] = "build";
        }

        foreach ($modeArray as $value) {
            switch ($value) {
            case 'check':
                $request =& $this->_container->getComponent("Request");
                if (!$this->_token->check($request)) {
                    $errorList =& $this->_actionChain->getCurErrorList();
                    $errorList->add(TOKEN_ERROR_TYPE,_INVALIDTOKEN);
                    $errorList->setType(TOKEN_ERROR_TYPE);
                }
                break;
            case 'remove':
            	//token削除処理        		
                $this->_token->remove();
                
                break;
            case 'build':
            default:
				$token_value = $this->_token->getValue();
				if($pages_flag && $action_name != "pages_view_rescue" && $action_name != "pages_action_rescue") {
 					//もし、headerが表示される場合、token作成
 					$header = $this->_request->getParameter("_header");
					if(!isset($header) || $header == 0) {					
 						$this->_token->build();
 						
 						////$this->_session->setParameter(array("_pagetoken_name",_DISPLAY_POSITION_CENTER),$token_value);
	 					
	 					$token_header_value = array($this->_session->getParameter('_headercolumn_page_id'), $token_prefix);
	 					$this->_token->setName($token_header_value);
	 					$this->_token->build();
	 					
	 					$token_left_value = array($this->_session->getParameter('_leftcolumn_page_id'), $token_prefix);
	 					////$this->_session->setParameter(array("_pagetoken_name",_DISPLAY_POSITION_LEFT),$token_left_value);
	 					$this->_token->setName($token_left_value);
	 					$this->_token->build();
	 					
	 					$token_right_value = array($this->_session->getParameter('_rightcolumn_page_id'), $token_prefix);
	 					////$this->_session->setParameter(array("_pagetoken_name",_DISPLAY_POSITION_RIGHT),$token_right_value);
	 					$this->_token->setName($token_right_value);
	 					$this->_token->build();
	 					//元に戻す
	 					//$this->_token->setName($url);
 					}
 				} else {
 					if(!empty($token_value) && !$mod_build) {
						// 既に作成済みの場合、作成しない
						return;
 					}
 					if ($remove_sameid) {
	 					$token_name = $this->_token->getName();
	 					//同じページ、ブロックのtokenを削除
	 					if($system_flag) {
			        		$token_value = array("system", $id);
			        	} else {
			        		$token_value = array("general", $id);
			        	}
		 				$this->_token->remove();
	 					//元に戻す
	 					$this->_token->setName($token_name);
 					}
 					
 					$this->_token->build();
 				}
                break;
            }
            
        }
    }
}
?>
