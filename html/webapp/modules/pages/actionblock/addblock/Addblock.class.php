<?php
/**
 * ブロック追加処理
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_Actionblock_Addblock extends Action
{
	// 使用コンポーネントを受け取るため
	var $blocksView = null;
	var $pagesView = null;
	var $blocksAction = null;
	var $pagesAction = null;
	var $modulesView = null;
	var $session = null;
	var $actionChain = null;
	var $preexecute = null;
	var $getdata = null;
	
	//var $block_obj = array();
	//var $page_obj = array();

	//リクエストパラメータを受け取るため
	var $module_id = null;		//必須
	var $page_id = null;		//必須
	var $topmargin = null;
	var $rightmargin = null;
	var $bottommargin = null;
	var $leftmargin = null;
	
	//値をセットするため
	var $add_block_id = null;
	var $add_page_id = null;
	var $add_room_id = null;
	var $add_module_id = null;
	var $edit_action_name = null;
	var $theme_name = null;

	function execute()
	{
		// メモリ量計測
		// pages_view_mainでメモリ計測しないと意味がないためコメント
		//$memory_limit =intval(str_replace("M", "000000", ini_get('memory_limit')));
		//$memory_current = intval(str_replace("M", "", memory_get_usage()));
		//if($memory_limit <= $memory_current + 200000) {
		//	// 残り0.2Mに満たない場合、それ以上、モジュールを追加させない
		//	$errorList =& $this->actionChain->getCurErrorList();
		//	$errorList->add(get_class($this), sprintf(PAGES_LIMIT_MEMORY,ini_get('memory_limit')));
    	//	return 'error';
		//}
		
		$pages = $this->getdata->getParameter("pages");
		//表示カウント＋＋
    	$this->pagesAction->updShowCount($this->page_id);

    	$module_obj =& $this->modulesView->getModulesById($this->module_id);
    	if(!$module_obj) {
    		$errorList =& $this->actionChain->getCurErrorList();
			$errorList->add("module_id". $this->module_id, PAGES_NOEXISTS_MODULE);
    		return 'error';
    	}

    	$block_obj = array(
			"page_id" => $this->page_id,
			"module_id" => $this->module_id,
			"site_id" => $this->session->getParameter("_site_id"),
			"root_id" => 0,
			"parent_id" => 0,
			"thread_num" => 0,
			"col_num" => 1,
			"row_num" => 1,
			"url" => "",
			"action_name" => $module_obj['action_name'],
			"parameters" => "",
			"block_name" => $module_obj['module_name'],
			"theme_name" => $module_obj['theme_name'],
			"temp_name" => $module_obj['temp_name'],
			"leftmargin" => $this->leftmargin,
			"rightmargin" => $this->rightmargin,
			"topmargin" => $this->topmargin,
			"bottommargin" => $this->bottommargin,
			"min_width_size" => $module_obj['min_width_size'],
			"shortcut_flag" => 0,
			"copyprotect_flag" => 0,
			"display_scope" => _DISPLAY_SCOPE_NONE
		);
		
		$_theme_list = $this->session->getParameter("_theme_list");
        if($block_obj['theme_name'] != null && $block_obj['theme_name'] != "") {
			$this->theme_name = $block_obj['theme_name'];
		} else {
			$this->theme_name = $_theme_list[$pages[$this->page_id]['display_position']];
        }
        // 枠なしならば、タイトルも表示しない
        if($this->theme_name == "noneframe") {
        	$block_obj['block_name'] = "";
        }
        
		$block_id = $this->blocksAction->insBlock($block_obj);
		if(!$block_id) {
			return 'error';
		}

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
		if(!$result) {
			return 'error';
		}
		//$this->session->setParameter("_editing_block_id", $block_id);
		
		if($module_obj && isset($module_obj['block_add_action'])) {
			$block_add_action = $module_obj['block_add_action'];
		} else {
			$block_add_action = "";
		}
		//add_blockをViewも指定する形に変更するためコメント
		/*		
		if($block_add_action != "") {
			//
			//追加ブロック関数呼び出し
			//
			$pathList   = explode("_", $block_add_action);
	        $ucPathList = array_map('ucfirst', $pathList);

	        $basename = ucfirst($pathList[count($pathList) - 1]);

	        $actionPath = join("/", $pathList);
	        $className  = join("_", $ucPathList);
	        $filename   = MODULE_DIR . "/${actionPath}/${basename}.class.php";
    		if (@file_exists($filename)) {
				$params = array("action" =>$block_add_action, "block_id" =>$block_id, "page_id" =>$block_obj['page_id'], "room_id" =>$pages[$this->page_id]['room_id'],"module_id" =>$block_obj['module_id']);
				$result = $this->preexecute->preExecute($block_add_action, $params);
				if(!$result) {
					//TODO:現状、処理しない
				}
    		}
		}
		*/
		$this->add_block_id = $block_id;
		$this->add_page_id = $block_obj['page_id'];
		$this->add_room_id = $pages[$this->page_id]['room_id'];
		$this->add_module_id = $block_obj['module_id'];
		if($block_add_action != "") {
			$this->edit_action_name = $block_add_action;
		} else if($module_obj['edit_action_name'] != "" && $module_obj['edit_action_name'] != null) {
			$this->edit_action_name = $module_obj['edit_action_name'];
		} else {
			$this->edit_action_name = $module_obj['action_name'];
		}
		
		return 'success';
	}

}
?>
