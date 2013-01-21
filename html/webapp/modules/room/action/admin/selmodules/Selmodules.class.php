<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 使用可能モジュール配置修正
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Selmodules extends Action
{
	// リクエストパラメータを受け取るため
    var $edit_current_page_id = null;
    var $parent_page_id = null;
    
    var $enroll_modules = null;
    
	// バリデートによりセット
	var $parent_page = null;
	var $page = null;
	    
    // 使用コンポーネントを受け取るため
    var $pagesView = null;
    var $pagesAction = null;
    var $modulesView = null;
    var $blocksView = null;
    var $blocksAction = null;
    var $preexecuteMain = null;
    var $db = null;
    var $request = null;
    
	//値をセットするため
	
    /**
     * 使用可能モジュール配置修正
     *
     * @access  public
     */
    function execute()
    {
    	if($this->edit_current_page_id != 0) {
    		$log =& LogFactory::getLog();
    		// ----------------------------------------------------------------------
			// --- ページモジュールリンクテーブル変更                             ---
			// ----------------------------------------------------------------------
			//if(isset($this->enroll_modules)) {
				$where_params = array(
		    			"room_id" => $this->edit_current_page_id
		    		);
				$page_modules =& $this->pagesView->getPageModulesLink($where_params, null, array($this, "_fetchcallbackModulesLink"));
				$error_flag = false;
				if(isset($this->enroll_modules)) {
			    	foreach($this->enroll_modules as $module_id) {
			    		if(!isset($page_modules[$module_id])) {
			    			$params = array(
				    			"room_id" => $this->edit_current_page_id,
				    			"module_id" => $module_id
				    		);
			    			//insert
			    			$result = $this->pagesAction->insPagesModulesLink($params);
			    			if($result === false) {
					    		$error_flag = true;	
					    	}
			    		}
			    		unset($page_modules[$module_id]);
			    	}
				}
		    	$page_list =& $this->db->selectExecute("pages", array("room_id" => $this->edit_current_page_id) ,null, null, null, array($this, "_fetchcallback"));
    			if($page_list === false) {
    				return 'error';	
    			}
    			$blocks =& $this->blocksView->getBlockByPageId($page_list);
    	
		    	//ページモジュールリンクテーブル削除
		    	foreach($page_modules as $page_module) {
		    		$where_params = array(
		    			"room_id" => $page_module['room_id'],
		    			"module_id" => $page_module['module_id']
		    		);
		    		$result = $this->pagesAction->delPagesModulesLink($where_params);
		    		if($result === false) {
			    		$error_flag = true;	
			    	}
			    	//
			    	// モジュール削除関数を呼び出す
			    	//
			    	$module =& $this->modulesView->getModulesById($page_module['module_id']);
			    	$delete_action = $module['delete_action'];
			    	if($delete_action != "" && $delete_action != null) {
		    			$result = $this->pagesAction->delFuncExec($page_module['room_id'], $module, $delete_action);
		    		}
		    	}
		    	if(is_array($blocks) && count($blocks) > 0) {
		    		foreach($blocks as $block) {
		    			$module_id = $block['module_id'];
		    			if(isset($page_modules[$module_id])) {
		    				//block削除処理
		    				$preexecute_params = array(
			    									"block_id" => $block['block_id'],
			    									"page_id"=> $block['page_id']
			    								);
			    			$result = $this->preexecuteMain->preExecute("pages_actionblock_deleteblock", $preexecute_params);
			    			//if($result === false || $result === "false") {
			    			//	$log->warn(sprintf("%sの削除アクション(%s)が失敗しました。","pages", "pages_actionblock_deleteblock"), "Room_Action_Admin_Create_Selectmodules#execute");
			    			//}
		    			}
		    		}
		    	}
		    	if($error_flag) {
		    		return 'error';	
		    	}
			//}
    	}
    	// ----------------------------------------------------------------------
		// --- 終了処理　　　　　                                             ---
		// ----------------------------------------------------------------------
		// リスト表示のリクエストパラメータセット
		if(!isset($this->parent_page)) {
			$this->request->setParameter("show_space_type", $this->page['space_type']);
			$this->request->setParameter("show_private_flag", $this->page['private_flag']);
			$this->request->setParameter("show_default_entry_flag", $this->page['default_entry_flag']);
		} else {
			$this->request->setParameter("show_space_type", $this->parent_page['space_type']);
			$this->request->setParameter("show_private_flag", $this->parent_page['private_flag']);
			$this->request->setParameter("show_default_entry_flag", $this->parent_page['default_entry_flag']);
		}
		
    	return 'success';
    }
    
    
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchcallbackModulesLink($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['module_id']] = $row;
		}
		return $ret;
	}
	
	
    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			array_push ( $ret, $row['page_id']);
		}
		return $ret;
	}
}
?>
