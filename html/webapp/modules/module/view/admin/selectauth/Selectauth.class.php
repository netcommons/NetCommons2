<?php
/**
 * モジュール表示クラス
 * 未インストールモジュールを取得するため、全インストールモジュールを取得する必要がある
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Module_View_Admin_Selectauth extends Action
{
	// リクエストパラメータ
	var $act_module_id = null;

	// 使用コンポーネントを受け取るため
	var $modulesView = null;
	var $authoritiesView = null;
	
	// 値をセットするため
	var $authorities = null;
	var $maxNum = 0;
	var $module_name = null;
	var $system_flag = null;
	
	function execute()
	{
		$this->act_module_id = intval($this->act_module_id);
		$modules =& $this->modulesView->getModulesById($this->act_module_id);
		$this->module_name = $modules['module_name'];
		$this->system_flag = $modules['system_flag'];
		if($this->system_flag==1) {
			$where_params = null;		
		} else {
			$where_params = array("myroom_use_flag"=>1);
		}
		$order_params = array("hierarchy"=>"DESC");
		$this->authorities = $this->authoritiesView->getAuthoritiesModulesLinkByModuleId(intval($this->act_module_id), $where_params, $order_params, array($this->authoritiesView,"_getAuthList"));
		$this->maxNum = count($this->authorities);
		return 'success';
	}
}
?>
