<?php

class Pages_Actionblock_Chgblockname extends Action
{
	var $block_id = null;
	var $block_name = null;
	
	// 使用コンポーネントを受け取るため
	var $blocksAction = null;
	
	function execute()
	{
		$this->blocksAction->updBlockname($this->block_id, $this->block_name);
		
		return 'success';
	}
	
}
?>
