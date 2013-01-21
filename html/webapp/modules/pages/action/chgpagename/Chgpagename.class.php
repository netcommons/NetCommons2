<?php

class Pages_Action_Chgpagename extends Action
{
	var $page_id = null;
	var $block_name = null;
	
	// 使用コンポーネントを受け取るため
	var $pagesAction = null;
	
	function execute()
	{
		$this->pagesAction->updPagename($this->page_id, $this->block_name);
		
		return 'success';
	}
	
}
?>
