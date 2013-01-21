<?php

class Pages_Action_Cancelgrouping extends Action
{
	// 使用コンポーネントを受け取るため
	var $blocksAction = null;
	var $pagesAction = null;
	var $blocksView = null;
	var $getData = null;
	var $session = null;
	
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	
	
    /**
     * グルーピング解除処理
     *
     * @access  public
     */
    function execute()
    {
    	//表示カウント＋＋
    	$this->pagesAction->updShowCount($this->page_id);
    	
    	$time = timezone_date();
    	//$site_id = $this->session->getParameter("_site_id");
        $user_id = $this->session->getParameter("_user_id");
        $user_name = $this->session->getParameter("_handle");
        
    	//
    	//グルーピングブロック取得
    	//
    	$main_block_obj =& $this->getData->getParameter("blocks");
    	
    	//グルーピングブロック削除
    	$this->blocksAction->delBlockById($this->block_id);
    	
    	//親移動
    	$blocks_obj = $this->blocksView->getBlockByParentId($this->block_id);
    	$row_count = -1;
    	$col_count = 0;
    	foreach($blocks_obj as $block_obj) {
    		if($block_obj['col_num'] == 1) {
    			$row_count++;
    		} else {
    			if($col_count < $block_obj['col_num'] - 1)
    				$col_count = $block_obj['col_num'] - 1;
    		}
    	}
    	
    	$params = array(
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"page_id" => $main_block_obj[$this->block_id]['page_id'],
			"block_id" => $this->block_id,
			"parent_id" => $main_block_obj[$this->block_id]['parent_id'],
			"col_num" => $main_block_obj[$this->block_id]['col_num'],
			"row_num" => $main_block_obj[$this->block_id]['row_num']
		);
    	$result = $this->blocksAction->incrementRowNum($params,$row_count);
		if(!$result) {
			return 'error';
		}
		
		$params = array(
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"page_id" =>$main_block_obj[$this->block_id]['page_id'],
			"block_id" => $this->block_id,
			"parent_id" => $main_block_obj[$this->block_id]['parent_id'],
			"col_num" => $main_block_obj[$this->block_id]['col_num'] + 1
		);
		$result = $this->blocksAction->incrementColNum($params,$col_count);
		if(!$result) {
			return 'error';
		}
    	
    	//
    	// グルーピング解除処理
    	//
    	foreach($blocks_obj as $block_obj) {
    		if($block_obj['root_id'] != 0)
    			$root_id = $block_obj['root_id'];
    		else
    			$root_id = $block_obj['block_id'];
    		$block_obj['update_time'] = $time;
		    $block_obj['update_user_id'] = $user_id;
		    $block_obj['update_user_name'] = $user_name;
		    $block_obj['parent_id'] = $main_block_obj[$this->block_id]['parent_id'];
		    $block_obj['root_id'] = $main_block_obj[$this->block_id]['root_id'];
		    $block_obj['thread_num'] = intval($block_obj['thread_num']) - 1;
		    	
    		if($block_obj['col_num'] == 1) {
    			$block_obj['col_num'] = intval($main_block_obj[$this->block_id]['col_num']);
		    	$block_obj['row_num'] = intval($block_obj['row_num']) + intval($main_block_obj[$this->block_id]['row_num']) - 1;	
    		} else {
    			$block_obj['col_num'] = intval($block_obj['col_num']) + intval($main_block_obj[$this->block_id]['col_num']) - 1;
    		}
    		$result = $this->blocksAction->updBlock($block_obj, array("block_id"=>$block_obj['block_id']), false);
			if(!$result) {
				return 'error';
			}
			//グループ化しているブロックならば,そのグループの子供を求める
	    	if($block_obj['action_name']=="pages_view_grouping") {
	    		$block_obj_children =& $this->blocksView->getBlockByRootId($root_id);
	    		$parent_id_arr = array($block_obj['block_id']);
	    		foreach ($block_obj_children as $block_obj_child) {
	    			if(in_array($block_obj_child['parent_id'], $parent_id_arr)) {
	    				$parent_id_arr[] = $block_obj_child['block_id'];
	    			} else {
	    				continue;
	    			}
	    			if($block_obj['root_id'] != 0)
		    			$root_child_id = $block_obj['root_id'];
		    		else
		    			$root_child_id = $block_obj['block_id'];
		    		
		    		$block_obj_child['root_id'] = $root_child_id;
		    		$block_obj_child['thread_num'] = intval($block_obj_child['thread_num']) - 1;
		    		$result = $this->blocksAction->updBlock($block_obj_child, array("block_id"=>$block_obj_child['block_id']), false);
		    		if(!$result)
		    			return 'error';
	    		}
	    	}
    	}
    	
    	return 'success';
    }
}
?>
