<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 配置モジュール修正画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_Selmodules extends Action
{
	// リクエストパラメータを受け取るため
	var $edit_current_page_id = null;
	var $parent_page_id = null;
		
	// バリデートによりセット
	var $parent_page = null;
	var $page = null;
	
	// コンポーネントを使用するため
	var $pagesView = null;
	var $modulesView = null;
	var $session = null;
	
	// 値をセットするため
	var $entry_modules = null;
	var $target_modules = null;
	
    /**
     * 使用可能モジュール選択画面表示
     *
     * @access  public
     */
    function execute()
    {
    	//$this->edit_current_page_id = ($this->edit_current_page_id == null) ? 0 : intval($this->edit_current_page_id);
    	$this->parent_page_id = ($this->parent_page_id == null) ? 0 : intval($this->parent_page_id);
    	
    	$modules =& $this->modulesView->getModules(array("disposition_flag"=>_ON, "system_flag"=>_OFF), array("default_enable_flag" => "DESC", "display_sequence" => ""));
		$this->entry_modules = array();
		$this->target_modules = array();
		if($modules === false) {
			return 'error';
		}
    			
    	//
		//配置可能モジュール修正
		//
		$where_params = array(
			"room_id" => intval($this->edit_current_page_id)
		);
		$page_modules =& $this->pagesView->getPageModulesLink($where_params, null, array($this, "_fetchcallbackModulesLink"));
		$count = 0;
		foreach($modules as $module) {
			//default_enable_flag　ONならば使用可能モジュールへ
    		if(isset($page_modules[$module['module_id']])) {
    			$this->entry_modules[] = $module;
    			unset($modules[$count]);
    		}
    		$count++;
    	}
		$this->target_modules =& $modules;
    	return 'success';
    }
    
    
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchcallbackModulesLink($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['module_id']] = $row;
		}
		return $ret;
	}
}
?>
