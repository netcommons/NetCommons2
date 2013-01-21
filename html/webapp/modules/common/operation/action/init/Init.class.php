<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール移動-ショートカット-コピーを実行
 *   モジュール独自のテーブルにおける操作は、Action(move_action等)内で行うこと
 * ------------------------------------------------------------------------------------------------------------------------------
 * mode="move"
 * １．移動先,移動元のpage_idのpagesテーブルのshow_count++（block_idの指定がなければ、移動元のshow_countはインクリメントしない）
 * ２．移動先のpage_idよりblocksテーブルにデータを追加し、追加したblock_id(move_block_id)をActionに渡す
 *     （一列目の先頭行に追加）
 * ３．リクエストパラメータblock_idがある場合、blocksテーブルから削除される:Pages_Actionblock_Deleteblockを呼ぶのみ
 * 
 * mode="shortcut"
 * １．移動先のpage_idのpagesテーブルのshow_count++
 * ２．action="move"の２．と同様
 * 
 * mode="copy":shortcutと同様
 * 
 * ------------------------------------------------------------------------------------------------------------------------------
 * リクエストパラメータ
 * mode：必須 "move" or "shortcut" or "copy"
 * block_id：必須
 * page_id：必須（ページID）
 * module_id：必須
 * unique_id：必須(bbs_id等)
 * move_page_id：必須
 * 
 * move_block_id(Actionによりセット)
 * ------------------------------------------------------------------------------------------------------------------------------
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Operation_Action_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $mode = null;
	var $block_id = null;
	var $page_id = null;
	var $module_id = null;
	var $unique_id = null;
	var $show_count = null;
	//var $room_id = null;
	
	var $move_page_id = null;
	
	var $topmargin = null;
	var $rightmargin = null;
	var $bottommargin = null;
	var $leftmargin = null;
	
    // 使用コンポーネントを受け取るため
    var $modulesView = null;
    var $actionChain = null;
    var $db = null;
    var $pagesAction = null;
    var $session = null;
    var $blocksView = null;
    var $preexecute = null;
	var $blocksAction = null;
	var $pagesView = null;
	
	// バリデートによりセット
	var $block = null;
	
	// 値をセットするため
	var $move_block_id = null;
    
    var $_classname = "Common_Operation_Action_Init";
    var $errorList = null;
    
    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$module = $this->modulesView->getModulesById($this->module_id);
    	if($module === false) {
    		return 'error';
    	}
    	$this->errorList =& $this->actionChain->getCurErrorList();
    	//
    	// アクションチェック
    	//
    	if($this->_chkAction()) {
			// Error
 			$this->errorList->add($this->_classname, _INVALID_AUTH);
    		return 'error';
		}
		
		//
    	// 移動先のpage_idのpagesテーブルのshow_count++
    	//
    	$move_page = $this->_incShowCount($this->move_page_id);
		if($move_page === false || $this->move_page_id == _SELF_TOPPUBLIC_ID) {
			// Error
 			$this->errorList->add($this->_classname, _INVALID_INPUT);
    		return 'error';
		}
		
		//
		// 移動先のpage_idよりblocksテーブルにデータを追加し、追加したblock_id(move_block_id)をActionに渡す
 		// （一列目の先頭行に追加）
		//
		if($this->mode == "shortcut") $shortcut_flag = _ON;
		else  $shortcut_flag = _OFF;
		$move_block_id = $this->_addBlock($this->move_page_id, $module, $this->block, $shortcut_flag);
		if($move_block_id === false) {
			// Error
 			return 'error';
		}
		$buf_pages = $this->pagesView->getPageById($this->page_id);
    	if($buf_pages === false || !isset($buf_pages['page_id'])) return 'error';
		
		$preexecute_params = array(
		    							"mode" => $this->mode,
		    							"block_id" => $this->block_id,
		    							"page_id" => $this->page_id,
		    							"room_id" => $buf_pages['room_id'],
		    							"module_id" => $this->module_id,
		    							"unique_id" => $this->unique_id,
		    							"move_page_id" => $this->move_page_id,
		    							"move_block_id" => $move_block_id,
		    							"move_room_id" => $move_page['room_id'],
		    							"shortcut_flag" => $shortcut_flag
		    						);
		    						
    	//
		// move_action or copy_action or shortcut_actionを呼ぶ
		//
		$result = $this->preexecute->preExecute($module[$this->mode.'_action'], $preexecute_params);
		if($result !== true && $result !== "true") {
			$this->errorList->add($this->_classname, sprintf(_INVALID_ACTION,$module[$this->mode.'_action']));
    		return 'error';
		}
    	return 'success';
    }
    
    /**
     * アクションチェック
     * @return boolean
     * @access  private
     */
    function _chkAction()
    {
    	if($this->mode != "shortcut" &&
    		$this->mode != "copy" &&
    		$this->mode != "move") {
    		return false;
    	}
    	if(($this->mode == "shortcut" && !isset($module['shortcut_action'])) ||
    		($this->mode == "copy" && !isset($module['copy_action'])) ||
		    ($this->mode == "move" && !isset($module['move_action']))
			) {
			// Error
			return false;
		}
		return true;
    }
    /**
     * 移動先のpage_idのpagesテーブルのshow_count++
     * @param  int $page_id
     * @return pages or false
     * @access  private
     */
    function &_incShowCount($page_id)
    {
    	$where_params = array("page_id"=>intval($page_id));
		$pages =& $this->db->selectExecute("pages", $where_params);
		if ($pages === false || !isset($pages[0])) {
	       	return false;
		}
		
 		// 移動先のpage_idのpagesテーブルのshow_count++
 		$this->pagesAction->updShowCount($page_id);
 		
 		return $pages[0];
    }
    
    /**
     * 移動先のpage_idのpagesテーブルのshow_count++
     * @param  int   $page_id
     * @param  array $module
     * @param  array $$block
     * @param  int   $shortcut_flag
     * @return block_id or false
     * @access  private
     */
    function _addBlock($page_id, &$module, &$block, $shortcut_flag)
    {
    	if($this->mode == "move") {
	    	$preexecute_params = array(
			    							"action" => "pages_actionblock_deleteblock",
			    							"_show_count" => $this->show_count,
			    							"page_id" => $this->page_id,
			    							"module_id" => $this->module_id,
			    							"block_id" => $block['block_id']
			    						);
			    						
	    	$result = $this->preexecute->preExecute("pages_actionblock_deleteblock", $preexecute_params);
			if($result === false || $result === "false") {
				return false;
			}
    	}
		
    	$block_obj = array(
    		"block_id" => $block['block_id'],
			"page_id" => $page_id,
			"module_id" => $module['module_id'],
			"site_id" => $this->session->getParameter("_site_id"),
			"root_id" => 0,
			"parent_id" => 0,
			"thread_num" => 0,
			"col_num" => 1,
			"row_num" => 1,
			"url" => $block['url'],
			"action_name" => $block['action_name'],
			"parameters" => "",
			"block_name" => $block['block_name'],
			"theme_name" => $block['theme_name'],
			"temp_name" => $block['temp_name'],
			"leftmargin" => $block['leftmargin'],
			"rightmargin" => $block['rightmargin'],
			"topmargin" => $block['topmargin'],
			"bottommargin" => $block['bottommargin'],
			"min_width_size" => $block['min_width_size'],
			"shortcut_flag" => $shortcut_flag,
			"copyprotect_flag" => _OFF,
			"display_scope" => _DISPLAY_SCOPE_NONE
		);
		if($this->mode != "move") {
			unset($block_obj['block_id']);
			$result = $this->blocksAction->insBlock($block_obj);
			$block_id = $result;
		} else {
			$result = $this->db->insertExecute("blocks", $block_obj, true);
			$block_id = $block['block_id'];
		}
		if($result === false) {
			return false;
		}
		
		
		//$block_id = $this->blocksAction->insBlock($block_obj);
		//if($block_id === false) {
		//	return false;
		//}

		$time = timezone_date();
    	//$site_id = $this->session->getParameter("_site_id");
        $user_id = $this->session->getParameter("_user_id");
        $user_name = $this->session->getParameter("_handle");

		//行＋１
	    $params = array(
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"page_id" => $block_obj['page_id'],
			"block_id" => $block_id,
			"parent_id" => $block_obj['parent_id'],
			"col_num" => $block_obj['col_num'],
			"row_num" => 0
		);

		$result = $this->blocksAction->incrementRowNum($params);
		if($result === false) {
			$this->blocksAction->delBlockById($block_id);
			return false;
		}
		return $block_id;
    }
}
?>
