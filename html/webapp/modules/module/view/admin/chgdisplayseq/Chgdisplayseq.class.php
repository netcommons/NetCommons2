<?php
/**
 * 表示順変更
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Module_View_Admin_Chgdisplayseq extends Action
{
	//使用コンポーネント
	var $modules = null;
	
	var $sysmodules_obj = null;
	var $modules_obj = null;
	
	function execute()
	{
		//システムモジュール
		$this->sysmodules_obj = $this->modules->getModulesBySystemflag(1);
		//一般モジュール
		$this->modules_obj = $this->modules->getModulesBySystemflag(0);		
		return 'success';
	}
}
?>
