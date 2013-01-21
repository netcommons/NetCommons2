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
class Module_View_Admin_Init extends Action
{
	//使用コンポーネントを受け取るため
	var $modules = null;
	var $configView = null;
	
	// 値をセットするため
	var $sysmodules_obj = null;
	var $modules_obj = null;
	var $installs_obj = null;
		
	var $maxNum = 0;
	var $sysMaxNum = 0;
	var $insMaxNum = 0;
	
	var $version = "";
	
	function execute()
	{
		//
		// 現在のバージョン
		//
		$config_version = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "version");
		if(isset($config_version) && isset($config_version['conf_value'])) {
			$this->version = $config_version['conf_value'];
		} else {
			$this->version = _NC_VERSION;
		}
		
		$installed = array();
		//
		//一般モジュール
		//
		$this->maxNum = $this->modules->getCountBySystemflag(0);
		if($this->maxNum > 0) {
			$this->modules_obj = $this->modules->getModulesBySystemflag(0);
			$count = 0;
			foreach ($this->modules_obj as $module_obj) {
				//インストール済モジュールセット
				$pathList = explode("_", $module_obj['action_name']);
				$this->modules_obj[$count]['module_dir'] = $pathList[0];
				$installed[] = $pathList[0];
				if(@file_exists(MODULE_DIR. "/" . $pathList[0]."/install.ini")) {
					$install_ini = $this->modules->loadInfo($pathList[0]);
					if (!$install_ini) {
						$this->modules_obj[$count]["current_version"] = MODULE_DEFAULT_VARSION;
					} else {
						if($install_ini['version'])
							$this->modules_obj[$count]["current_version"] = $install_ini['version'];
						else
							$this->modules_obj[$count]["current_version"] = MODULE_DEFAULT_VARSION;
					}
				}
				$count++;
			}
		}
		//
		//システムモジュール
		//
		$this->sysMaxNum = $this->modules->getCountBySystemflag(1);
		if($this->sysMaxNum > 0) {
			$this->sysmodules_obj = $this->modules->getModulesBySystemflag(1);
			$count = 0;
			foreach ($this->sysmodules_obj as $sysmodule_obj) {
				//インストール済モジュールセット
				$pathList = explode("_", $sysmodule_obj['action_name']);
				$installed[] = $pathList[0];
				if(@file_exists(MODULE_DIR. "/" . $pathList[0]."/install.ini")) {
					$install_ini = $this->modules->loadInfo($pathList[0]);
					if (!$install_ini) {
						$this->sysmodules_obj[$count]["current_version"] = MODULE_DEFAULT_VARSION;
					} else {
						if($install_ini['version'])
							$this->sysmodules_obj[$count]["current_version"] = $install_ini['version'];
						else
							$this->sysmodules_obj[$count]["current_version"] = MODULE_DEFAULT_VARSION;
					}
				}
				$count++;
			}
		}
		
		//
		//未インストールモジュール
		//
		$path = MODULE_DIR."/";
		$fileArray=glob( $path."*" );
		if(is_array($fileArray)) {
			$count = 0;
			foreach( $fileArray as $full_path){
				if(@file_exists($full_path."/install.ini")) {
					//install.iniが存在する
					preg_match("/^(.*[\/])(.*)/",$full_path, $matches);
					$dir_name = $matches[2];
					if (in_array($dir_name, $installed)) {
						continue;
					}
					$this->installs_obj[$count]["module_name"] = $this->modules->loadModuleName($dir_name);
					$install_ini = $this->modules->loadInfo($dir_name);
					if (!$install_ini) {
						$this->installs_obj[$count]["version"] = MODULE_DEFAULT_VARSION;
					} else {
						if($install_ini['version'])
							$this->installs_obj[$count]["version"] = $install_ini['version'];
						else
							$this->installs_obj[$count]["version"] = MODULE_DEFAULT_VARSION;
					}
					//Dir Name セット
					$this->installs_obj[$count]["dir_name"] = $dir_name;
					
					$count++;
				}
			}
		}
		$this->insMaxNum = $count;
		
		return 'success';
	}
}
?>
