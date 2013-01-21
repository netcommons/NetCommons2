<?php

class Pages_Action_Chgdisplaycolumn extends Action
{
	// 使用コンポーネントを受け取るため
	var $pagesAction = null;
	var $getData = null;
	
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $leftcolumn_flag = null;
	var $rightcolumn_flag = null;
	var $header_flag = null;
	var $footer_flag = null;
	
    /**
     * ページに新規列挿入
     *
     * @access  public
     */
    function execute()
    {
    	//表示カウント＋＋
    	//$this->pagesAction->updShowCount($this->page_id);
    	
    	$pages = $this->getData->getParameter("pages");
    	$params = array(
			"page_id" =>$this->page_id,
			"header_flag" => ($this->header_flag == null) ? $pages[$this->page_id]['header_flag'] : $this->header_flag,
			"footer_flag" => ($this->footer_flag == null) ? $pages[$this->page_id]['footer_flag'] : $this->footer_flag,
			"leftcolumn_flag" => ($this->leftcolumn_flag == null) ? $pages[$this->page_id]['leftcolumn_flag'] : $this->leftcolumn_flag,
			"rightcolumn_flag" => ($this->rightcolumn_flag == null) ? $pages[$this->page_id]['rightcolumn_flag'] : $this->rightcolumn_flag
		);
    	
    	//
    	//UPDATE Column_flag
    	//
    	$this->pagesAction->updColumnFlag($params);
    	return 'success';
    }
}
?>
