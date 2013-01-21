<?php

class Pages_Action_Insertrow extends Action
{
	// 使用コンポーネントを受け取るため
	var $blocksAction = null;
	var $pagesAction = null;
	
	// リクエストパラメータを受け取るため
	var $page_id = null;
	
    /**
     * ページに新規行挿入
     *
     * @access  public
     */
    function execute()
    {
    	//表示カウント＋＋
    	$this->pagesAction->updShowCount($this->page_id);
    		
    	//
    	//INSERT_ROW
    	//
    	$this->blocksAction->InsertRow($this->page_id);
    	return 'success';
    }
}
?>
