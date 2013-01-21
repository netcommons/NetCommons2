<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

 /**
 * モジュールアップデート
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Module_Action_Admin_Update extends Action
{
	
	// リクエストパラメータを受け取るため
	var $upd_module_id = null;
	var $op = null;	//一括アップデート用
	//使用コンポーネント
	var $modulesView = null;
	var $modulesAction = null;
	var $moduleCompmain = null;
	var $preexecute = null;
	var $authoritiesView = null;
	var $authoritiesAction = null;
	var $configView = null;
	var $configAction = null;
	//var $authCheck = null;
	var $blocksAction = null;
	var $session = null;
	var $mobileAction = null;
	var $mobileView = null;
	var $pagesAction = null;

	//結果文字列
	var $result_str = "";
	var $module_name = null;
    /**
     * アップデート処理
     *
     * @access  public
     */
    function execute()
    {
    	if($this->upd_module_id != 0) {
    		$ret = $this->_updateModule($this->upd_module_id);
    	} else {
    		$ret = "";
    	}
    	if($this->op == "all") {
    		//システムモジュール
			$modules_obj = $this->modulesView->getModulesBySystemflag(1);
			//一般モジュール
			$modules_obj = array_merge($modules_obj,$this->modulesView->getModulesBySystemflag(0));
			$count = 0;
			$this->upd_module_id = array();
			foreach($modules_obj as $module_obj) {
				$this->upd_module_id[$count] = $module_obj['module_id'];
				$count++;
			}
    		return "all_success";
    	}else if($this->op == "detail") {
    		return "detail_success";
    	}else {
    		return $ret;
    	}
    }
    
    function _updateModule($module_id) {
    	$container =& DIContainerFactory::getContainer();
    	$filterChain =& $container->getComponent("FilterChain");
    	
    	$module_obj = $this->modulesView->getModulesById($module_id);
    	$this->module_name = $module_obj["module_name"];
    	//処理開始
    	$this->_setMes(MODULE_MES_RESULT_START);
    	if(!$module_obj) {
    		$this->_setMes(MODULE_MES_GET_ER,1);
    		//処理エラー終了
    		$this->_setMes(MODULE_MES_RESULT_ERROR);
    		return 'error';
    	} else {
    		//action_nameからinstall.iniを取得
    		$pathList = explode("_", $module_obj["action_name"]);
    		$dirname = $pathList[0];
    		$install_ini = $this->modulesView->loadInfo($dirname);
    		if (!$install_ini) {
    			//バージョンファイルが不正
    			$this->_setMes(sprintf(MODULE_MES_VERSION_ER,$dirname),1);
	       		//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';	
	        }
	        
	        // ----------------------------------------------------------------------
			// --- アクション名称チェック                                         ---
			// ----------------------------------------------------------------------
			$this->_setMes(MODULE_MES_RESULT_ACTION_NAME_CHECK_ST,1);
	        if(!isset($install_ini["action_name"]) || $install_ini["action_name"] == "") {
	        	$install_ini["action_name"] == $this->dir_name . MODULE_DEFAULT_DEF_ACTION_NAME;
	        }
	        if(!$this->moduleCompmain->ActionNameCheck($install_ini["action_name"])) {
	        	//アクション名称が不正
				$this->_setMes(sprintf(MODULE_MES_ACTION_NAME_CHECK_ER,$install_ini["action_name"]),1);
	       		//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';
	        }
	        
	        if($install_ini["edit_action_name"] != "" && !$this->moduleCompmain->ActionNameCheck($install_ini["edit_action_name"])) {
	        	//アクション名称が不正
				$this->_setMes(sprintf(MODULE_MES_ACTION_NAME_CHECK_ER,$install_ini["edit_action_name"]),1);
	       		//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';	
	        }
	        
	        if($install_ini["edit_style_action_name"] != "" && !$this->moduleCompmain->ActionNameCheck($install_ini["edit_style_action_name"])) {
	        	//アクション名称が不正
				$this->_setMes(sprintf(MODULE_MES_ACTION_NAME_CHECK_ER,$install_ini["edit_style_action_name"]),1);
	       		//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';	
	        }
	        
	        $this->_setMes(MODULE_MES_RESULT_ACTION_NAME_CHECK_EN,1);
	        
	        $install_ini["module_id"] = $this->upd_module_id;	//module_idセット
	        $this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST,MODULE_MODULE_TABLE_NAME),1);
	        // 変更があるcolumnを検索し、処理内容も追加する
			if(!$this->modulesView->setDefaultInfoFile($install_ini,$module_obj,$this->result_str)) {
				$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_MODULE_TABLE_NAME),1);
	        	//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';
			}
			// ----------------------------------------------
			// --- モジュールテーブル更新				  ---
			// ----------------------------------------------
	        if(!$this->modulesAction->updModule($install_ini)) {
	        	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_MODULE_TABLE_NAME),1);
	        	//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';
	        }
	        $this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN,MODULE_MODULE_TABLE_NAME),1);
	        // ----------------------------------------------
			// --- ブロックテーブル更新　				  ---
			// ----------------------------------------------
			if($install_ini["action_name"] != $module_obj["action_name"]) {
				if(!$this->blocksAction->updNewActionname($module_obj["action_name"], $install_ini["action_name"])) {
					$this->_setMes(sprintf(MODULE_MES_UPDATE_ER,MODULE_BLOCKS_TABLE_NAME),1);
					//処理エラー終了
	       			$this->_setMes(MODULE_MES_RESULT_ERROR);
	       			return 'error';
				}
			}
			
			// ----------------------------------------------------------------------
			// --- 権限モジュールリンクデータ削除(authorities_modules_link)       ---
			//      配置不可能モジュールの場合　　　　　　　　　　　　　　　　　　---
			// ----------------------------------------------------------------------
			if(!$install_ini['disposition_flag']) {
				$del_where_params = array("module_id" => $module_id);
				$this->authoritiesAction->delAuthorityModuleLink($del_where_params);
			}
		    // ----------------------------------------------------------------------
			// --- ページモジュールリンクデータ削除                               ---
			//     配置不可能モジュールの場合　　　　　　　　　　                 ---
			// ----------------------------------------------------------------------
			if(!$install_ini['disposition_flag']) {
				$del_where_params = array("module_id" => $module_id);
				$this->pagesAction->delPagesModulesLink($del_where_params);
			}
			
	       	// --------------------------------------------------------
			// --- アップデートアクション実行(module_update_action) ---
			// --------------------------------------------------------
	        //install.iniにupdate指定があればupdate
	        if(isset($install_ini["module_update_action"])) {
	        	$container =& DIContainerFactory::getContainer();
	        	$actionChain =& $container->getComponent("ActionChain");
		    	list ($classname, $filename) = $actionChain->makeNames($install_ini["module_update_action"]);
		    	if (!$filename || !@file_exists($filename)) {
		    		$this->_setMes(sprintf(MODULE_MES_NOEXISTS_UPDATEFILE,$filename),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'error';
		    	}
		    	include_once $filename;
		    	if(!class_exists($classname)) {
		    		$this->_setMes(sprintf(MODULE_MES_NOEXISTS_CLASSNAME,$classname),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'error';
				}
				
				$params = array("action" =>"module_action_admin_update", "module_id" =>$this->upd_module_id);
				$result = $this->preexecute->preExecute($install_ini['module_update_action'], $params);
				
				//再度、sessionにauth_id等をセット
				//$this->authCheck->AuthCheck($actionChain->getCurActionName(),0, 0);
        	
				if(!$result) {
					$this->_setMes(sprintf(MODULE_MES_UPDATEFILE_ER,$classname),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'error';	
				}
		    	$this->_setMes(sprintf(MODULE_MES_UPDATEFILE_EN,$classname),1);
	        }
	        // ----------------------------------------------
			// --- CSS,JSのhtdocsへのコピー				  ---
			// ----------------------------------------------
			set_time_limit(MODULE_UPDATE_TIME_LIMIT);
			if(!$this->moduleCompmain->delHtdocsFilesByDirname($dirname,$this)) {
				return 'error';
			}
			if(!$this->moduleCompmain->copyHtdocsFilesByDirname($dirname,$this)) {
				return 'error';
			}
			$this->_setMes(MODULE_MES_RESULT_DELETEJS_ST,1);
			if(!$this->moduleCompmain->deleteJsFiles($dirname)) {
				$this->_setMes(MODULE_MES_RESULT_DELETEJS_ER,1);
			} else {
				$this->_setMes(MODULE_MES_RESULT_DELETEJS_EN,1);
			}
			$this->_setMes(MODULE_MES_RESULT_REGISTJS_ST,1);
			
			if(!$this->moduleCompmain->registFile(MODULE_DIR."/". $dirname."/files/js/"."*", $dirname) ||
				!$this->moduleCompmain->registFile(MODULE_DIR."/". $dirname."/files/css/"."*", $dirname) ) {
				$this->_setMes(MODULE_MES_RESULT_REGISTJS_ER,1);
			} else {
				$this->_setMes(MODULE_MES_RESULT_REGISTJS_EN,1);
			}
			
			// --------------------------------------------------
			// --- Config登録処理実行(Config) ---
			// --------------------------------------------------
			if(isset($install_ini['Config'])) {
				$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST,MODULE_CONFIG_TABLE_NAME),1);
				$result = true;
				$config_obj = null;
				$config_obj =& $this->configView->getConfig($this->upd_module_id, false);
				foreach ($install_ini['Config'] as $key=>$item) {
					$regs = array();
					if (preg_match("/^([^\[\]>]+)(\[([0-9]*)\])?$/", $key, $regs)) {
						$conf_name = $regs[1];
					}
					$conf_catid = isset($regs[3]) ? intval($regs[3]) : 0;
					if (!isset($config_obj[$conf_name])) {
						// 新規登録
						$result = $this->configAction->insConfigValue($this->upd_module_id, $key, $item);
						if (!$result) break;
					} elseif (isset($config_obj[$conf_name]) && $config_obj[$conf_name]["insert_time"] == $config_obj[$conf_name]["update_time"]) {
					//} else {
						//
						// カテゴリ更新
						// モジュール管理から更新する場合は、insert_timeとupdate_timeをそれぞれ更新する
						//
						$time = timezone_date();
						$site_id = $this->session->getParameter("_site_id");
				        $user_id = $this->session->getParameter("_user_id");
				        $user_name = $this->session->getParameter("_handle");
				        if($user_name === null) $user_name = "";
				        $upd_params = array(
				        	"conf_catid" => $conf_catid,
							"conf_value" => $item,
							"insert_time" =>$time,
							"insert_site_id" => $site_id,
							"insert_user_id" => $user_id,
							"insert_user_name" => $user_name,
							"update_time" =>$time,
							"update_site_id" => $site_id,
							"update_user_id" => $user_id,
							"update_user_name" => $user_name
						);
						$upd_where_params = array(
							"conf_modid" => $this->upd_module_id, 
							"conf_name" => $conf_name
						);
						
						$result = $this->configAction->updConfig($upd_params, $upd_where_params, false);
						//$result = $this->configAction->updConfigValue($this->upd_module_id, $conf_name, $item, $conf_catid);
						if (!$result) break;
					}
					if (isset($config_obj[$conf_name])) {
						unset($config_obj[$conf_name]);
					}
				}
				// 使用していないConfig削除
				if ($result && count($config_obj)>0) {
					foreach ($config_obj as $key=>$item) {
						$result = $this->configAction->delConfigValue($this->upd_module_id, $key);
					}
				}
				if($result) {
			    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN,MODULE_CONFIG_TABLE_NAME),1);
				} else {
			    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_CONFIG_TABLE_NAME),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'error';
				}
			} else {
				// config削除
				$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST,MODULE_CONFIG_TABLE_NAME),1);
				$result = $this->configAction->delConfigValueByModid($this->upd_module_id);
				if($result) {
			    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN,MODULE_CONFIG_TABLE_NAME),1);
				} else {
			    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_CONFIG_TABLE_NAME),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'error';
				}
			}

			// --------------------------------------------------
			// --- Mobile登録処理実行(Mobile) ---
			// --------------------------------------------------
			if (isset($install_ini['Mobile'])) {
				$module_mobile = $this->modulesView->getModuleByDirname("mobile");
				if (!empty($module_mobile)) {
					$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST, MODULE_MOBILE_TABLE_NAME), 1);
					$mobile_params = array();
					foreach ($install_ini['Mobile'] as $key=>$item) {
						if ($key == "header" && isset($install_ini['Mobile']["header"]) && $install_ini['Mobile']["header"] == _ON) {
							$mobile_params["display_position"] = _DISPLAY_POSITION_HEADER;
							continue;
						}
						$mobile_params[$key] = $item;
					}
			    	$count = $this->mobileView->getCount($this->upd_module_id);
			    	if ($count === false) {
			    		return 'error';
			    	}
					if ($count > 0) {
						$result = $this->mobileAction->updateMobile($this->upd_module_id, $mobile_params);
					} else {
						$result = $this->mobileAction->insertMobile($this->upd_module_id, $mobile_params);
					}
					if ($result) {
				    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN, MODULE_MOBILE_TABLE_NAME), 1);
					} else {
				    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER, MODULE_MOBILE_TABLE_NAME), 1);
			    		//処理エラー終了
			   			$this->_setMes(MODULE_MES_RESULT_ERROR);
			   			return 'error';
					}
				}
			}

			// ----------------------------------------------
			// --- キャッシュクリア		 ---
			// ----------------------------------------------
			if($this->moduleCompmain->clearCacheByDirname($dirname)) {
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELCACHE_EN,"[".$dirname."]"),1);
			}
			
	        //処理正常終了
	        $this->_setMes(MODULE_MES_RESULT_END);
	       	return 'success';
    	}
    }
    
    function _setMes($mes,$tabLine=0) {
    	$this->result_str .= $this->modulesView->getShowMes($mes,$tabLine);
    }
}
?>
