<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
include_once MAPLE_DIR.'/includes/pear/File/Archive.php';
include_once MAPLE_DIR.'/includes/pear/XML/Unserializer.php';

/**
 * XML->配列コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Components_Restore
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	var $_actionChain = null;
	var $_fileAction = null;
	var $_pagesView = null;
	var $_configView = null;
	var $_modulesView = null;
	var $_databaseSqlutility = null;
	var $_requestMain = null;
	var $encryption = null;

	var $tablelist = array();
	var $tablePrimarylist = array();
	var $restorelist = array();

	var $data = array();
	var $restore_data = array();

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Backup_Components_Restore()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_actionChain =& $this->_container->getComponent("ActionChain");
		$commonMain =& $this->_container->getComponent("commonMain");
		$this->_fileAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/Action.class.php', "File_Action", "fileAction");
        $this->_pagesView =& $this->_container->getComponent("pagesView");
		$this->_configView =& $this->_container->getComponent("configView");
		$this->_modulesView =& $this->_container->getComponent("modulesView");
		$this->_databaseSqlutility =& $this->_container->getComponent("databaseSqlutility");
		$this->_requestMain =& $this->_container->getComponent("requestMain");
	}
	/**
     * バックアップXMLファイル->リストア配列変換処理
     *
     * @access  public
     */
	function getRestoreArray($upload_id, $backup_page_id, $module_id, $temporary_file_path) {
		set_time_limit(BACKUP_TIME_LIMIT);
		// メモリ最大サイズ設定
		ini_set('memory_limit', -1);

    	$errorList =& $this->_actionChain->getCurErrorList();

    	//if($backup_page_id == 0 || $upload_id == 0) {
    	//	// フルバックアップをリストアしようとしている
    	//	$errorList->add("backup", BACKUP_FAILURE_RESTORE);
		//	return false;
    	//}

    	//$uploads = $this->_db->selectExecute("uploads", array("upload_id" => $upload_id, "room_id" => $backup_page_id, "module_id" => $module_id));
		$uploads = $this->_db->selectExecute("uploads", array("upload_id" => $upload_id, "module_id" => $module_id));
		if($uploads === false || !isset($uploads[0])  || count($uploads) > 1) return false;

		$uploads_file_path = FILEUPLOADS_DIR."backup/".$uploads[0]['physical_file_name'];
		if(!file_exists($uploads_file_path)) {
			// バックアップファイルなし
			$errorList->add("backup", BACKUP_NONE_RESTORE);
			return false;
		}

		if(file_exists($temporary_file_path)) {
			// 現在、同じバックアップファイル-リストア中
			// エラーとする
			$errorList->add("backup", BACKUP_RESTORING);
			return false;
		}
		if(!mkdir($temporary_file_path, 0777)) {
			$errorList->add("backup", BACKUP_RESTORE_ERROR);
			return false;
		}
		//
		// 解凍
		//
		File_Archive::extract(
			File_Archive::read($uploads_file_path . "/"),
			File_Archive::appender($temporary_file_path)
		);

		if(!file_exists($temporary_file_path.BACKUP_ROOM_XML_FILE_NAME)) {
			// XMLファイルなし
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_FAILURE_RESTORE);
			return false;
		}

		// PHP 4 > 4.3.0, PHP 5
		$xml = file_get_contents($temporary_file_path.BACKUP_ROOM_XML_FILE_NAME);
		if($xml === false) {
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_RESTORE_ERROR);
			return false;
		}


		if(!file_exists($temporary_file_path.BACKUP_ROOM_XML_INI_NAME)) {
			// XML INIファイルなし
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_FAILURE_RESTORE);
			return false;
		}
		$xml_ini = file_get_contents($temporary_file_path.BACKUP_ROOM_XML_INI_NAME);
		if($xml_ini === false) {
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_RESTORE_ERROR);
			return false;
		}

		$unserializer =& new XML_Unserializer();
		//
		// 複合化チェック
		//
		$result = $unserializer->unserialize($xml_ini);
		if($result !== true) {
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_FAILURE_UNSERIALIZE);
			return false;
		}
		$data_ini = $unserializer->getUnserializedData();

		// 共有設定されているかどうか
		if($data_ini["host_field"] == BASE_URL.INDEX_FILE_NAME) {
			// 自サイトならば、無条件でOK
			$self_flag = true;
		} else {
			$self_flag = false;
			// サイトテーブルにあり、commons_flagが立っているものを許す
			$data_ini["host_field"] = preg_replace("/".preg_quote(INDEX_FILE_NAME, "/")."$/i", "", $data_ini["host_field"]);

			$where_params = array(
				"url" => $data_ini["host_field"],
				"commons_flag" => _ON
			);
			// 有効期限がいついつの公開鍵を取得
			$other_site = $this->_db->selectExecute("sites", $where_params, null, 1);
			if ($other_site === false) {
		       	$this->_fileAction->delDir($temporary_file_path);
				$errorList->add("backup", BACKUP_RESTORE_ERROR);
				return false;
			}
			if(!isset($other_site[0])) {
				// 共有設定されていない
				$this->_fileAction->delDir($temporary_file_path);
				$errorList->add("backup", BACKUP_RESTORE_COMMONS_ERROR);
				return false;
			}
		}

		$get_public_url = $data_ini["host_field"].INDEX_FILE_NAME."?action=encryption_view_publickey".
							"&encrypt_data=".rawurlencode($data_ini['encrypt_text']).
							"&expiration_time=".$data_ini['expiration_time']."&_header="._OFF;

		$public_key_html = $this->_requestMain->getResponseHtml($get_public_url);
		if($public_key_html === false) {
			// 公開鍵取得失敗
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_RESTORE_PUBLIC_KEY_ERROR);
			return false;
		}
		if(is_string($public_key_html)) {
			$public_key_html = trim($public_key_html);
			$public_key = preg_replace("/^public_key=/i", "", $public_key_html);
		}
		if(isset($public_key) && $public_key_html == $public_key) {
			// 公開鍵取得失敗
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_RESTORE_PUBLIC_KEY_ERROR);
			return false;
		}

		// 複合化
		//$this->encryption =& $this->_container->getComponent("encryptionView");
		$decrypt_text = $this->encryption->decrypt($data_ini['encrypt_text'], $public_key);
		$hash_text = sha1($xml);
		if($decrypt_text != $hash_text) {
			// 不正なファイルの可能性あり
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_FAILURE_RESTORE);
			return false;
		}

		//$options = array ('parseAttributes' => true);	属性値を含める
		//$unserializer =& new XML_Unserializer($options);

		$result = $unserializer->unserialize($xml);
		if($result !== true) {
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_FAILURE_UNSERIALIZE);
			return false;
		}
		$data = $unserializer->getUnserializedData();


		//-----------------------------------------------------------------------------------
		// NetCommonsバージョンチェック
		// 上3桁目がいっしょならば、リストアを許す
		//-----------------------------------------------------------------------------------
		if(!isset($data["version"]) || !isset($data["version"]["_version"]) ||
			 !isset($data["_room_inf"])) {
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_FAILURE_RESTORE);
			return false;
		}
		$current_version = $data["version"]["_version"];
		$current_version_arr = explode(".", $current_version);

		$now_version_arr = explode(".", _NC_VERSION);

		if(count($current_version_arr) != 4 || count($now_version_arr) != 4) {
			// バージョン情報が不正
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", BACKUP_WRONG_VERSION);
			return false;
		}
		if($now_version_arr[0] != $current_version_arr[0] ||
			$now_version_arr[1] != $current_version_arr[1] ||
			$now_version_arr[2] != $current_version_arr[2]
			) {
			// バージョン情報が一致しない
			$this->_fileAction->delDir($temporary_file_path);
			$errorList->add("backup", sprintf(BACKUP_DIFFER_VERSION, $current_version, _NC_VERSION));
			return false;
		}
		//-----------------------------------------------------------------------------------
		// リストア対象ルーム情報取得-チェック
		// DB項目が変わることもありえるので、すべての項目はチェックしない
		// 「バックアップファイル追加」時にチェックすればいい
		//-----------------------------------------------------------------------------------
		if(!isset($data["_room_inf"]["row"]) || !isset($data["_room_inf"]["row"]["page_id"]) ||
			 !isset($data["_room_inf"]["row"]["room_id"]) || !isset($data["_room_inf"]["row"]["private_flag"]) ||
			 !isset($data["_room_inf"]["row"]["space_type"]) || !isset($data["_room_inf"]["row"]["site_id"]) ||
			 !isset($data["_room_inf"]["row"]["thread_num"]) || !isset($data["_room_inf"]["row"]["parent_id"]) ||
			 $data["_room_inf"]["row"]["room_id"] != $data["_room_inf"]["row"]["page_id"]
			 ) {

			 	$this->_fileAction->delDir($temporary_file_path);
				$errorList->add("backup", BACKUP_WRONG_ROOMINF);
				return false;
		}

		$room_inf = $data["_room_inf"]["row"];
		if(!$self_flag) {
			$room_inf['site_id'] = $other_site[0]['site_id'];
			$room_inf['url'] = $data_ini["host_field"];
			if($room_inf['thread_num'] == 2) {
				$room_inf['parent_id'] = 0;
			}
			$room_inf['room_id'] = 0;
		} else {
			$room_inf['url'] = '';
		}
		$room_inf['pre_page_name'] = $room_inf['page_name'];

		// スペースタイプがパブリックスペース or プライベートスペースならば上書き
		// それ以外ならば、追加登録
		if($room_inf['thread_num'] == 0 &&
			($room_inf['private_flag'] == _ON || $room_inf['space_type'] == _SPACE_TYPE_PUBLIC)) {
			// パブリックスペース or プライベートスペース
			// 参加者や、その他、親カラムの情報は基本的にリストアしない（選択させる？）
			$restore_type = "top";
		} else {
			// グループルーム or サブグループ（新規ルームとしてリストア）
			if($room_inf['thread_num'] == 2) {
				$restore_type = "subgroup";
			} else {
				if($room_inf['space_type'] == _SPACE_TYPE_PUBLIC) {
					$restore_type = "public_room";
				} else {
					$restore_type = "group_room";
				}
			}
			// 同名のルームが存在したら、リネームする
			$first_room_name = $room_inf['page_name'];
			$rename_count = 1;
			while(1) {
				$chk_pages =& $this->_pagesView->getPages(array("thread_num"=>$room_inf['thread_num'], "space_type"=>$room_inf['space_type'], "private_flag"=>$room_inf['private_flag'], "page_id=room_id"=>null, "page_name"=> $room_inf['page_name']));
				if($chk_pages === false) {
					$this->_fileAction->delDir($temporary_file_path);
		    		return false;
		    	}
		    	if(isset($chk_pages[0])) {
		    		// 同名のルームあり
		    		$room_inf['page_name'] = sprintf(BACKUP_RESTORE_PREFIX_PAGE_NAME,$rename_count).$first_room_name;
		    		$rename_count++;
		    	} else {
		    		break;
		    	}
			}
		}
		if(($restore_type == "public_room" || $restore_type == "top") && $room_inf['private_flag'] == _OFF) {
			// パブリックスペースであればサイト閉鎖中かどうかを取得(警告メッセージを表示するため)
			// サイト閉鎖中かどうか
			$closesite = $this->_configView->getConfigByConfname(_SYS_CONF_MODID, "closesite");
    		if($closesite === false) {
    			$this->_fileAction->delDir($temporary_file_path);
    			return false;
    		}
			$closesite = $closesite['conf_value'];
		}

		//-----------------------------------------------------------------------------------
		// 各モジュールバージョンチェック
		// 上3桁目がいっしょならば、リストアを許す
		// インストールされていないモジュールはリストアしない
		//-----------------------------------------------------------------------------------
		$modules = $this->_modulesView->getModules(array("system_flag" => _OFF), null, null, null, array($this, "_fetchcallbackModules"), $this->_modulesView);
		if($modules === false) {
			$this->_fileAction->delDir($temporary_file_path);
			return false;
		}
		$adodb = $this->_db->getAdoDbObject();

		foreach($modules as $dirname => $module) {
			$buf_tablelist = $this->_databaseSqlutility->getTableList($dirname, false);
			if(is_array($buf_tablelist) && count($buf_tablelist) > 0) {
				foreach($buf_tablelist as $table) {
					// テーブルリスト取得
					$this->tablePrimarylist[$dirname][$table] = $adodb->MetaPrimaryKeys($this->_db->getPrefix().$table);
					$this->tablelist[$table] = $dirname;
				}
			}
			unset($buf_tablelist);
		}
		$restore_modules = array();
		$restore_modules["system"]['restore_type'] = $restore_type;
		$restore_modules["system"]['self_flag'] = $self_flag;
		foreach($data["version"] as $module_name => $module_version) {
			//if($module_name == "_version") {
			//	continue;
			//}
			if(preg_match("/^_/i" , $module_name)) {
				// モジュール名称
				continue;
			}
			$restore_modules[$module_name] = array();
			$restore_modules[$module_name]['state'] = false;
			if(isset($modules[$module_name])) {
				$restore_modules[$module_name]['module_id'] = $modules[$module_name]['module_id'];
				$restore_modules[$module_name]['module_name'] = $modules[$module_name]['module_name'];

				$module_version_arr = explode(".", $module_version);
				$now_module_version_arr = explode(".", $modules[$module_name]['version']);

				if(count($current_version_arr) != 4 || count($now_version_arr) != 4) {
					// モジュールのバージョンの書き方が不正（リストア対象からはずす）
					$restore_modules[$module_name]['error_mes'] = BACKUP_WRONG_MODULE_VERSION;
					continue;
				} else if($now_module_version_arr[0] != $module_version_arr[0] ||
					$now_module_version_arr[1] != $module_version_arr[1] ||
					$now_module_version_arr[2] != $module_version_arr[2]
					) {
					// バージョン情報が一致しない
					$restore_modules[$module_name]['error_mes'] = BACKUP_DIFFER_MODULE_VERSION;
					continue;
				}
				$this->restorelist[$module_name] = true;
				//$buf_tablelist = $this->_databaseSqlutility->getTableList($module_name, false);
				//if(is_array($buf_tablelist) && count($buf_tablelist) > 0) {
				//	foreach($buf_tablelist as $table) {
				//		// テーブルリスト取得
				//		$this->tablelist[$table] = $module_name;
				//	}
				//}
			} else {

				// 未インストールモジュール
				if(isset($data["version"]["_".$module_name])) {
					$restore_modules[$module_name]['module_name'] = $data["version"]["_".$module_name];
				} else {
					$restore_modules[$module_name]['module_name'] = $module_name;
				}
				$restore_modules[$module_name]['error_mes'] = BACKUP_UNINSTALL_MODULE;
				continue;
			}
			if($modules[$module_name]['restore_action'] == "auto") {
				//
				// install.iniチェック
				// [Restore]
				// _transfer_id="bbs_id,post_id,topic_id,parent_id,room_id,block_id"
				//						     -> IDを振り替えるリスト($this->db->nextSeq($table_name))
				// topic_id = post_id	     -> post_idを振り替えた値をtopic_idに入れるという意味（topic_idでnextSeqしない）
				// parent_id = post_id　     -> 上記と同様
				// room_id = core.room_id  　-> coreのテーブルのroom_idがはいる
				// block_id = core.block_id  -> coreのテーブルのblock_idがはいる
				$file_path = MODULE_DIR."/".$module_name.'/install.ini';
				if (file_exists($file_path)) {
					if(version_compare(phpversion(), "5.0.0", ">=")){
			        	$initializer =& DIContainerInitializerLocal::getInstance();
			        	$install_ini = $initializer->read_ini_file($file_path, true);
			        } else {
			 	        $install_ini = parse_ini_file($file_path, true);
			        }
			        if(isset($install_ini['Restore']) && is_array($install_ini['Restore'])) {
			        	$restore_modules[$module_name]['transfer_list'] = $install_ini['Restore'];
			        } else {
			        	// リストア対象外モジュール
			        	$restore_modules[$module_name]['error_mes'] = BACKUP_UNSUPPORTED_MODULE;
			        }
			        //if(isset($install_ini['CleanUp']) && is_array($install_ini['CleanUp'])) {
			        //	// uploadsテーブルのIDを振り替えるため、その振替元のカラムのupload_idの項目を更新しなければならない
			        //	// $install_ini['Restore']にそれを記述すると煩雑になるため、現状、CleanUpの項目を用いる
			        //	$restore_modules[$module_name]['transfer_uploads'] = $install_ini['CleanUp'];
			        //}
				}
			} else if($modules[$module_name]['restore_action'] == "") {
				// リストア対象外モジュール
			    $restore_modules[$module_name]['error_mes'] = BACKUP_UNSUPPORTED_MODULE;
			} else {
				// リストアアクションを呼ぶのみ
				$restore_modules[$module_name]['transfer_list'] =$modules[$module_name]['restore_action'];
			}
			$restore_modules[$module_name]['state'] = true;
		}

		// room毎に分割
		$this->data =& $data;

		//$this->_fileAction->delDir($temporary_file_path);
		return array($room_inf, $restore_modules, $data["version"], $modules);
	}

	/**
     * ルーム配列情報を返す
     * getRestoreArray後に通す
     *
     * @access  public
     */
	function &getRoomArray() {
		if(!is_array($this->data)) {
			return false;
		}
		$this->restore_data = array();
		foreach($this->data as $rooms_key => $room) {
			if($rooms_key == "_room_inf" || $rooms_key == "version") {
				continue;
			}
			// ルームID取得
			$room_id = intval(preg_replace ( "/^room/i" , "" , $rooms_key ));
			if($room_id != 0) {
				$this->_setColumnData($room, $room_id);
			}
		}
		return $this->restore_data;
	}

    /**
	 * リストア配列の分解
	 * @param array $data_arr
	 * @access	private
	 *
	 * [key]
	 * 		[sub_key]
	 * 		[sub_key2]
	 * 		･･･
	 *
	 * 	or
	 *
	 * [key]
	 * 		[0]
	 * 			[sub_key]
	 * 			[sub_key2]
	 * 			･･･
	 * 		[1]
	 * 			[sub_key]
	 * 			[sub_key2]
	 * 			･･･
	 */
    function _setColumnData(&$data_arr, $room_id) {
    	foreach($data_arr as $data_key => $data) {
			if(!is_int($data_key)) {
				// １つのデータのリストア
				$this->_setBreakdown($data_key, $data, $room_id);
			} else {
				// 複数データのリストア
				$this->_setColumnData($data_arr[$data_key], $room_id);
			}
		}
    }

    /**
	 * テーブルカラム分解
	 * @param string $table_name
	 * @param array $data['primary_key']
	 * 					  ['row']
	 * @return array
	 * @access	private
	 */
    function _setBreakdown($table_name, &$data, $room_id) {
    	if(isset($data['row'][0])) {
    		foreach($data['row'] as $rc) {
    			$this->_setRestoreModules($table_name, $rc, $room_id);
    		}
    	} else if(isset($data['row'])) {
    		$this->_setRestoreModules($table_name, $data['row'], $room_id);
    	} else {
    		// ここにははいってこないかも
    		$this->_setRestoreModules($table_name, $data, $room_id);
    	}
    }

    function _setRestoreModules($table_name, &$data, $room_id) {
    	$system_table_list = array("pages", "pages_style", "pages_modules_link", "pages_users_link", "monthly_number", "blocks", "uploads", "pages_meta_inf");

    	if(in_array($table_name, $system_table_list)) {
    		// System
    		$this->restore_data["system"]['room'][$room_id][$table_name][] = $data;
    	} else {
    		// 一般モジュール
    		//if(isset($this->tablelist[$table_name])) {
    		//	$this->restore_data[$this->tablelist[$table_name]]['room'][$room_id][$table_name][] = $data;
    		if(isset($this->tablelist[$table_name]) && isset($this->restorelist[$this->tablelist[$table_name]])) {
    			$this->restore_data[$this->tablelist[$table_name]]['room'][$room_id][$table_name][] = $data;
    		} else {
    			// 存在しないテーブル
    			// 未処理
    		}
    	}
    	//$this->restore_data[$module_name]['error_mes']
    }

    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchcallbackModules($result, $func_param) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$pathList = explode("_",$row['action_name']);
			$dirname =$pathList[0];
			$row["module_name"] = $func_param->loadModuleName($dirname);
			$ret[$dirname] = $row;
		}
		return $ret;
	}

	/**
	 * テーブルリスト取得
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &getTableList() {
		return $this->tablelist;
	}

	/**
	 * テーブルプライマリーキーリスト取得
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &getTablePrimaryList() {
		return $this->tablePrimarylist;
	}


	/**
	 * テーブルプライマリーキーリスト取得
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function mkdirTemporary($backup_dir) {
		$uploads_backup_dir = FILEUPLOADS_DIR . "backup/";
		if(!file_exists($uploads_backup_dir)) {
			mkdir($uploads_backup_dir, 0755);
		}

		$uploads_backup_dir .= BACKUP_TEMPORARY_DIR_NAME . "/";
		if(!file_exists($uploads_backup_dir)) {
			mkdir($uploads_backup_dir, 0755);
		}
		$uploads_backup_dir .= $backup_dir . "/";
		if(!file_exists($uploads_backup_dir)) {
			mkdir($uploads_backup_dir, 0755);
		}
	}
}
?>
