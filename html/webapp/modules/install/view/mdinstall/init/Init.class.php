<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install モジュールインストール
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Mdinstall_Init extends Action
{
    // リクエストパラメータを受け取るため
    
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    var $token = null;
    var $modulesView = null;
    var $request = null;
    
    // 値をセットするため
    var $token_value = null;
    var $modules = array();
    var $sys_modules = array();
    
    var $install_system_module_cnt = 0;
    var $install_general_module_cnt = 0;
    
    /**
     * Install モジュールインストール
     *
     * @access  public
     */
    function execute()
    {
    	$this->request->setParameter("_header", _OFF);
    	$this->request->setParameter("_noscript", _ON);
    	
    	$this->installCompmain->setTitle();
		$this->token_value = $this->token->getValue();
		
		// インストール対象取得
		list($this->sys_modules, $this->modules) = $this->_getInstallModule();
		$this->install_system_module_cnt = count($this->sys_modules);
		$this->install_general_module_cnt = count($this->modules);
		
    	return 'success';
    }
    
    
    /**
     * インストール対象取得
     * モジュールディレクトリ直下のinstall.iniがあるものすべて
     * インストール対象として列挙
     * @return array array($sys_modules,$modules)
     * @access  private
     */
    function _getInstallModule() {
    	$modules = array();
    	$sys_modules = array();
    	
    	// 既にインストールされているモジュールを取得
    	$installed = $this->modulesView->getModules();
    	if($installed === false) {
    		return array($sys_modules, $modules);
    	}
    	
    	$path = MODULE_DIR."/";
		$fileArray=glob( $path."*" );
		$count = 0;
		$sys_count = 0;
		if(is_array($fileArray)) {
			foreach( $fileArray as $full_path){
				if(@file_exists($full_path."/install.ini")) {
					//install.iniが存在する
					preg_match("/^(.*[\/])(.*)/",$full_path, $matches);
					$dir_name = $matches[2];
					if (in_array($dir_name, $installed)) {
						// 既にインストール済
						continue;
					}
					$install_ini = $this->modulesView->loadInfo($dir_name);
					if($install_ini === false) {
						// install.iniが不正の場合、スルー
						continue;	
					}
					if(isset($install_ini['system_flag']) && $install_ini['system_flag'] == _ON) {
						// 管理系
						$sys_modules[$sys_count]["module_name"] = $this->modulesView->loadModuleName($dir_name);
						$sys_modules[$sys_count]["dir_name"] = $dir_name;
						$sys_count++;
					} else {
						$modules[$count]["module_name"] = $this->modulesView->loadModuleName($dir_name);
						$modules[$count]["dir_name"] = $dir_name;
						$count++;
					}
				}
			}
		}
		
    	return array($sys_modules, $modules);
    }
}
?>
