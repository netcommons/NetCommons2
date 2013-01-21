<?php
/**
 *  インストールコモンコンポーネント
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Components_Compmain {
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	var $_session = null;
	var $_actionChain = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Install_Components_Compmain() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_session =& $this->_container->getComponent("Session");
		$this->_actionChain =& $this->_container->getComponent("ActionChain");
	}

	/**
	 * ヘッダーTitleタグ設定
	 *
	 * @access	public
	 */
	function setTitle() {
		$action_name = $this->_actionChain->getCurActionName();
		$this->_session->setParameter("_site_name", INSTALL_SITE_TITLE);
		switch($action_name) {
			case "install_view_main_init":
				$this->_session->setParameter("_page_title", INSTALL_WELCOME_TITLE);
				break;
			case "install_view_main_intro":
				$this->_session->setParameter("_page_title", INSTALL_INTRO_TITLE);
				break;
			case "install_view_main_general":
				$this->_session->setParameter("_page_title", INSTALL_GENERAL_CONF_TITLE);
				break;
			case "install_view_main_confirm":
				$this->_session->setParameter("_page_title", INSTALL_GENERAL_CONF_CONFIRM_TITLE);
				break;
			case "install_view_main_permission":
				$this->_session->setParameter("_page_title", INSTALL_PERMISSION_CONFIG_TITLE);
				break;
			case "install_view_main_urlcheck":
				$this->_session->setParameter("_page_title", INSTALL_PATH_URL_CHECK_TITLE);
				break;
			case "install_view_main_dbconfirm":
				$this->_session->setParameter("_page_title", INSTALL_DATABASE_CONFIRM_TITLE);
				break;
			case "install_view_main_dbcheck":
				$this->_session->setParameter("_page_title", INSTALL_DATABASE_CHECK_TITLE);
				break;
			case "install_view_main_dbcreate":
				$this->_session->setParameter("_page_title", INSTALL_DATABASE_CREATE_TITLE);
				break;
			case "install_view_main_tbcreate":
				$this->_session->setParameter("_page_title", INSTALL_TABLE_CREATE_TITLE);
				break;
			case "install_action_main_saveini":
				$this->_session->setParameter("_page_title", INSTALL_SAVE_SETTING_TITLE);
				break;
			case "install_view_mdinstall_init":
				$this->_session->setParameter("_page_title", INSTALL_MODULE_INSTALL_TITLE);
				break;
			case "install_view_main_adminsetting":
				$this->_session->setParameter("_page_title", INSTALL_ADMIN_SETTING_TITLE);
				break;
			case "install_action_main_insertdata":
				$this->_session->setParameter("_page_title", INSTALL_INSERT_DATA_TITLE);
				break;
			case "install_view_complete":
				$this->_session->setParameter("_page_title", INSTALL_COMPLETE_TITLE);
				break;
		}
	}
	/**
	 * install/config/define.inc.phpから初期値取得
	 *
	 * @access	public
	 */
	function getConfigDef($key) {
		switch($key) {
    		case "database":
    			// データベースの種類
    			if(defined("INSTALL_DATABASE")) {
    				return 	INSTALL_DATABASE;
    			}
    			break;
    		case "dbusername":
    			// データベース-ID名
    			if(defined("INSTALL_DEFAULT_DBUSERNAME")) {
    				return 	INSTALL_DEFAULT_DBUSERNAME;
    			}
    			break;
    		case "dbpass":
    			// データベース-パスワード
    			if(defined("INSTALL_DEFAULT_DBPASS")) {
    				return 	INSTALL_DEFAULT_DBPASS;
    			}
    			break;
    		case "dbhost":
    			// データベース-ホスト名
    			if(defined("INSTALL_DEFAULT_DBHOST")) {
    				return 	INSTALL_DEFAULT_DBHOST;
    			}
    			break;
    		case "dbname":
    			// データベース名
    			if(defined("INSTALL_DEFAULT_DBNAME")) {
    				return 	INSTALL_DEFAULT_DBNAME;
    			}
    			break;
    		case "dbprefix":
    			// テーブル接頭語
    			if(defined("INSTALL_DEFAULT_DATABASE_PREFIX")) {
    				return 	INSTALL_DEFAULT_DATABASE_PREFIX;
    			}
    			break;
    		case "dbpersist":
    			// 持続的接続
    			if(defined("INSTALL_DEFAULT_DATABASE_PERSIST")) {
    				return 	INSTALL_DEFAULT_DATABASE_PERSIST;
    			}
    			break;
    		case "core_base_url":
    		case "base_url":
    			// ベースURL
    			$filepath = (! empty($_SERVER['REQUEST_URI']))
                            ? dirname($_SERVER['REQUEST_URI'])
                            : dirname($_SERVER['SCRIPT_NAME']);

	            //$filepath = str_replace("\\", "/", $filepath); // "
	            //$filepath = transPathSeparator($filepath);

	            $filepath = str_replace("/install", "", $filepath);
	            if ( substr($filepath, 0, 1) == "/" ) {
	                $filepath = substr($filepath,1);
	            }
	            if ( substr($filepath, -1) == "/" ) {
	                $filepath = substr($filepath, 0, -1);
	            }
	            $protocol = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://';
	            return (!empty($filepath)) ? $protocol.$_SERVER['HTTP_HOST']."/".$filepath : $protocol.$_SERVER['HTTP_HOST'];
	        case "base_dir":
	        	$base_dir = dirname(START_INDEX_DIR);
	        	//$base_dir = transPathSeparator($base_dir);
	        	//$base_dir = str_replace("\\","/",$base_dir);
	        	//$base_dir = str_replace("\\","/",getcwd()); // "
            	//$base_dir = str_replace("/install", "", $base_dir);
            	return $base_dir;
            case "htdocs_dir":
            	$htdocs_dir = START_INDEX_DIR;
            	//$htdocs_dir = str_replace("\\","/",$htdocs_dir);
            	//$htdocs_dir = transPathSeparator($htdocs_dir);
            	return $htdocs_dir;
            case "style_dir":
            	$base_dir = dirname(START_INDEX_DIR);
	        	//$base_dir = str_replace("\\","/",$base_dir);
	        	//$base_dir = transPathSeparator($base_dir);
            	return $base_dir . '/webapp/style';
            case "fileuploads_dir":
            	$base_dir = dirname(START_INDEX_DIR);
	        	//$base_dir = str_replace("\\","/",$base_dir);
	        	//$base_dir = transPathSeparator($base_dir);
            	return $base_dir . '/webapp/uploads';
    	}
    	return "";
	}
	/**
	 * データベース関連情報をセッションより取得
	 * @return boolean
	 * @access	public
	 */
	function getSessionDb(&$database, &$dbhost, &$dbusername, &$dbpass, &$dbname, &$dbprefix, &$dbpersist, &$dsn) {
		$dbprefix = $this->_session->getParameter("dbprefix");
    	if($dbprefix != "") {
    		$dbprefix .= "_";		// 最後に「_」をつける
    	}

    	$database = $this->_session->getParameter("database");
		$dbhost = $this->_session->getParameter("dbhost");
		$dbusername = $this->_session->getParameter("dbusername");
		$dbpass = $this->_session->getParameter("dbpass");
		$dbname = $this->_session->getParameter("dbname");
    	$dbprefix = $this->_session->getParameter("dbprefix");
    	if($dbprefix != "") {
    		$dbprefix .= "_";		// 最後に「_」をつける
    	}
    	$dbpersist = $this->_session->getParameter("dbpersist");
    	$dsn = rawurlencode($database)."://".rawurlencode($dbusername).":".rawurlencode($dbpass)."@".rawurlencode($dbhost)."/".rawurlencode($dbname);
		if(!isset($database) || $database == "") {
    		return false;
    	}
    	return true;
	}

	/**
	 * SQLファイル実行
	 * param string $file_name
	 * param string $type
	 *
	 * @access	public
	 */
	function executeSqlFile($file_name, $type="default") {
		$res_arr = array();
		$result = false;
		$_lang = $this->_session->getParameter("_lang");
		$base_dir = $this->_session->getParameter("base_dir");
		$sitename = addslashes($this->_session->getParameter("sitename"));
		$modulesView =& $this->_container->getComponent("modulesView");


		//$modules[$dir_name], $self_site_id, $private_room_name, $admin_user_id
		$self_site_id = $this->_session->getParameter("install_self_site_id");
		$admin_user_id = $this->_session->getParameter("install_user_id");
		$admin_login_id = $this->_session->getParameter("install_login_id");
		$admin_handle = addslashes($this->_session->getParameter("install_handle"));
		$permalink = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $admin_handle);

		$this->getSessionDb($database, $dbhost, $dbusername, $dbpass, $dbname, $dbprefix, $dbpersist, $dsn);

    	//
		// DB接続
		//
    	//include_once $base_dir.'/maple/nccore/db/DbObjectAdodb.class.php';
    	include_once BASE_DIR.'/maple/nccore/db/DbObjectAdodb.class.php';


    	$dbObject = new DbObjectAdodb();
    	$dbObject->setPrefix($dbprefix);
    	$dbObject->setDsn($dsn);
    	$conn_result = @$dbObject->connect();

		if ($conn_result == false) {
			// DB接続失敗
			$res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_DBCHECK_NOT_CONNECT, $dbname);
			return array($result, $res_arr);
		}

		// $modules[$dir_name]
		$modules = $dbObject->execute("SELECT {modules}.* FROM {modules}", array(), null, null, true, array($this, "_fetchcallbackModules"), array($modulesView));
		if ($modules === false) {
			// とりあえずDB接続失敗のエラーメッセージとする
			$res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_DBCHECK_NOT_CONNECT, $dbname);
			return array($result, $res_arr);
		}

		$databaseSqlutility =& $this->_container->getComponent("databaseSqlutility");

		$config_db_kind = $database;
		$lang_file_path = $base_dir.'/webapp/modules/install/language/'.$_lang.'/'.$file_name;
		if(!@file_exists($lang_file_path)) {
			$_lang = "english";
			$lang_file_path = $base_dir.'/webapp/modules/install/language/'.$_lang.'/'.$file_name;
		}

		include_once $lang_file_path;

		if(defined(strtoupper("INSTALL_CONF_ADD_PRIVATE_SPACE_NAME_".$_lang))) {
			$private_room_name = str_replace("{X-HANDLE}", $admin_handle, constant(strtoupper('INSTALL_CONF_ADD_PRIVATE_SPACE_NAME_'.$_lang)));
			$this->_session->setParameter("install_private_room_name", $private_room_name);
		} else {
			$private_room_name = $this->_session->getParameter("install_private_room_name");
		}

		$file_path = $base_dir.'/webapp/modules/install/sql/'.$database.'/'.$type."/".$file_name;
		if(!@file_exists($file_path) && $database == "mysqli") {
			$database = "mysql";
			$file_path = $base_dir.'/webapp/modules/install/sql/'.$database.'/'.$type."/".$file_name;
		}

		$data = "";
		include_once $file_path;
		if($file_name == INSTALL_CONFIG_DATA_FILENAME) {
			$config_lang_sql = '';
			$languages = array('japanese', 'english', 'chinese');
			$items = explode(',', _MULTI_LANG_CONFIG_ITEMS);
			foreach($languages as $lang_dirname) {
				include_once $base_dir.'/webapp/modules/install/language/'.$lang_dirname.'/'.$file_name;
				foreach($items as $item) {
					if($item == 'sitename') {
						$conf_value = $sitename;
					}else if($item == 'from') {
						$conf_value = 'netcommons';
					}else {
						$conf_value = constant(strtoupper('INSTALL_CONF_'.$item.'_'.$lang_dirname));
					}
					$config_lang_sql .= "INSERT INTO `config_language` (`conf_name`, `lang_dirname`, `conf_value`) VALUES ('".$item."', '".$lang_dirname."', '".$conf_value."');";
				}
			}
			$data .= $config_lang_sql;
		}
		$databaseSqlutility->splitMySqlFile($pieces, $data);
		$result = true;
		$config_success_count = 0;
		$config_failed_count = 0;
		$adodb =& $dbObject->getAdoDbObject();
		$savetable_name = "";
		foreach ($pieces as $piece) {
			// SQLユーティリティクラスにてテーブル名にプレフィックスをつける
			// 配列としてリターンされ、
            // 	[0] プレフィックスをつけたクエリ
            // 	[4] プレフィックスをつけないテーブル名
			// が格納されている
			$prefixed_query = $databaseSqlutility->prefixQuery($piece, $dbObject->getPrefix());
			//$prefixed_query_result = true;
			if ( !$prefixed_query ) {
				//$prefixed_query_result = false;
				$result = false;
				$res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_INSERT_DATA_FAILED_INSERT, 1, "Unknown Table");
				continue;
			}

			if($savetable_name != $prefixed_query[4]) {
				// テーブルが変わった
				if($savetable_name != "") {
					// メッセージ
					if($config_success_count > 0) {
						// 成功
						$res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_INSERT_DATA_SUCCESS_INSERT, $config_success_count, $savetable_name);
					}
					if($config_failed_count > 0) {
						$result = false;
						$res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_INSERT_DATA_FAILED_INSERT, $config_failed_count, $savetable_name);
					}
					$config_success_count = 0;
					$config_failed_count = 0;
				}
				$savetable_name = $prefixed_query[4];
			}

			// 実行
			//$dbObject->executeはprefixの変換処理があるため使用しない
			if ( !$adodb->Execute($prefixed_query[0]) ) {
				$config_failed_count++;
				$result = false;
				//continue;
			} else {
				// 成功
				$config_success_count++;
			}
		}
		if($savetable_name != "") {
			// メッセージ
			if($config_success_count > 0) {
				// 成功
				$res_arr[] = INSTALL_IMG_YES . sprintf(INSTALL_INSERT_DATA_SUCCESS_INSERT, $config_success_count, $savetable_name);
			}
			if($config_failed_count > 0) {
				$result = false;
				$res_arr[] = INSTALL_IMG_NO . sprintf(INSTALL_INSERT_DATA_FAILED_INSERT, $config_failed_count, $savetable_name);
			}
			//$config_success_count = 0;
			//$config_failed_count = 0;
		}
		return array($result, $res_arr);
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
			$row["module_name"] = $func_param->loadModuleName($pathList[0]);
			$ret[$pathList[0]] = $row;
		}
		return $ret;
	}

	/**
	 * パス変換処理
	 * @param string $path
	 * @return array
	 * @access	public
	 */
	function transPath($path) {
		if ( DIRECTORY_SEPARATOR != '/' ) {
	 		// IIS6 doubles the \ chars
			$path = str_replace( strpos( $path, '\\\\', 2 ) ? '\\\\' : DIRECTORY_SEPARATOR, '/', $path);
		}
		return $path;
	}
}
?>