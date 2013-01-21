<?php

class Pages_Action_Grouping extends Action
{
	// 使用コンポーネントを受け取るため
	var $blocksView = null;
	var $blocksAction = null;
	var $pagesAction = null;
	var $getData = null;
	var $session = null;
	var $actionChain = null;
	var $request = null;
	var $commonMain = null;
	
	// リクエストパラメータを受け取るため
	var $block_id = null;
	
	//Validataでセット
	var $grouping_list = null;
	
	var $page_id = null;
	
	
    /**
     * グルーピング処理
     *
     * @access  public
     */
    function execute()
    {
    	//表示カウント＋＋
    	$this->pagesAction->updShowCount($this->page_id);
    	
    	//
    	//グルーピングブロック新規登録
    	//
    	$blocks_obj =& $this->getData->getParameter("blocks");
    	$insert_block_obj = $blocks_obj[$this->block_id];

    	$time = timezone_date();
    	$site_id = $this->session->getParameter("_site_id");
        $user_id = $this->session->getParameter("_user_id");
        $user_name = $this->session->getParameter("_handle");
        
        $insert_block_obj['site_id'] = $site_id;
        
    	$insert_block_obj['insert_time'] = $time;
    	$insert_block_obj['insert_user_id'] = $user_id;
    	$insert_block_obj['insert_user_name'] = $user_name;
    	$insert_block_obj['update_time'] = $time;
    	$insert_block_obj['update_user_id'] = $user_id;
    	$insert_block_obj['update_user_name'] = $user_name;
    	array_shift( $insert_block_obj );
   	
    	$insert_block_obj['block_name'] = PAGES_GROUPING_DEFAULT_BLOCK_NAME;
    	$insert_block_obj['action_name'] = "pages_view_grouping";
    	$insert_block_obj['module_id'] = 0;
    	$insert_block_obj['theme_name'] = "noneframe";		//noneframe固定
    	$insert_block_obj['temp_name'] = "";				//なし固定
    	$insert_block_obj['min_width_size'] = 0;
    	$insert_block_obj['topmargin'] = 0;
    	$insert_block_obj['rightmargin'] = 0;
    	$insert_block_obj['leftmargin'] = 0;
    	$insert_block_obj['bottommargin'] = 0;
   	
    	$ins_block_id = $this->blocksAction->insBlock($insert_block_obj, false);
    	if(!$ins_block_id) {
	    	return 'error';
    	}
    	$cellLists = explode(":", $this->grouping_list);
    	$col_num = 1;
    	$row_num = 1;
    	//$dec_col_num = 1;
    	//$dec_row_num = 1;
    	$block_children_arr = array();
    	foreach ($cellLists as $cellList) {
    		$rowLists = explode(",", $cellList);
    		foreach ($rowLists as $rowList) {
    			$block_id = $rowList;
    			$block_obj =& $this->blocksView->getBlockById($block_id);
    			////$block_obj =& $blocks_obj[$block_id];
    			
    			//$block_obj =& $this->blocksView->getBlockById($block_id);
		    	//ブロックが存在しないならばエラー
		    	if(!$block_obj && !is_array($block_obj)) {
		    		$errorList =& $this->actionChain->getCurErrorList();
					$errorList->add("block_id". $block_id, PAGES_NOEXISTS_BLOCK);
					return 'error';
		    	}
		    	//グルーピング先ならば、前詰めする必要がないため処理しない
    			if($this->block_id != $block_obj['block_id']) {
    				//前詰め処理(移動元)
				    $params = array(
						"update_time" =>$time,
						"update_user_id" => $user_id,
						"update_user_name" => $user_name,
						"page_id" => $block_obj['page_id'],
						"block_id" => $block_obj['block_id'],
						"parent_id" => $block_obj['parent_id'],
						"col_num" => $block_obj['col_num'], //$dec_col_num,
						"row_num" => $block_obj['row_num']  //$dec_row_num
					);
					
					$result = $this->blocksAction->decrementRowNum($params);
					if(!$result) {
						return 'error';
					}
					//$dec_row_num--;
					$params_row_count = array( 
						"page_id" => $block_obj['page_id'],
						"parent_id" => $block_obj['parent_id'],
						"col_num" => $block_obj['col_num']
					);
					$count_row_num = $this->blocksView->getCountRownumByColnum($params_row_count);			
					if($count_row_num == 1) {
						//移動前の列が１つしかなかったので
						//列--
						$params = array(
							"update_time" =>$time,
							"update_user_id" => $user_id,
							"update_user_name" => $user_name,
							"page_id" => $block_obj['page_id'],
							"block_id" => $block_obj['block_id'],
							"parent_id" => $block_obj['parent_id'],
							"col_num" => $block_obj['col_num'] //$dec_col_num
						);
						$result = $this->blocksAction->decrementColNum($params);
						if(!$result) {
							return 'error';
						}
						//$dec_col_num--;
					}
    			}
		    	$block_obj['col_num'] = $col_num;
		    	$block_obj['row_num'] = $row_num;
		    	$block_obj['thread_num'] = intval($block_obj['thread_num'])+1;
		    	$block_obj['parent_id'] = $ins_block_id;
		    	$root_id = $block_obj['root_id'];
	    		if($insert_block_obj['parent_id'] == 0) {
	    			$block_obj['root_id'] = $ins_block_id;
	    		}
		    	$result = $this->blocksAction->updBlock($block_obj,array("block_id"=>$block_obj['block_id']), false);
    			if(!$result) {
    				return 'error';
    			}
    			//グループ化しているブロックならば,そのグループの子供を求める
		    	if($block_obj['action_name']=="pages_view_grouping") {
		    		if($root_id == 0)
		    			$root_id = $block_obj['block_id'];
		    		
		    		$block_obj_children = $this->blocksView->getBlockByRootId($root_id);
		    		$parent_id_arr = array($block_obj['block_id']);
		    		$buf_block_children_arr = array();
		    		foreach($block_obj_children as $block_obj_child) {
		    			if(in_array($block_obj_child['parent_id'], $parent_id_arr)) {
		    				$parent_id_arr[] = $block_obj_child['block_id'];
		    				$buf_block_children_arr[] = $block_obj_child;
		    			}
		    		}
		    		$block_children_arr[$block_obj['block_id']] = $buf_block_children_arr;
		    	}
		    	$row_num++;
    			//$dec_row_num++;
    		}
    		$col_num++;
    		//$dec_col_num++;
    		$row_num = 1;
    		//$dec_row_num = 1;
    	}
    	
    	//子ブロックがあればthread_num,root_id更新
    	//但し、root_idは、トップエレメントが登録された場合にのみ更新
    	if(isset($block_children_arr)) {
    		foreach ($block_children_arr as $key_block_id => $block_obj_children) {
		    	foreach ($block_obj_children as $block_obj_child) {
		    		if($key_block_id == $block_obj_child['block_id'] || $ins_block_id == $block_obj_child['block_id']) {
		    			continue;
		    		}
		    		$block_obj_child['thread_num'] = intval($block_obj_child['thread_num']) + 1;
		    		if($insert_block_obj['parent_id'] == 0) {
		    			$block_obj_child['root_id'] = $ins_block_id;
		    		}
		    		//もし、親IDが追加したブロックの親IDと等しいならば、
		    		//親IDを追加ブロックIDに変更
		    		if($block_obj_child['parent_id'] == $insert_block_obj['parent_id'])
		    			$block_obj_child['parent_id'] = $ins_block_id;		    		
		    		$result = $this->blocksAction->updBlock($block_obj_child,array("block_id"=>$block_obj_child['block_id']));
		    		if(!$result) {
		    			return 'error';
		    		}
		    	}
    		}
    	}
    	
    	//空TD-column挿入
    	$renderer =& SmartyTemplate::getInstance();
    	$empty_columns[0] = "";
    	$renderer->assign('columns',$empty_columns);
    	$renderer->assign('main_action_name',$this->session->getParameter('_main_action_name'));
    	//$renderer->assign('main_block_id',null);
    	$this->request->setParameter("block_id",$ins_block_id);
    	$this->request->setParameter("page_id",$insert_block_obj['page_id']);
    	$this->request->setParameter("action","pages_view_grouping");
    	
    	$this->commonMain->getTopId($ins_block_id);
    	
    	return 'success';
    }
}
?>
