<?php

class Module_Action_Admin_Chgdisplayseq extends Action
{
	//使用コンポーネント
	var $modules = null;
	
	var $module_array = null;
	
	function execute()
	{
		foreach($this->module_array as $module_id=>$display_sequence) {
			$this->modules->updModuleDisplayseq($module_id,$display_sequence);
		}
		return 'success';
	}
}
?>
