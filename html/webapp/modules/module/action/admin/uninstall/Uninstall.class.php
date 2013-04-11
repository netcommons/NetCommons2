<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *  モジュールアンインストール
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Module_Action_Admin_Uninstall extends Action
{
	
	// リクエストパラメータを受け取るため
	var $upd_module_id = null;
	
	//バリデートの値を受け取るため
	var $module_obj = null;
	
	//使用コンポーネント
	var $modulesView = null;
	var $modulesAction = null;
	var $configView = null;
	var $configAction = null;
	var $databaseSqlutility = null;
	var $pagesAction = null;
	var $blocksView = null;
	var $blocksAction = null;
	var $moduleCompmain = null;
	var $authoritiesAction = null;
	var $preexecute = null;
	//var $authCheck = null;
	var $uploadsView = null;
	var $uploadsAction = null;
	var $fileAction = null;
	var $getdata = null;
	var $db = null;
	var $mobileAction = null;
	
	//結果文字列
	var $result_str = "";
	var $module_name = null;
    /**
     * アンインストール処理
     *
     * @access  public
     */
    function execute()
    {
    	//TODO:maple.iniで指定するように後に修正するほうがよい
    	$container =& DIContainerFactory::getContainer();
    	$filterChain =& $container->getComponent("FilterChain");
    	$actionChain =& $container->getComponent("ActionChain");
    	$db =& $container->getComponent("DbObject");
    	
    	$this->module_name = $this->module_obj["module_name"];
    	//処理開始
    	$this->_setMes(MODULE_MES_RESULT_START);
    	if(!$this->module_obj) {
    		$this->_setMes(MODULE_MES_GET_ER,1);
    		//処理エラー終了
    		$this->_setMes(MODULE_MES_RESULT_ERROR);
    		return 'error';
    	} else {
	    	// -------------------------------------
			// --- モジュール用SQLファイルの取得 ---
			// -------------------------------------
			//action_nameからinstall.iniを取得
    		$pathList = explode("_", $this->module_obj["action_name"]);
    		$dirname = $pathList[0];
    		
			$this->_setMes(MODULE_MES_RESULT_SQL_ST,1);
			$config_obj = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "db_kind");	//conf_name=db_kind(mysql等がvalueに入る)
			if($config_obj) {
				$db_kind = $config_obj["conf_value"];
			} else {
				$db_kind = _DEFAULT_SQL_KIND;
			}
			$file_path = "/".$dirname."/sql/".$db_kind.'/'._SYS_TABLE_INI;
			if (@!file_exists(MODULE_DIR.$file_path) && $db_kind == "mysqli") {
				$db_kind = "mysql";
				$file_path = "/".$dirname."/sql/".$db_kind.'/'._SYS_TABLE_INI;
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
				
				//DBオブジェクト取得
				$error = false;
				$error_del = false;
				$created_tables = array();
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
					
					//既にTABLEが存在しなければ、エラーメッセージを変更
					if(in_array($db->getPrefix().$prefixed_query[4],$created_tables)) {
						continue;
					}else if($db->execute("DROP TABLE ".$db->getPrefix().$prefixed_query[4])) {
						$message_tmp = sprintf(MODULE_MES_RESULT_SQL_DELETE,$db->getPrefix().$prefixed_query[4]);
		        		$this->_setMes($message_tmp,2);
		        		$created_tables[] = $db->getPrefix().$prefixed_query[4];
		        		//seq_idテーブルがあればDROP
		        		if($db->execute("DROP TABLE ".$db->getPrefix().$prefixed_query[4]._SYS_TABLE_SEQID_POSTFIX)) {
							$message_tmp = sprintf(MODULE_MES_RESULT_SQL_DELETE,$db->getPrefix().$prefixed_query[4]._SYS_TABLE_SEQID_POSTFIX);
		        			$this->_setMes($message_tmp, 3);
		        			$created_tables[] = $db->getPrefix().$prefixed_query[4]._SYS_TABLE_SEQID_POSTFIX;
		        		}
					} else {
						$message_tmp = sprintf(MODULE_MES_RESULT_SQL_DELETE_ERR,$db->getPrefix().$prefixed_query[4]);
		        		$this->_setMes($message_tmp,2);	
		        		$error_del = true;
					}
				}
				if(!$error) {    
	 	        	if($error_del) {
	 	        		$this->_setMes(MODULE_MES_RESULT_SQL_ER,1);
	 	        	} else
	 	        		$this->_setMes(MODULE_MES_RESULT_SQL_EN,1);
				} else {
	 	        	$this->_setMes(MODULE_MES_RESULT_SQL_ER,1);
	 	        	//処理エラー終了
	       			$this->_setMes(MODULE_MES_RESULT_ERROR);
	       			return 'error';
	 	        }
	       	} else {
	       		$this->_setMes(sprintf(MODULE_MES_RESULT_SQL_GET_ER,$file_path),1);
	        }
    		
    		$install_ini = $this->modulesView->loadInfo($dirname);
    		if (!$install_ini) {
    			//バージョンファイルが不正
    			$this->_setMes(sprintf(MODULE_MES_VERSION_ER,$dirname),1);
	       		//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';	
	        }
	        
			// ----------------------------------------------
			// --- ブロックテーブルのデータ削除		 ---
			// ----------------------------------------------
			$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST,MODULE_BLOCKS_TABLE_NAME),1);
			$blocks_obj = $this->blocksView->getBlockByModuleId($this->upd_module_id);
			if(is_array($blocks_obj)) {
				if(count($blocks_obj) >= 1) {
					$delblock_action_name = "pages_actionblock_deleteblock";
					foreach($blocks_obj as $block_obj) {
						$this->getdata->setParameter(array("blocks", $block_obj['block_id']), $block_obj);
						$params = array("action" =>"module_action_admin_uninstall", "page_id" =>$block_obj['page_id'], "block_id" =>$block_obj['block_id']);
						$result = $this->preexecute->preExecute($delblock_action_name, $params);
						//$resultが'success'ならば成功。エラーの場合も対処しない。
					}
					//再度、sessionにauth_id等をセット
	        		////$this->authCheck->AuthCheck($actionChain->getCurActionName(),0, 0);
				}
			} else {
				//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';	
			}
			$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,MODULE_BLOCKS_TABLE_NAME),1);
	        // ---------------------------------------------------------------
			// --- アンインストールアクション実行(module_uninstall_action) ---
			// ---------------------------------------------------------------
			if(isset($install_ini['module_uninstall_action'])) {
		    	list ($classname, $filename) = $actionChain->makeNames($install_ini["module_uninstall_action"]);
		    	if (!$filename || !@file_exists($filename)) {
		    		$this->_setMes(sprintf(MODULE_MES_NOEXISTS_UNINSTALLFILE,$filename),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'error';
		    	}
		    	include_once $filename;
		    	if(!class_exists($classname)) {
		    		$this->_setMes(sprintf(MODULE_MES_NOEXISTS_CLASSNAME,$classname),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'success';
				}
				
				$params = array("action" =>$install_ini['module_uninstall_action'], "module_id" =>$this->upd_module_id);
				$result = $this->preexecute->preExecute($install_ini['module_uninstall_action'], $params);
				
				//再度、sessionにauth_id等をセット
        		//$this->authCheck->AuthCheck($actionChain->getCurActionName(),0, 0);
				
				if(!$result) {
					$this->_setMes(sprintf(MODULE_MES_UNINSTALLFILE_ER,$classname),1);
		    		//処理エラー終了
		   			$this->_setMes(MODULE_MES_RESULT_ERROR);
		   			return 'error';	
				}
		    	$this->_setMes(sprintf(MODULE_MES_UNINSTALLFILE_EN,$classname),1);
			}
			// -------------------------------------------------------------
			// --- アップロードテーブルのデータ削除＆ファイル削除		 ---
			// -------------------------------------------------------------
			$this->_setMes(MODULE_MES_RESULT_UPLOADS_ST,1);
			$del_flag = false;
			$uploads_objs = $this->uploadsView->getUploadByModuleid($this->upd_module_id);
			if($uploads_objs != null) {
				foreach($uploads_objs as $upload_obj) {
					$pathname = FILEUPLOADS_DIR.$upload_obj['file_path'];
					if (file_exists($pathname)) {
						$del_flag = true;
						$this->fileAction->delDir($pathname);
					}
				}
			}
			if ($del_flag) {
				$this->_setMes(MODULE_MES_RESULT_UPLOADS_EN,1);
			}

			// ----------------------------------------------
			// --- モジュールテーブルのデータ削除		 ---
			// ----------------------------------------------
			$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST,MODULE_MODULE_TABLE_NAME),1);
	        if(!$this->modulesAction->delModuleById($this->upd_module_id) || !$this->modulesAction->decrementDisplaySequence($this->module_obj["display_sequence"],$this->module_obj["system_flag"])) {
	        	$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER,MODULE_MODULE_TABLE_NAME),1);
	        	//処理エラー終了
	       		$this->_setMes(MODULE_MES_RESULT_ERROR);
	       		return 'error';
	        }
	        $this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,MODULE_MODULE_TABLE_NAME),1);
	       
	        
	        // ----------------------------------------------------------------------
			// --- 権限モジュールリンクデータ削除                                 ---
			// ----------------------------------------------------------------------
			$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST,MODULE_AUTHORITIES_MODULES_LINK_TABLE_NAME),1);
			$where_params = array(
				"module_id" => $this->upd_module_id
			);
			if($this->authoritiesAction->delAuthorityModuleLink($where_params)) {
				//削除成功
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,MODULE_AUTHORITIES_MODULES_LINK_TABLE_NAME),1);
			} else {
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER,MODULE_AUTHORITIES_MODULES_LINK_TABLE_NAME),1);
			}
			
			// ----------------------------------------------------------------------
			// --- ページモジュールリンクデータ削除                                 ---
			// ----------------------------------------------------------------------
			$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST,MODULE_PAGES_MODULES_LINK_TABLE_NAME),1);
			if ($this->pagesAction->deleteRoomModule(null, array($this->upd_module_id))) {
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,MODULE_PAGES_MODULES_LINK_TABLE_NAME),1);
			} else {
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER,MODULE_PAGES_MODULES_LINK_TABLE_NAME),1);
			}

			// --------------------------------------------------
			// --- Configデータ削除処理実行(Config) ---
			// --------------------------------------------------
			if(intval($this->upd_module_id) != 0) {
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST,MODULE_CONFIG_TABLE_NAME),1);
				if($this->configAction->delConfigValueByModid($this->upd_module_id)) {
					//削除成功
					$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,MODULE_CONFIG_TABLE_NAME),1);
				} else {
					$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER,MODULE_CONFIG_TABLE_NAME),1);
				}
			}
			// --------------------------------------------------
			// --- Mobilデータ削除処理実行(Mobile) ---
			// --------------------------------------------------
			if (isset($install_ini['Mobile'])) {
				$module_mobile = $this->modulesView->getModuleByDirname("mobile");
				if (!empty($module_mobile)) {
					$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST, MODULE_MOBILE_TABLE_NAME), 1);
					$result = $this->mobileAction->deleteMobile($this->upd_module_id);
					if ($result) {
						//削除成功
						$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,MODULE_MOBILE_TABLE_NAME), 1);
					} else {
						$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER,MODULE_MOBILE_TABLE_NAME), 1);
					}
				}
			}

			// --------------------------------------------------
			// --- shortcutデータ削除処理実行(shortcut) ---
			// --------------------------------------------------
			$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST, MODULE_SHORTCUT_TABLE_NAME),1);
			if($this->db->deleteExecute("shortcut", array("module_id" => $this->upd_module_id))) {
				//削除成功
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN, MODULE_SHORTCUT_TABLE_NAME),1);
			} else {
				$this->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER, MODULE_SHORTCUT_TABLE_NAME),1);
			}
			
			
	        // ----------------------------------------------
			// --- CSS,JSのhtdocsからの削除				  ---
			// ----------------------------------------------
			if(!$this->moduleCompmain->delHtdocsFilesByDirname($dirname,$this)) {
				return 'error';
			}
			$this->_setMes(MODULE_MES_RESULT_DELETEJS_ST,1);
			if(!$this->moduleCompmain->deleteJsFiles($dirname)) {
				$this->_setMes(MODULE_MES_RESULT_DELETEJS_ER,1);
			} else {
				$this->_setMes(MODULE_MES_RESULT_DELETEJS_EN,1);
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
