<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *  モジュールインストール
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Module_Action_Admin_Install extends Action
{
	
	// リクエストパラメータを受け取るため
	var $dir_name = null;
	var $install_flag = null;
	
	//使用コンポーネント
	var $modulesView = null;
	var $modulesAction = null;
	var $common = null;
	var $configView = null;
	var $configAction = null;
	var $databaseSqlutility = null;
	var $moduleCompmain = null;
	var $authoritiesView = null;
	var $authoritiesAction = null;
	var $pagesView = null;
	var $pagesAction = null;
	var $preexecute = null;
	//var $authCheck = null;
	var $session = null;
	var $db = null;
	var $mobileAction = null;
	
	//実行SQL_TABLE
	var $created_tables = array();
	var $module_id = null;
	
	//結果文字列
	var $result_str = "";
	var $module_name = null;
	var $selectauth_flag = false;
    /**
     * インストール処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->module_id = null;
    	$container =& DIContainerFactory::getContainer();
    	$actionChain =& $container->getComponent("ActionChain");
    	$return_prefix = "";
    	if($this->install_flag == _ON) {
    		// インストーラから呼ばれた場合
    		$return_prefix = "install_";
    	}
    	
    	//モジュール名称取得
    	$this->module_name = $this->modulesView->loadModuleName($this->dir_name);
    	//処理開始
    	$this->_setMes(MODULE_MES_RESULT_START);
    	
    	// ------------------------------------------------------------------------------------
		// --- システムで既に使用されているディレクトリであった場合、インストール不可　　　 ---
		// ------------------------------------------------------------------------------------
    	$reserved_dir_name = array(
			"control",
			"comp",
			"dialog",
			"common",
			"pages"
		);
    	if (in_array($this->dir_name, $reserved_dir_name)) {
    		$this->_setMes(sprintf(MODULE_MES_DIRNAME_ER,$this->dir_name),1);
    		//処理エラー終了
       		$this->_setMes(MODULE_MES_RESULT_ERROR);
       		return $return_prefix . 'error';	
		}
    	
    	// ------------------------------------------------------------
		// --- 同一のディレクトリ名で既に登録されていないかチェック ---
		// ------------------------------------------------------------
		if($this->modulesView->getModuleByDirname($this->dir_name)) {
			$this->_setMes(sprintf(MODULE_MES_RESULT_MODULE_EXIST_ER,$this->module_name),1);
    		//処理エラー終了
       		$this->_setMes(MODULE_MES_RESULT_ERROR);
       		return $return_prefix . 'error';
		}
		// ----------------------------------------------------------------------
		// --- install.ini取得処理	　                                        ---
		// ----------------------------------------------------------------------
        $install_ini = $this->modulesView->loadInfo($this->dir_name);
		if (!$install_ini) {
			//コールバック処理
 	        $this->sqlCallback();
 	        	
			//バージョンファイルが不正
			$this->_setMes(sprintf(MODULE_MES_VERSION_ER,$this->dir_name),1);
       		//処理エラー終了
       		$this->_setMes(MODULE_MES_RESULT_ERROR);
       		return $return_prefix . 'error';	
        }
        $this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST,MODULE_MODULE_TABLE_NAME),1);
        
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
       		return $return_prefix . 'error';	
        }
        
        if($install_ini["edit_action_name"] != "" && !$this->moduleCompmain->ActionNameCheck($install_ini["edit_action_name"])) {
        	//アクション名称が不正
			$this->_setMes(sprintf(MODULE_MES_ACTION_NAME_CHECK_ER,$install_ini["edit_action_name"]),1);
       		//処理エラー終了
       		$this->_setMes(MODULE_MES_RESULT_ERROR);
       		return $return_prefix . 'error';	
        }
        
        if($install_ini["edit_style_action_name"] != "" && !$this->moduleCompmain->ActionNameCheck($install_ini["edit_style_action_name"])) {
        	//アクション名称が不正
			$this->_setMes(sprintf(MODULE_MES_ACTION_NAME_CHECK_ER,$install_ini["edit_style_action_name"]),1);
       		//処理エラー終了
       		$this->_setMes(MODULE_MES_RESULT_ERROR);
       		return $return_prefix . 'error';	
        }
        
        $this->_setMes(MODULE_MES_RESULT_ACTION_NAME_CHECK_EN,1);
        
		// -------------------------------------
		// --- モジュール用SQLファイルの取得 ---
		// -------------------------------------
		$this->_setMes(MODULE_MES_RESULT_SQL_ST,1);
		$config_obj = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "db_kind");	//conf_name=db_kind(mysql等がvalueに入る)
		if($config_obj) {
			$db_kind = $config_obj["conf_value"];
		} else {
			$db_kind = _DEFAULT_SQL_KIND;
		}
		$file_path = "/".$this->dir_name."/sql/".$db_kind."/"._SYS_TABLE_INI;
		if (@!file_exists(MODULE_DIR.$file_path) && $db_kind == "mysqli") {
			$db_kind = "mysql";
			$file_path = "/".$this->dir_name."/sql/".$db_kind."/"._SYS_TABLE_INI;
		}
		
		// -----------------------------
		// --- モジュール用SQLの実行 ---
		// -----------------------------
		if (@file_exists(MODULE_DIR.$file_path) && filesize(MODULE_DIR.$file_path) != 0) {
 	        //モジュールに使用するテーブルあり
 	        // SQLファイルの読み込み
 	        $handle = fopen(MODULE_DIR.$file_path, 'r');
			$sql_query = fread($handle, filesize(MODULE_DIR.$file_path));
			fclose($handle);
			$sql_query = trim($sql_query);
			// SQLユーティリティクラスにて各クエリを配列に格納する
			$this->databaseSqlutility->splitMySqlFile($pieces, $sql_query);
			//$created_tables = array();
			$reserved_tables = explode(",", _DB_RESERVED_TABLES);	//システムで使用不可テーブル
			
			//DBオブジェクト取得
			$db =& $container->getComponent("DbObject");
			$error = false;
			foreach ($pieces as $piece) {
				// SQLユーティリティクラスにてテーブル名にプレフィックスをつける
				// 配列としてリターンされ、				
	            // 	[0] プレフィックスをつけたクエリ
	            // 	[4] プレフィックスをつけないテーブル名
				// が格納されている
				$prefixed_query = $this->databaseSqlutility->prefixQuery($piece, $db->getPrefix());
				if ( !$prefixed_query ) {
					$this->_setMes(sprintf(MODULE_MES_RESULT_SQL_FILE_ER, $piece),2);
					$error = true;
					break;
				}
				// システムテーブル名と重ならないかチェック
				if ( in_array($prefixed_query[4], $reserved_tables) ) {
					$this->_setMes(sprintf(MODULE_MES_RESULT_SQL_TABLE_NAME_ER, $prefixed_query[4]),2);
					$error = true;
					break;
				}
	
				// 実行
				if ( !$db->execute($prefixed_query[0]) ) {
					$this->_setMes("<span class='errorstr'>".$db->ErrorMsg()."</span>",2);
					$error = true;
					break;
				}
	
				// 既に作成したテーブルの場合は、データをInsertしたメッセージ、
				// 作成してない場合は、テーブルを作成したメッセージを設定
				if ( in_array($prefixed_query[4], $this->created_tables) ) {
					$message_tmp = sprintf(MODULE_MES_RESULT_SQL_INSERT, $db->getPrefix().$prefixed_query[4]);
	        		$this->_setMes($message_tmp,2);
				} else {
	        		$message_tmp = sprintf(MODULE_MES_RESULT_SQL_CREATE,$db->getPrefix().$prefixed_query[4]);
	        		$this->_setMes($message_tmp,2);
					$this->created_tables[] = $db->getPrefix().$prefixed_query[4];					
				}	
				
			}
			if(!$error)        
 	        	$this->_setMes(MODULE_MES_RESULT_SQL_EN,1);
 	        else {
 	        	//コールバック処理
 	        	$this->sqlCallback();
 	        	
 	        	$this->_setMes(MODULE_MES_RESULT_SQL_ER,1);
 	        	//処理エラー終了
       			$this->_setMes(MODULE_MES_RESULT_ERROR);
       			return $return_prefix . 'error';
 	        }
       	} else {
       		$this->_setMes(sprintf(MODULE_MES_RESULT_SQL_GET_ER,$file_path),1);
        }
        
        // ----------------------------------------------------------------------
		// --- モジュールデータ登録関数                                       ---
		// ----------------------------------------------------------------------
        //insModule
        $this->module_id = $this->modulesAction->insModule($install_ini);
        if(!$this->module_id) {
        	//コールバック処理
 	        $this->sqlCallback();
 	        	
        	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_MODULE_TABLE_NAME),1);
        	//処理エラー終了
       		$this->_setMes(MODULE_MES_RESULT_ERROR);
       		return $return_prefix . 'error';
        }
        $this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN,MODULE_MODULE_TABLE_NAME),1);
        
        // ----------------------------------------------
		// --- CSS,JSのhtdocsへのコピー				  ---
		// ----------------------------------------------
		$dirname = $this->dir_name;
		set_time_limit(MODULE_UPDATE_TIME_LIMIT);
		if(!$this->moduleCompmain->delHtdocsFilesByDirname($dirname,$this)) {
			return $return_prefix . 'success';
		}
		if(!$this->moduleCompmain->copyHtdocsFilesByDirname($dirname,$this)) {
			return $return_prefix . 'error';
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
		
        // ----------------------------------------------------------------------
		// --- 権限モジュールリンクデータ登録(authorities_modules_link)       ---
		//      管理者に限らずデフォルト配置可能モジュールならば、登録しておく---
		// ----------------------------------------------------------------------
		if($install_ini['disposition_flag']) {
			if($install_ini['default_enable_flag']) {
				$auth_where_params = array();
			} else {
				$auth_where_params = array("user_authority_id"=>_AUTH_ADMIN);
			}
			$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST,MODULE_AUTHORITIES_MODULES_LINK_TABLE_NAME),1);
			$authority_link_flag = true;
			$authorities_obj = $this->authoritiesView->getAuthorities($auth_where_params);
			if(isset($authorities_obj[0])) {
				foreach($authorities_obj as $authoritiy) {
					if($install_ini['system_flag']) {
						switch($authoritiy['user_authority_id']) {
							case _AUTH_ADMIN:
								if($authoritiy['role_authority_id'] == _ROLE_AUTH_ADMIN) {
									$auth_default_modules = AUTHORITY_SYS_DEFAULT_MODULES_SYSADMIN;
								} else {
									$auth_default_modules = AUTHORITY_SYS_DEFAULT_MODULES_ADMIN;
								}
								break;
							case _AUTH_CHIEF:
								$auth_default_modules = AUTHORITY_SYS_DEFAULT_MODULES_CHIEF;
								break;
							case _AUTH_MODERATE:
								$auth_default_modules = AUTHORITY_SYS_DEFAULT_MODULES_MODERATE;
								break;
							case _AUTH_GENERAL:
								$auth_default_modules = AUTHORITY_SYS_DEFAULT_MODULES_GENERAL;
								break;
							case _AUTH_GUEST:
								$auth_default_modules = AUTHORITY_SYS_DEFAULT_MODULES_GUEST;
								break;
							default:
								continue;
						}
						
						$auth_default_modules_arr = array();
						if($auth_default_modules != "all") {
							$auth_default_modules_arr = explode("|", $auth_default_modules);
						}
						if(!(in_array($dirname, $auth_default_modules_arr) || $auth_default_modules == "all")) {
							continue;	
						}
					}
					if($this->dir_name == "user" && $authoritiy['user_authority_id'] == _AUTH_ADMIN && $authoritiy['role_authority_id'] != _ROLE_AUTH_ADMIN) {
						// 管理者でシステム管理者でなくとも、管理者として登録
						$authority_param = array(
							"role_authority_id" =>$authoritiy['role_authority_id'],
							"module_id" => $this->module_id,
							"authority_id" => _AUTH_ADMIN		//_AUTH_CHIEF
						);
					} else if($this->dir_name == "user" && $authoritiy['role_authority_id'] == _ROLE_AUTH_CLERK) {
						// 事務局で会員管理ならば、管理者として登録
						$authority_param = array(
							"role_authority_id" =>$authoritiy['role_authority_id'],
							"module_id" => $this->module_id,
							"authority_id" => _AUTH_ADMIN
						);
					} else {
						$authority_param = array(
							"role_authority_id" =>$authoritiy['role_authority_id'],
							"module_id" => $this->module_id,
							"authority_id" => $authoritiy['user_authority_id']
						);
					}
					if($this->authoritiesAction->insAuthorityModuleLink($authority_param)) {
						//インサート成功
					} else {
						$authority_link_flag = false;	
					}
				}
			}
			if($authority_link_flag) {
		    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN,MODULE_AUTHORITIES_MODULES_LINK_TABLE_NAME),1);
			} else {
		    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_AUTHORITIES_MODULES_LINK_TABLE_NAME),1);
			}
		}
	    // ----------------------------------------------------------------------
		// --- ページモジュールリンクデータ登録                               ---
		//      一般モジュール　かつ　　　　　　　　　　                      ---
		//      デフォルト配置可能フラグがONの場合、登録                      ---
		// ----------------------------------------------------------------------
		if(!$install_ini['system_flag']) {
			$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST,MODULE_PAGES_MODULES_LINK_TABLE_NAME),1);
			$pages_id_object = $this->db->execute("SELECT {pages}.page_id,{pages}.space_type, {pages}.private_flag" . 
									" FROM {pages} " .
									" WHERE {pages}.room_id  = {pages}.page_id " .
									" ");
			$pages_modules_link_flag = true;
			foreach($pages_id_object as $page_id_object) {
				$insert_flag = false;
				if($page_id_object['private_flag'] == _OFF && $install_ini['disposition_flag'])
					$insert_flag = true;
				if($insert_flag && ($install_ini['default_enable_flag'] || $page_id_object['page_id'] == _SPACE_TYPE_PUBLIC)) {
					$authority_param = array(
						"room_id" =>$page_id_object['page_id'],
						"site_id" => $this->session->getParameter("_site_id"),
						"module_id" => $this->module_id
					);
					if($this->pagesAction->insPagesModulesLink($authority_param)) {
						//インサート成功
					} else {
						$pages_modules_link_flag = false;	
					}
				}
			}
			if($pages_modules_link_flag) {
		    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN,MODULE_PAGES_MODULES_LINK_TABLE_NAME),1);
			} else {
		    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_PAGES_MODULES_LINK_TABLE_NAME),1);
			}
		}
		// ---------------------------------------------------------
		// --- インストールアクション実行(module_install_action) ---
		// ---------------------------------------------------------
		if(isset($install_ini['module_install_action'])) {
	    	list ($classname, $filename) = $actionChain->makeNames($install_ini["module_install_action"]);
	    	if (!$filename || !@file_exists($filename)) {
	    		$this->_setMes(sprintf(MODULE_MES_NOEXISTS_INSTALLFILE,$filename),1);
	    		//処理エラー終了
	   			$this->_setMes(MODULE_MES_RESULT_ERROR);
	   			return $return_prefix . 'error';
	    	}
	    	include_once $filename;
	    	if(!class_exists($classname)) {
	    		$this->_setMes(sprintf(MODULE_MES_NOEXISTS_CLASSNAME,$classname),1);
	    		//処理エラー終了
	   			$this->_setMes(MODULE_MES_RESULT_ERROR);
	   			return $return_prefix . 'error';
			}
			
			$params = array("action" =>"module_action_admin_install",  "module_id" =>$this->module_id);
			$result = $this->preexecute->preExecute($install_ini['module_install_action'], $params);
			
			//再度、sessionにauth_id等をセット
			//$this->authCheck->AuthCheck($actionChain->getCurActionName(),0, 0);
        
			if(!$result) {
				$this->_setMes(sprintf(MODULE_MES_INSTALLFILE_ER,$classname),1);
	    		//処理エラー終了
	   			$this->_setMes(MODULE_MES_RESULT_ERROR);
	   			return $return_prefix . 'error';	
			}
	    	$this->_setMes(sprintf(MODULE_MES_INSTALLFILE_EN,$classname),1);
		}

		// --------------------------------------------------
		// --- Config登録処理実行(Config) ---
		// --------------------------------------------------
		if(isset($install_ini['Config'])) {
			$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST,MODULE_CONFIG_TABLE_NAME),1);
			$result = false;
			foreach ($install_ini['Config'] as $key=>$item) {
				$result = $this->configAction->insConfigValue($this->module_id, $key, $item);
				if (!$result) break;
			}
			if($result) {
		    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN,MODULE_CONFIG_TABLE_NAME),1);
			} else {
		    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER,MODULE_CONFIG_TABLE_NAME),1);
	    		//処理エラー終了
	   			$this->_setMes(MODULE_MES_RESULT_ERROR);
	   			return $return_prefix . 'error';	
			}
		}

		// --------------------------------------------------
		// --- Mobile登録処理実行(Mobile) ---
		// --------------------------------------------------
		if (isset($install_ini['Mobile'])) {
			$module_mobile = $this->modulesView->getModuleByDirname("mobile");
			if (!empty($module_mobile)) {
				$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ST, MODULE_MOBILE_TABLE_NAME), 1);
				$mobile_params = array("module_id"=>$this->module_id);
				foreach ($install_ini['Mobile'] as $key=>$item) {
					if ($key == "header" && isset($install_ini['Mobile']["header"]) && $install_ini['Mobile']["header"] == _ON) {
						$mobile_params["display_position"] = _DISPLAY_POSITION_HEADER;
						continue;
					}
					$mobile_params[$key] = $item;
				}
				$result = $this->mobileAction->insertMobile($this->module_id, $mobile_params);
				if ($result) {
			    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_EN, MODULE_MOBILE_TABLE_NAME), 1);
				} else {
			    	$this->_setMes(sprintf(MODULE_MES_RESULT_REGIST_ER, MODULE_MOBILE_TABLE_NAME), 1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return $return_prefix . 'error';	
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
        
        $this->system_flag = $install_ini['system_flag'];
        
        // ---------------------------------------------------------------------
		// --- 権限管理が使用できるかどうか（権限選択を行わせるかどうか）	 ---
		// ---------------------------------------------------------------------
		if($this->install_flag != _ON) {
			$role_authority_id =$this->session->getParameter("_role_auth_id");
	        $authorities_module_link =& $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($role_authority_id, null, null, array($this, "_fetchcallbackAuthorityModuleLink"));
	        if(isset($authorities_module_link['authority_id']) && $authorities_module_link['authority_id'] >= _AUTH_CHIEF) {
	        	//権限管理使用可能　かつ　主担以上
	        	$this->selectauth_flag = true;
	        }
		}
        return $return_prefix . 'success';
    }
    
    function _setMes($mes,$tabLine=0) {
    	$this->result_str .= $this->modulesView->getShowMes($mes,$tabLine);
    }
    
     /**
     * エラーが発生した場合は作成したテーブルを削除し、次回のインストールに備える
     *
     * @access  public
     */
    function sqlCallback()
    {
    	$container =& DIContainerFactory::getContainer();
    	$db =& $container->getComponent("DbObject");
    	
    	if ( is_array($this->created_tables) ) {
    		foreach ($this->created_tables as $created_table) {
    			$db->execute("DROP TABLE ".$created_table);
    		}
		}
		//登録DBの削除
		if($this->module_id != null && $this->module_id != false){
    		$this->modulesAction->delModuleById($this->module_id);
			$this->configAction->delConfigValue($this->module_id);
		}
    }
    
	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @return array configs
	 * @access	private
	 */
	function _fetchcallbackAuthorityModuleLink($result) 
	{
		$authorities_module_link = array();
		while ($row = $result->fetchRow()) {
			$pathList = explode("_", $row["action_name"]);
			if($row["authority_id"] != null && $pathList[0] == "authority") {
				$authorities_module_link = $row;
			}
		}
		return $authorities_module_link;
	}
}
?>
