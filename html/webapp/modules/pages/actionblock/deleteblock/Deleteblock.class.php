<?php

class Pages_Actionblock_Deleteblock extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	//var $show_count = null;
	
	// 使用コンポーネントを受け取るため
	var $blocksAction = null;
	var $blocksView = null;
	var $pagesAction = null;
	var $getData = null;
	var $session = null;
	var $db = null;
	var $actionChain = null;
	
	function execute()
	{
    	//
    	//削除ブロック取得
    	// getDataから取得した場合、Uninstall時に複数ブロック削除した場合におかしくなるため
    	// コメント
    	//$blocks_obj =& $this->getData->getParameter("blocks");
    	//if(!$blocks_obj) {
    		$blocks =& $this->blocksView->getBlockById($this->block_id);
    		$blocks_obj[$this->block_id] =& $blocks;
    	//}
		$time = timezone_date();
    	//$site_id = $this->session->getParameter("_site_id");
        $user_id = $this->session->getParameter("_user_id");
        $user_name = $this->session->getParameter("_handle");
        
    	//表示カウント＋＋
		$this->pagesAction->updShowCount($this->page_id);
		
		//$this->show_count++;
		// --------------------------------------
		// --- 前詰め処理(移動元)		　   ---
		// --------------------------------------
	    $params = array(
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"page_id" => $blocks_obj[$this->block_id]['page_id'],
			"block_id" => $blocks_obj[$this->block_id]['block_id'],
			"parent_id" => $blocks_obj[$this->block_id]['parent_id'],
			"col_num" => $blocks_obj[$this->block_id]['col_num'],
			"row_num" => $blocks_obj[$this->block_id]['row_num']
		);
		
		$result = $this->blocksAction->decrementRowNum($params);
		if(!$result) {
			return 'error';
		}
		
		$params_row_count = array( 
			"page_id" => $blocks_obj[$this->block_id]['page_id'],
			"parent_id" => $blocks_obj[$this->block_id]['parent_id'],
			"col_num" => $blocks_obj[$this->block_id]['col_num']
		);
		$count_row_num = $this->blocksView->getCountRownumByColnum($params_row_count);			
		if($count_row_num == 1) {
			//移動前の列が１つしかなかったので
			//列--
			$params = array(
				"update_time" =>$time,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name,
				"page_id" => $blocks_obj[$this->block_id]['page_id'],
				"block_id" => $blocks_obj[$this->block_id]['block_id'],
				"parent_id" => $blocks_obj[$this->block_id]['parent_id'],
				"col_num" => $blocks_obj[$this->block_id]['col_num']
			);
			$result = $this->blocksAction->decrementColNum($params);
			if(!$result) {
				return 'error';
			}
		}
		
        // --------------------------------------
		// --- ブロック削除処理     	 　   ---
		// --------------------------------------
		$result = $this->_deleteBlock($blocks_obj[$this->block_id]);
		if(!$result) {
			return 'error';
		}
		
		//グループ化した空ブロック削除処理
		if($count_row_num == 1) {
			$this->blocksAction->delGroupingBlock($blocks_obj[$this->block_id]['parent_id']);
		}	
		return 'success';
	}
	
	/**
	 * ブロック削除処理
	 * 再帰的に処理
	 * @access	private
	 * @param object block_objct
	 * @param boolean shortcut_flag
	 * @return	boolean true or false
	 **/
	function _deleteBlock(&$block_obj, $shortcuts_flag = false)
	{
		$action_name = $block_obj['action_name'];
		$recursive_action_name = $this->actionChain->getRecursive();
		$recursive_flag = false;
		if($recursive_action_name == "common_operation_action_init") {
			$recursive_flag = true;
		}
		if($action_name == "pages_view_grouping") {
			// --------------------------------------
			// --- 子供に位置するモジュール削除   ---
			// --------------------------------------
			//子供取得
			$child_blocks_obj = $this->blocksView->getBlockByParentId($block_obj['block_id']);
			foreach($child_blocks_obj as $child_block_obj) {
				//再帰処理
				$this->_deleteBlock($child_block_obj);
			}
		} else if($recursive_flag == false){
			// -------------------------------------
			// --- 削除関数                      ---
			// -------------------------------------
			$this->blocksAction->delFuncExec($block_obj['block_id']);
		}
		// -------------------------------------
		// --- ブロック削除					 ---
		// -------------------------------------
		$this->blocksAction->delBlockById($block_obj['block_id']);
		
		// --------------------------------------
		// --- css_files削除処理     	 　   ---
		// --------------------------------------
		if($recursive_flag == false) {
			$result = $this->db->deleteExecute("css_files", array("block_id" => $block_obj['block_id']));
			if(!$result) {
				return 'error';
			}
		}
		// ----------------------------------------------------------------------
		// --- ショートカット先削除                                           ---
		// ----------------------------------------------------------------------
		if($shortcuts_flag == false) {
			//ショートカットのショートカットは許さないためチェックしない
			$shortcuts =& $this->db->selectExecute("shortcut", array("shortcut_block_id" => $block_obj['block_id']));
			if($shortcuts !== false && isset($shortcuts[0])) {
				foreach($shortcuts as $shortcut) {
					$blocks =& $this->blocksView->getBlockById($shortcut['shortcut_block_id']);
					if(count($blocks) > 0) $this->_deleteBlock($blocks, true);
				}
			}
			$this->db->deleteExecute("shortcut", array("shortcut_block_id" => $block_obj['block_id']));
		}
		return true;
	}	
}
?>
