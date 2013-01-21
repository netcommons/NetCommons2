<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* ファイルクリーンアップ表示画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cleanup_View_Main_Init extends Action
{
	// コンポーネントを使用するため
	var $modulesView = null;
	
	// 値をセットするため
	var $modules_list = null;
	
    /**
     * ファイルクリーンアップ表示画面
     *
     * @access  public
     */
    function execute()
    {
    	// ----------------------------------------------------------------------
		// --- 対象モジュール一覧取得                                         ---
		// ----------------------------------------------------------------------
		$modules = $this->modulesView->getModules(null, array("{modules}.system_flag"=>"DESC", "{modules}.display_sequence" => "ASC"), null, null, array($this, "_fetchcallbackModules"));
		$count = 0;
		foreach($modules as $module) {
			$dirname = $module['dirname'];
			$file_path = MODULE_DIR."/".$dirname.'/install.ini';
			if (file_exists($file_path)) {
				if(version_compare(phpversion(), "5.0.0", ">=")){
		        	$initializer =& DIContainerInitializerLocal::getInstance();
		        	$install_ini = $initializer->read_ini_file($file_path, true);
		        } else {
		 	        $install_ini = parse_ini_file($file_path, true);
		        }
		        if(isset($install_ini['CleanUp'])) {
		        	// クリーンアップ対象
		        	$modinfo_ini = $this->modulesView->loadModuleInfo($dirname);
		        	if($modinfo_ini && isset($modinfo_ini["module_name"])) {
		        		$this->modules_list[$count]["module_name"] = $modinfo_ini["module_name"];
		        		$this->modules_list[$count]["target"] = $modules[$dirname];
		        		$count++;
		        	}
		        	
		        }
		        
			}
		}
		
		return 'success';
    }
    
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackModules($result, $func_param) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$pathList = explode("_", $row["action_name"]);
			$row['dirname'] = $pathList[0];
			$ret[$pathList[0]] = $row;
		}
		return $ret;
	}
}
?>
