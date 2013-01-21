<?php
/**
 *  モジュールコモンコンポーネント
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Module_Components_Compmain {
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	var $_filterChain = null;
	
	var $_db = null;
	var $_fileAction = null;
	var $_fileView = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Module_Components_Compmain() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_filterChain =& $this->_container->getComponent("FilterChain");
		$this->_actionChain =& $this->_container->getComponent("ActionChain");
		
		$this->_db =& $this->_container->getComponent("DbObject");
		
		$commonMain =& $this->_container->getComponent("commonMain");
		$this->_fileAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/Action.class.php', "File_Action", "fileAction");
        $this->_fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
	}
	
	/**
	 * モジュール毎のキャッシュクリア処理
	 *
	 * @access	public
	 */
	function clearCacheByDirname($dirname) {
		// ----------------------------------------------
		// --- キャッシュクリア		 ---
		// ----------------------------------------------
		if($dirname == "pages" || $dirname == "control") {
			$temp_name = "";
		} else {
			$modulesView =& $this->_container->getComponent("modulesView");
			$module =& $modulesView->getModuleByDirname($dirname);
			$temp_name = isset($module['temp_name']) ? $module['temp_name'].'/' : "";
		}
		
		if($this->_filterChain->hasFilterByName("Cache")) {
			$renderer = new SmartyTemplate();
			//$renderer =& SmartyTemplate::getInstance();
			//$session =& $this->_container->getComponent("Session");
			//if (is_object($session)) {
			//	$renderer->setSession($session);
			//}
			if(is_object($renderer)) {
				//DirName以下のコンパイルディレクトリの中身を全て破棄する
				$path = "/" . $dirname. "/templates/";
				$base_temp_name = $path.$temp_name;
				$renderer->clear_compiled_tpl(null,$path);
				$this->_clearCompiledTpl($path, $base_temp_name, $renderer);
				
				$cache =& $this->_filterChain->getFilterByName("Cache");
				$clear_cache = $cache->getClearCache();	//値を保存
				$cache->setClearCache(array($dirname, DEFAULT_ACTION, "control_view_main"));
				//キャッシュクリア
				$renderer->clear_cache();
				$cache->setClearCache($clear_cache);	//元に戻す
				
				return true;
			}
		}
		return false;
	}
	
	/**
	 * templates/mainのキャッシュクリア処理
	 *
	 * @access	public
	 */
	function clearCacheMainByDirname() {
		// ----------------------------------------------
		// --- キャッシュクリア		 ---
		// ----------------------------------------------
		$renderer = new SmartyTemplate();
		//$renderer =& SmartyTemplate::getInstance();
		//TODO:うまく機能していないようなので、後に調査
		//clear_all_cacheを呼ぶとエラーになる
		//$renderer->clear_all_cache();
		$renderer->clearTemplates_c();
		return false;
	}
	
	function _clearCompiledTpl($path, $base_temp_name, &$renderer,$clearbase_dir = MODULE_DIR) {
		$errorList =& $this->_actionChain->getCurErrorList();
		$renderer->setErrorList($errorList);
		$fetch_flag = false;
		if(preg_match('/^'.preg_quote($base_temp_name, '/').'/', $path)) {
			$fetch_flag = true;
		}
		//fetch処理（template_cにファイル生成処理）
		$current_files = $this->_fileView->getCurrentFiles($clearbase_dir . $path);
		if($current_files) {
			foreach($current_files as $current_file) {
				if (preg_match("/.html$/", $current_file)) {
					if($base_temp_name == "/pages/templates/" || $base_temp_name == "/control/templates/") {
						//main.htmlは、page.htmlからのincludeを使用してのみfetchしないとaddmodule_box.htmlのインクルードに失敗するため
						//fetchしない
						//また、読み込む側より、読み込まれる側(include path)が、若いテンプレートNoをもっていないと
						//「Smarty error: unable to read template resource」となるが
						//気にする必要はない
					} else if($fetch_flag) {
						$renderer->clear_all_assign();
						$renderer->setTemplateDir($clearbase_dir . $path);
						$renderer->fetch($current_file,null, $path);
					}
				}
			}
		}
		$current_dirs = $this->_fileView->getCurrentDir($clearbase_dir . $path);
		if($current_dirs) {
			foreach($current_dirs as $current_dir) {
				$renderer->clear_compiled_tpl(null, $path.$current_dir."/");
				//再帰処理
				$this->_clearCompiledTpl($path.$current_dir."/", $base_temp_name, $renderer, $clearbase_dir);
			}
		}
	}
	/**
	 * htdocsからファイル削除処理
	 * @param string dirname
	 * @param array  this_obj
	 * @return boolean
	 * @access	public
	 */
	function delHtdocsFilesByDirname($dirname,&$this_obj) {
		if(START_INDEX_DIR != HTDOCS_DIR) {
			// htdocs内にimages、css等のディレクトリはないはずなので処理しない
			return true;	
		}
		// ----------------------------------------------
		// --- CSS,JSのhtdocsへの削除				  ---
		// ----------------------------------------------
		
		//削除処理
		
		$htdocs_dir = array(
			"css_dir" => "/css",
			//"css_dir" => "/css/".$dirname,
			"js_dir" => "/js/".$dirname,
			"images_dir" => "/images/".$dirname
		);
		foreach($htdocs_dir as $htdoc_dir) {
			if(@file_exists(HTDOCS_DIR.$htdoc_dir)) {
				$this_obj->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST,"[".$htdoc_dir."]"),1);
				if(!$this->_fileAction->delDir(HTDOCS_DIR.$htdoc_dir)) {
					$this_obj->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER,"[".$htdoc_dir."]"),1);
	        		//処理エラー終了
	       			$this_obj->_setMes(MODULE_MES_RESULT_ERROR);
	       			return false;	
				}
				$this_obj->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,"[".$htdoc_dir."]"),1);
			}
		}
		
		return true;
	}
	
	/**
	 * htdocsへのファイルコピー処理
	 * @param string dirname
	 * @param array  this_obj
	 * @return boolean
	 * @access	public
	 */
	function copyHtdocsFilesByDirname($dirname,&$this_obj) {
		if(START_INDEX_DIR != HTDOCS_DIR) {
			// htdocs内にimages、css等のディレクトリはないはずなので処理しない
			return true;	
		}
		// ----------------------------------------------
		// --- CSS,JSのhtdocsへのコピー				  ---
		// ----------------------------------------------
		
		//コピー先
		$htdocs_dir = array(
			//"css_dir" => "/css/".$dirname,
			//"js_dir" => "/js/".$dirname,
			"images_dir" => "/images/".$dirname
		);
		//if(!@file_exists(HTDOCS_DIR."/css")) { 
		//	mkdir(HTDOCS_DIR."/css", 0777);
		//}
		if(!@file_exists(HTDOCS_DIR."/js")) { 
			mkdir(HTDOCS_DIR."/js", 0777);
		}
		if(!@file_exists(HTDOCS_DIR."/images")) { 
			mkdir(HTDOCS_DIR."/images", 0777);
		}
		
		//コピー元
		$files_dir = array(
			//"css_dir" => "/".$dirname."/files/css/",
			//"js_dir" => "/".$dirname."/files/js/",
			"images_dir" => "/".$dirname."/files/images/"
		);
		foreach($files_dir as $key => $file_dir) {
			if(@file_exists(MODULE_DIR.$file_dir)) {
				//jsファイルは言語ファイルのみコピー、その他は結合対象
				//if(($key == "js_dir") || $key != "js_dir") {
					$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ST,"[".$file_dir."]"),1);
					if(!$this->_fileAction->copyDir(MODULE_DIR.$file_dir,HTDOCS_DIR.$htdocs_dir[$key])) {
						$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ER,"[".$file_dir."]"),1);
		        		//処理エラー終了
		       			$this_obj->_setMes(MODULE_MES_RESULT_ERROR);
		       			return false;	
					}
					$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_EN,"[".$file_dir."]"),1);
				//}
			}
		}
		return true;
	}
	
	/**
	 * themeファイルのコピー処理
	 * TODO:テーマ管理で行うほうが望ましいが、現状、こちらで実装
	 * @param string dirname
	 * @param array  this_obj
	 * @return boolean
	 * @access	public
	 */
	function copyThemesFiles(&$this_obj) {
		if(START_INDEX_DIR != HTDOCS_DIR && preg_match("/^".preg_quote(BASE_DIR."/webapp/style", "/")."/i", STYLE_DIR)) {
			// themeファイルを個々にもっていない場合、処理しない
			return true;	
		}
		$result = $this->registFile(STYLE_DIR."/"."*", "theme", true);
		if($result === false) {
			return $result;
		}
		
		$dir_name = "/themes/";
		if(@file_exists(START_INDEX_DIR.$dir_name)) {
			$this_obj->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ST,"[".$dir_name."]"),1);
			if(!$this->_fileAction->delDir(START_INDEX_DIR.$dir_name)) {
				$this_obj->_setMes(sprintf(MODULE_MES_RESULT_DELETE_ER,"[".$dir_name."]"),1);
        		//処理エラー終了
       			$this_obj->_setMes(MODULE_MES_RESULT_ERROR);
       			return false;	
			}
			$this_obj->_setMes(sprintf(MODULE_MES_RESULT_DELETE_EN,"[".$dir_name."]"),1);
		}
		mkdir(START_INDEX_DIR.$dir_name, 0755);
		
		$theme_images = "/images/";
		$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ST,"[".$theme_images."]"),1);
		if(!$this->_fileAction->copyDir(STYLE_DIR.$theme_images, START_INDEX_DIR."/themes".$theme_images)) {
			$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ER,"[".$theme_images."]"),1);
    		//処理エラー終了
   			$this_obj->_setMes(MODULE_MES_RESULT_ERROR);
   			return false;	
		}
		$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_EN,"[".$theme_images."]"),1);
		
		//$theme_css = "/css/";
		//$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ST,"[".$theme_css."]"),1);
		//if(!$this->_fileAction->copyDir(STYLE_DIR.$theme_css, START_INDEX_DIR."/themes".$theme_css)) {
		//	$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ER,"[".$theme_css."]"),1);
    	//	//処理エラー終了
   		//	$this_obj->_setMes(MODULE_MES_RESULT_ERROR);
   		//	return false;	
		//}
		//$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_EN,"[".$theme_css."]"),1);
					
		// ----------------------------------------------
		// --- imagesのhtdocsへのコピー			  ---
		// ----------------------------------------------
		$themes_arr = $this->_fileView->getCurrentDir(STYLE_DIR."/themes/");
		foreach($themes_arr as $theme_name) {
			$theme_images = "/themes/".$theme_name."/images/";
			if(!file_exists(START_INDEX_DIR. "/themes/".$theme_name."/")) {
				mkdir(START_INDEX_DIR. "/themes/".$theme_name."/", 0755);
			}
			//if(!file_exists(START_INDEX_DIR. "/themes/".$theme_name."/css/")) {
			//	mkdir(START_INDEX_DIR. "/themes/".$theme_name."/css/", 0755);
			//}
			if(!file_exists(START_INDEX_DIR. "/themes/".$theme_name."/images/")) {
				mkdir(START_INDEX_DIR. "/themes/".$theme_name."/images/", 0755);
			}
			if(file_exists(STYLE_DIR.$theme_images)) {
				$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ST,"[".$theme_images."]"),1);
				if(!$this->_fileAction->copyDir(STYLE_DIR.$theme_images, START_INDEX_DIR.$theme_images)) {
					$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ER,"[".$theme_images."]"),1);
		    		//処理エラー終了
		   			$this_obj->_setMes(MODULE_MES_RESULT_ERROR);
		   			return false;	
				}
				$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_EN,"[".$theme_images."]"),1);
			}
			//$theme_css = "/themes/".$theme_name."/css/";
			//if(file_exists(STYLE_DIR.$theme_css)) {
			//	$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ST,"[".$theme_css."]"),1);
			//	if(!$this->_fileAction->copyDir(STYLE_DIR.$theme_css, START_INDEX_DIR.$theme_css)) {
			//		$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_ER,"[".$theme_css."]"),1);
		    //		//処理エラー終了
		   	//		$this_obj->_setMes(MODULE_MES_RESULT_ERROR);
		   	//		return false;	
			//	}
			//	$this_obj->_setMes(sprintf(MODULE_MES_RESULT_CREATE_EN,"[".$theme_css."]"),1);
			//}
		}
		return true;
	}
	/**
	 * モジュール毎のキャッシュクリア処理
	 * @param string action_name
	 * @return boolean
	 * @access	public
	 */
	function ActionNameCheck($action_name) {
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $action_name)) {
            return false;
        }
        list ($className, $filename) = $this->_actionChain->makeNames($action_name, true);
        
		if (!$className) {
			return false;
		}
		return true;
	}
	
	/**
	 * jsファイル共通登録処理
	 * @param  array $common_dir
	 * @return boolean
	 * @access	public
	 */
	function registCommonFile(&$common_dir) {
		// 共通系削除処理
		$where_params = array(
			"system_flag" => _ON
		);
		$result = $this->_db->deleteExecute("javascript_files", $where_params);
		
		$where_params = array(
			"type="._CSS_TYPE_COMMON. " OR type="._CSS_TYPE_THEME => null
		);
		
		//$where_params = array(
		//	"type="._CSS_TYPE_COMMON. " OR type="._CSS_TYPE_MODULE . " OR type="._CSS_TYPE_THEME => null
		//);
		$result = $this->_db->deleteExecute("css_files", $where_params);
		
		foreach($common_dir as $dir_name) {
			$files_dir = array(
				"css_dir" => "/".$dir_name."/files/css/",
				"js_dir" => "/".$dir_name."/files/js/"
			);
			foreach($files_dir as $file_dir) {
				$result = $this->registFile(MODULE_DIR.$file_dir."*", $dir_name, true);
				if($result === false) {
					return $result;
				}
			}
		}
		// htdocsへのlang_common.jsコピー処理
		$this->copyCommonLangFile();
		
		return true;
	}
	
	/**
	 * htdocsへのlang_common.jsコピー処理
	 * @return boolean
	 * @access	public
	 */
	function copyCommonLangFile() {
		if(START_INDEX_DIR != HTDOCS_DIR) {
			// htdocs内にimages、css等のディレクトリはないはずなので処理しない
			return true;	
		}
		$global_lang_path = WEBAPP_DIR . "/language";
		$common_js_filename = "lang_common.js";
		$js_path = HTDOCS_DIR."/js";
		$dirArray = glob( $global_lang_path . "/*" );
		if(is_array($dirArray)) {
			foreach( $dirArray as $child_langpath){
				if(is_dir( $child_langpath ) && @file_exists($child_langpath . "/". $common_js_filename)) {
					$lang_dir = basename($child_langpath);
					$result = $this->_fileAction->copyFile($child_langpath . "/". $common_js_filename, $js_path. "/" . $lang_dir . "/" . $common_js_filename);
					if($result === false) {
						return false;
					}
				}
			}
		}
		
		
		return true;
	}
	
	/**
	 * jsファイル,cssファイル登録処理
	 * @param  string    $dir_path (MODULE_DIR."/". $module_dir ."/"."*")
	 * @param  string    $module_name
	 * @param  boolean   $system_flag
	 * @return boolean
	 * @access	public
	 */
	function registFile($dir_path, $module_name=null, $system_flag=_OFF) {
		$dirArray = glob( $dir_path );
		if(is_array($dirArray)) {
			foreach( $dirArray as $child_dirname){
				//if(($child_path != "" && !is_dir( $child_dirname )) || substr($child_dirname, -3) == "CVS") {
				//	continue;
				//}
				if($module_name == null) {
					$pathList = explode("/", $child_dirname);
					$sub_module_name = $pathList[count($pathList) - 1];
				} else {
					$sub_module_name = $module_name;
				}
				$fileArray=glob( $child_dirname . "*");
				//$fileArray=glob( $child_dirname. $child_path . "*");
				if(is_array($fileArray)) {
					foreach( $fileArray as $filename){
						if(is_dir( $filename )) {
							if(substr($filename, -3) != "CVS" || strtolower($filename) != '.svn') {
								$this->registFile($filename."/"."*", $sub_module_name, $system_flag);
							}
						} else {
							if(substr($filename, -3) == ".js") {
								$this->_regist($filename, $sub_module_name, $system_flag);
							} else if(substr($filename, -4) == ".css") {
								$this->_registCss($filename, $sub_module_name, $system_flag);
							}
						}
					}
				}
			}
		}
		
		return true;
	}
	
	function _registCss($filepath, $dir_name, $system_flag=_OFF) {
		//$modules_dir = array(
		//	"control",
		//	"comp",
		//	"dialog"
		//);
		$common_admin_flag = _OFF;
		$common_general_flag = _OFF;
		$filename = basename($filepath);
		$type = _CSS_TYPE_MODULE;
		if($dir_name == "theme") {
			$key_dir_name = preg_replace("/^".preg_quote(STYLE_DIR, '/')."/i", "", $filepath);
		} else {
			$key_dir_name = preg_replace("/^".preg_quote(MODULE_DIR, '/')."/i", "", $filepath);
			$key_dir_name = "/".$dir_name.preg_replace("/^\/".preg_quote($dir_name, '/')."\/files\/css/i", "", $key_dir_name);
		}
		switch($dir_name) {
			case "comp":
				$compcommon_arr =  explode("|", MODULE_COMMON_COMPCOMMON_JS);
				$dir_name = substr($filename, 0,strlen($filename)-4);
				break;
			case "control":
				$common_admin_flag = _ON;
				break;
			case "theme":
				if($key_dir_name == "/css/page_style.css") {
					$common_general_flag = _ON;
					$type = _CSS_TYPE_COMMON;
				} else if($key_dir_name == "/css/style.css" || $key_dir_name == "/css/common.css") {
					$common_admin_flag = _ON;
					$common_general_flag = _ON;
					$type = _CSS_TYPE_COMMON;
				} else {
					$type = _CSS_TYPE_THEME;
				}
				
				break;
			//case "dialog":
			//	break;		
		}
		
		$dir_name = $key_dir_name;
		$contents = $this->_fileCompressor($filepath, true);
			
		$result = $this->_db->selectExecute("css_files", array("dir_name" => $dir_name), null, 1);
		if($result === false) {
			return false;	
		}
		if(isset($result[0]) && $contents == $result[0]['data']) {
			// CSSファイル変更なし
			return true;	
		}
		if(isset($result[0])) {
			// アップデート	
			$data = $contents;
			$params=array(
						"type" => $type,
						"block_id" => 0,
						"data" => $data,
						"system_flag" => $system_flag,
						"common_general_flag" => $common_general_flag,
						"common_admin_flag" => $common_admin_flag
					);
			$where_params=array("dir_name" => $dir_name);
			$result = $this->_db->updateExecute("css_files", $params, $where_params, true);
		} else {
			// インサート
			$params=array(
						"dir_name" => $dir_name,
						"type" => $type,
						"block_id" => 0,
						"data" => $contents,
						"system_flag" => $system_flag,
						"common_general_flag" => $common_general_flag,
						"common_admin_flag" => $common_admin_flag
					);
			$result = $this->_db->insertExecute("css_files", $params, true);
		}
		return $result;
	}
	
	function _regist($filepath, $dir_name, $system_flag=_OFF) {
		$common_admin_flag = _OFF;
		$common_general_flag = _OFF;
		$read_order = MODULE_READ_ORDER_MODULE;
		$filename = basename($filepath);
		switch($dir_name) {
			case "common":
				$common_admin_flag = _ON;
				$common_general_flag = _ON;
				$debug_arr = explode("|", MODULE_COMMON_DEBUG_JS);
				$common_arr =  explode("|", MODULE_COMMON_COMMON_JS);
				
				if(in_array($filename, $debug_arr)) {
					$dir_name = "debug";
					$read_order = MODULE_READ_ORDER_DEBUG;
				} else if(in_array($filename, $common_arr)) {
					$read_order = MODULE_READ_ORDER_COMMON;
				}
				break;
			case "comp":
				// pluginsまたはextensionフォルダの場合、読み込みを行わない（動的に読み込む）。
				if( preg_match("/".preg_quote("/plugins/".$filename, '/')."$/i", $filepath) 
					|| preg_match("/".preg_quote("/extension/".$filename, '/')."$/i", $filepath) ) {
					$common_admin_flag = _OFF;
					$common_general_flag = _OFF;
				} else {
					$common_admin_flag = _ON;
					$common_general_flag = _ON;
				}

				$compcommon_arr =  explode("|", MODULE_COMMON_COMPCOMMON_JS);
				if(in_array($filename, $compcommon_arr)) {
					$dir_name = "comp_common";
					$read_order = MODULE_READ_ORDER_COMPCOMMON;
				} else {
					$dir_name = substr($filename, 0,strlen($filename)-3);
					$read_order = MODULE_READ_ORDER_COMP;
				}
				break;
			case "pages":
				$common_general_flag = _ON;
				$read_order = MODULE_READ_ORDER_PAGE;
				break;
			case "control":
				$common_admin_flag = _ON;
				$read_order = MODULE_READ_ORDER_PAGE;
				break;
			case "dialog":
				$common_general_flag = _ON;
				$common_admin_flag = _ON;
				break;
			case "login":
			case "userinf":
				$common_admin_flag = _ON;
				$common_general_flag = _ON;
				break;
					
		}
		
		$contents = $this->_fileCompressor($filepath, false);
		
		$result = $this->_db->selectExecute("javascript_files", array("dir_name" => $dir_name), null, 1);
		if($result === false) {
			return false;	
		}
		// jsファイルが複数ファイルに分かれている場合は、効果なし。
		if(isset($result[0]) && $contents == $result[0]['data']) {
			// jsファイル変更なし
			return true;	
		}
		
		if(isset($result[0])) {
			// アップデート	
			if($filename == "prototype.js") {
				$data =	$contents."\n". $result[0]['data'];
			} else {
				$data = $result[0]['data']."\n".$contents;
			}
			$params=array(
						"data" => $data,
						"read_order" => $read_order,
						"system_flag" => $system_flag,
						"common_general_flag" => $common_general_flag,
						"common_admin_flag" => $common_admin_flag
					);
			$where_params=array("dir_name" => $dir_name);
			$result = $this->_db->updateExecute("javascript_files", $params, $where_params, true);
		} else {
			// インサート
			$params=array(
						"dir_name" => $dir_name,
						"data" => $contents,
						"read_order" => $read_order,
						"system_flag" => $system_flag,
						"common_general_flag" => $common_general_flag,
						"common_admin_flag" => $common_admin_flag
					);
			$result = $this->_db->insertExecute("javascript_files", $params, true);
		}
		return $result;
	}
	/**
	 * jsファイル,CSSファイル縮小化処理
	 * @param  string    $filepath
	 * @return string    $contents
	 * @access	public
	 */
	function _fileCompressor($filepath, $relative_core_base_url=false) {
		$handle = fopen($filepath, "r");
		if(!$handle) {
			return false;
		}
		$filesize = filesize($filepath);
		if ($filesize <= 0) {
			return true;
		}
		$contents = fread($handle, $filesize);
		fclose($handle);
		
		//コメント除去
		$pattern = array("/<\{[$]smarty\.const\.CORE_BASE_URL\}>/si", "/^\s+/s", "/\n\s+/s", "/^(\/\/).*?(?=\n)/s", "/\n\/\/.*?(?=\n)/s", "/\s+(\/\/).*?(?=\n)/s", "/^\/\*(.*?)\*\//s", "/\n\/\*(.*)\*\//Us","/\s+(?=\n)/s");
		//$replacement = array (CORE_BASE_URL, "","\n","","\n","","","","");
		if (!$relative_core_base_url) {
			$replacement = array (CORE_BASE_URL, "","\n","","\n","","","","");
		} else {
			$replacement = array (".", "","\n","","\n","","","","");
		}
		$contents = preg_replace($pattern, $replacement, $contents);
		return $contents;
	}
	
	/**
	 * jsファイル削除処理
	 * @param  string    $dir_name
	 * @return boolean
	 * @access	public
	 */
	function deleteJsFiles($dir_name) {
		$result = $this->_db->deleteExecute("javascript_files", array("dir_name" => $dir_name));
		if($result === false) return false;
		
		$result = $this->_db->deleteExecute("css_files", array("dir_name LIKE '/".$dir_name."/%'" => null));
		if($result === false) return false;
		
		return $result;
	}
}
?>