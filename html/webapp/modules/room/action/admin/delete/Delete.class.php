<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム削除処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Delete extends Action
{
	// パラメータを受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	
	// バリデートによりセット
	var $parent_page = null;
	var $page = null;
	
	// 使用コンポーネントを受け取るため
    var $pagesView = null;
    var $pagesAction = null;
    var $blocksView = null;
    var $modulesView = null;
    var $preexecuteMain = null;
    var $blocksAction = null;
    var $db = null;
    var $uploadsAction = null;
	
    /**
     * ルーム削除処理
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->_delRoom(intval($this->edit_current_page_id));
    	if($result === false) return 'error';
    	return 'success';
    }
    
    /**
	 * 削除処理
	 * @param int current_page_id
	 * @return boolean 
	 * @access	private
	 */
	function _delRoom($current_page_id) {
		$log =& LogFactory::getLog();
    	
		// ----------------------------------------------------------------------
		// --- サブグループ    　　　　　　　　　　　　　                     ---
		// ----------------------------------------------------------------------
		$sub_pages =& $this->db->selectExecute("pages", array("parent_id" => $current_page_id, "room_id = page_id" => null));
    	if($sub_pages === false) {
    		return false;
    	}
    	if(count($sub_pages) > 0) {	
    		//サブグループあり
    		//それぞれ、ルーム削除を再帰的に行う
    		foreach($sub_pages as $sub_page) {
    			$result = $this->_delRoom(intval($sub_page['page_id']));
    			if(!$result) return false;
    		}
    	}
    	
    	// ルームに所属しているページ一覧取得
		$result = $this->db->selectExecute("pages", array("room_id" => $current_page_id) ,null, null, null, array($this, "_fetchcallback"));
    	if($result === false) {
    		return false;
    	}
    	list($page_list, $pages) = $result;
    	
    	// ----------------------------------------------------------------------
		// --- ブロック削除アクション-削除アクション実行                     ---
		// ----------------------------------------------------------------------
		$blocks =& $this->blocksView->getBlockByPageId($page_list);
		$modules =& $this->modulesView->getModulesByUsed($current_page_id,0,0,true);
		if(is_array($blocks) && count($blocks) > 0) {
			foreach($blocks as $block) {
				$block_delete_action = $modules[$block['module_id']]['block_delete_action'];
				if($block_delete_action != "" && $block_delete_action != null) {
					//ブロック削除アクション
					$result = $this->blocksAction->delFuncExec($block['block_id'], $block, $block_delete_action);
					//if($result === false) {
						// 削除アクションの返り値をエラーとしない
				    	// モジュールの作り方によってルームの削除ができなくなると問題なので。
						//$log->warn(sprintf("%sのブロック削除アクション(%s)が失敗しました。",$modules[$block['module_id_id']]['module_name'], $block_delete_action), "Room_Action_Admin_Delete_Init#execute");
						//return false;
					//}
				}
			}
		}
		if(is_array($modules) && count($modules) > 0) {
	    	foreach($modules as $module) {
	    		$delete_action = $module['delete_action'];
	    		// delete_action処理
	    		if($delete_action != "" && $delete_action != null) {
	    			$result = $this->pagesAction->delFuncExec($current_page_id, $module, $delete_action);
	    		}
	    	}
		}
		// ----------------------------------------------------------------------
		// --- 添付ファイル更新処理                                           ---
		// ----------------------------------------------------------------------
		$result = $this->uploadsAction->delUploadsByRoomid($current_page_id);
		if($result === false) return false;	
	
    	// ----------------------------------------------------------------------
		// --- ページモジュールリンクテーブル削除                             ---
		// ----------------------------------------------------------------------
		$result = $this->pagesAction->delPagesModulesLink(array("room_id" => $current_page_id));
		if($result === false) return false;
		
    	// ----------------------------------------------------------------------
		// --- ページユーザリンクテーブル削除                                 ---
		// ----------------------------------------------------------------------
		$result = $this->pagesAction->delPageUsersLink(array("room_id" => $current_page_id));
		if($result === false) return false;
		
		foreach($page_list as $page_id) {
			// ----------------------------------------------------------------------
			// --- ブロックテーブル削除                                           ---
			// ----------------------------------------------------------------------
	    	$result = $this->pagesAction->delPageById($page_id, "blocks");
			if(!$result) {
				return false;	
			}

			// ----------------------------------------------------------------------
			// --- 非表示メニューテーブル削除                                           ---
			// ----------------------------------------------------------------------
			if(!$this->pagesAction->delPageById($page_id, 'menu_detail')) {
				return false;	
			}
			if(!$this->pagesAction->delPageById($page_id, 'mobile_menu_detail')) {
				return false;	
			}

			// ----------------------------------------------------------------------
			// --- ページテーブル削除                                             ---
			// ----------------------------------------------------------------------
	    	$result = $this->pagesAction->delPageById($page_id);
			if(!$result) {
				return false;	
			}
			// ----------------------------------------------------------------------
			// --- ページスタイル削除                                             ---
			// ----------------------------------------------------------------------
			if(!$this->pagesAction->delPageStyleById($page_id)) {
				return false;	
			}
			
			// ----------------------------------------------------------------------
			// --- pages_meta_infテーブル削除                                    ---
			// ----------------------------------------------------------------------
			if(!$this->pagesAction->delPageMetaInfById($page_id)) {
				return false;
			}
			
			// ----------------------------------------------------------------------
			// --- 表示順decrement                                               ---
			// ----------------------------------------------------------------------
			if($current_page_id == $page_id) {
				if(!$this->pagesAction->decrementDisplaySeq($pages[$page_id]['parent_id'], $pages[$page_id]['display_sequence'], $pages[$page_id]['lang_dirname'])) {
					return false;	
				}
			}
		}
		// ----------------------------------------------------------------------
		// --- ショートカット先削除                                            ---
		// ----------------------------------------------------------------------
		$shortcuts =& $this->db->selectExecute("shortcut", array("room_id" => $current_page_id));
		if(isset($shortcuts[0])) {
			$preexecute_params = array(
	    							"mode" => "delete",
	    							"shortcut_flag" => _ON
	    						);
			foreach($shortcuts as $shortcut) {
				//TODO shortcut_site_idにより、他サイトを考慮した場合は、他サイトの削除処理を行うこと　現状、未実装
				$preexecute_params["block_id"] = $shortcut["shortcut_block_id"];
				$preexecute_params["page_id"] = $shortcut["shortcut_page_id"];
				$preexecute_params["room_id"] = $shortcut["shortcut_room_id"];
				//delete_blockアクションを呼ぶ
				$result = $this->preexecuteMain->preExecute("pages_actionblock_deleteblock", $preexecute_params);
    			if($result === false || $result === "false") {
    				//$errorList->add($this->_classname, sprintf(_INVALID_ACTION,"pages_actionblock_deleteblock"));
		    		return false;
    			}
			}
			$result = $this->db->deleteExecute("shortcut", array("room_id" => $current_page_id));
			if($result === false){
				//$errorList->add($this->_classname, sprintf(_INVALID_ACTION,"pages_actionblock_deleteblock"));
	    		return false;
			}
		}
    	return true;
	}
    
    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function _fetchcallback($result) {
		$ret = array();
		$parent_ret = array();
		while ($row = $result->fetchRow()) {
			$ret[] = $row['page_id'];
			$page_ret[$row['page_id']] = $row;
		}
		return array($ret, $page_ret);
	}
}
?>