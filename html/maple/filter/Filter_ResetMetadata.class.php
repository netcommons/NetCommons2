<?php

 /**
 * Metaデータ再セット用
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_ResetMetadata extends Filter {
	
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_actionChain;
    
    var $_request;
    
    var $_session;
     
    var $_className;
    
    var $_getdata;
    
    var $_errorList;
    
    var $_pagesView;
    
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_ResetMetadata() {
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
        $this->_getdata =& $this->_container->getComponent("GetData");
        $this->_pagesView =& $this->_container->getComponent("pagesView");
        
        $this->_className = get_class($this);
        
        $this->_errorList =& $this->_actionChain->getCurErrorList();
    	
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
    	if ($this->_errorList->isExists()) {
    		//既にエラーがあればそのまま返却
    		return;	
    	}
    	if(DEFAULT_ACTION == 'install_view_main_init') {
    		return;
    	}
    	$pages = $this->_getdata->getParameter("pages");
    	$page_id = $this->_session->getParameter("_main_room_id");
    	$main_page_id = $this->_session->getParameter("_main_page_id");
    	$db =& $this->_container->getComponent("DbObject");
    	$action_name = $this->_actionChain->getCurActionName();
    	$active_center = $this->_request->getParameter("active_center");
    	
    	//
    	// metaデータ再セット
    	//
    	if(isset($pages[$main_page_id]) && isset($pages[$main_page_id]['permalink']) && 
    		($action_name == DEFAULT_ACTION || $action_name == "dialog_pagestyle_view_edit_init" || 
    			$action_name == "dialog_pagestyle_action_edit_init") && 
    		!isset($active_center)) {
    		$where_params = array(
	    		"page_id" => $main_page_id
	    	);
	    	$pages_meta_inf = $db->selectExecute("pages_meta_inf", $where_params, null, 1, 0);
			if($pages_meta_inf === false) {
				//$this->_errorList->add("ResetMetadata_Error", sprinf(_INVALID_SELECTDB, "pages_meta_inf"));	
				// テーブルがなければそのまま返却
				return;
			}
    		$page_name = $pages[$main_page_id]['page_name'];
    		$meta = $this->_pagesView->getDafaultMeta($pages[$main_page_id]);
    		if(isset($pages_meta_inf[0]['title'])) {
    			$meta['title'] = $pages_meta_inf[0]['title'];
    		}
    		if(isset($pages_meta_inf[0]['meta_description'])) {
    			$meta['meta_description'] = $pages_meta_inf[0]['meta_description'];
    		}
    		if(isset($pages_meta_inf[0]['meta_keywords'])) {
    			$meta['meta_keywords'] = $pages_meta_inf[0]['meta_keywords'];
    		}
    		$room_name = "";
    		if(isset($meta['title']) && strpos($meta['title'], "{X-ROOM}") !== false) {
				if(!isset($pages[$page_id])) {
					$page = $this->_pagesView->getPageById($pages[$main_page_id]['room_id']);
					$room_name = $page['page_name'];
				} else {
					$room_name = $pages[$page_id]['page_name'];
				}
			}
			
    		if($pages[$main_page_id]['private_flag'] == _ON) {
				$handle =  $pages[$main_page_id]['insert_user_name'];
				
				if(isset($meta['title'])) {
					// {X-USER}{X-PAGE}
					$meta['title'] = str_replace("{X-USER}", $handle, $meta['title']);
					$meta['title'] = str_replace("{X-ROOM}", $room_name, $meta['title']);
					$meta['title'] = str_replace("{X-PAGE}", $page_name, $meta['title']);
				}
				if(isset($meta['meta_description'])) {
					// {X-USER}{X-ROOM}{X-PAGE}
					$meta['meta_description'] = str_replace("{X-USER}", $handle, $meta['meta_description']);
					if(strpos($meta['title'], "{X-ROOM}")) {
						$meta['meta_description'] = str_replace("{X-ROOM}", $room_name, $meta['meta_description']);
					}
					$meta['meta_description'] = str_replace("{X-PAGE}", $page_name, $meta['meta_description']);
				}
			} else if($pages[$main_page_id]['space_type'] == _SPACE_TYPE_GROUP) {
				if(isset($meta['title'])) {
					// {X-ROOM}{X-PAGE}
					$meta['title'] = str_replace("{X-ROOM}", $room_name, $meta['title']);
					$meta['title'] = str_replace("{X-PAGE}", $page_name, $meta['title']);
				}
			} else {
				// {X-PAGE}
				$meta['title'] = str_replace("{X-PAGE}", $page_name, $meta['title']);
			}
			// データセット
    		$this->_session->setParameter("_meta",$meta);
    		if(isset($meta['title'])) {
    			$this->_session->setParameter("_page_title", $meta['title']);
    		}
    	} else {
    		$meta = $this->_session->getParameter("_meta");
    		$meta['meta_description'] = "";
    		$meta['meta_keywords'] = "";
    		$this->_session->setParameter("_meta",$meta);
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
