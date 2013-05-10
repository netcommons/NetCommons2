<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

include_once MAPLE_DIR.'/includes/pear/File/Archive.php';
include_once MAPLE_DIR.'/includes/pear/XML/Serializer.php';


/**
* バックアップファイル作成処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Action_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $backup_page_id = null;
    var $module_id = null;
    
    // バリデートによりセット
    var $page = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
    var $uploadsView = null;
    var $uploadsAction = null;
    var $actionChain = null;
    var $fileAction = null;
    var $fileView = null;
    var $pagesView = null;
    var $modulesView = null;
    var $databaseSqlutility = null;
    var $preexecuteMain = null;
    var $configView = null;
    var $session = null;
    var $backupRestore = null;
    var $encryption = null;
    
    var $errorList = null;
    
    // 値をセットするため
    var $_upload_id = null;
    
    /**
     * バックアップファイル作成処理
     *
     * @access  public
     */
    function execute()
    {
    	$container =& DIContainerFactory::getContainer();
    	$this->errorList =& $this->actionChain->getCurErrorList();
    	
    	set_time_limit(BACKUP_TIME_LIMIT);
		// メモリ最大サイズ設定
		ini_set('memory_limit', -1);
		    	
    	if($this->backup_page_id == 0) {
    		//
    		// フルバックアップ	
    		//
    		$result = $this->_fullBackUp();
    		if($result === false) {
    			// バックアップ処理済みに変更
    			if($this->_upload_id != null) {
    				$this->session->removeParameter(array("backup", "backingup", $this->_upload_id));
    			}
				return 'error';
    		}
    	} else {
    		//
    		// ルームバックアップ
    		//
    		//
    		$result = $this->_roomBackUp($this->page);
    		if($result === false) {
    			// バックアップ処理済みに変更
    			if($this->_upload_id != null) {
    				$this->session->removeParameter(array("backup", "backingup", $this->_upload_id));
    			}
				return 'error';
    		}
    	}
    	// 正常終了（エラーリストに完了メッセージ追加）
    	$upload_flag = $this->session->getParameter(array("backup", "backingup", $this->_upload_id));
    	if(isset($upload_flag) && $upload_flag == _ON) {
    		// 完了通知メッセージをまだ出力していない
    		$this->session->removeParameter(array("backup", "backingup", $this->_upload_id));		
    		$this->errorList->add("backup", BACKUP_END_MES);
    	}
    	
        return 'success';
    }
    /**
	 * フルバックアップ処理
	 * @return boolean true or false
	 * @access	private
	 */
	function _fullBackUp() 
	{
		$config = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "db_kind");
		if($config) {
			$db_kind = $config["conf_value"];
		} else {
			$db_kind = _DEFAULT_SQL_KIND;
		}
		$backup_class_path = WEBAPP_DIR ."/components/database/".$db_kind."/Backup.class.php";
		if(!file_exists($backup_class_path)) {
			if($db_kind == "mysqli") {
				$db_kind = "mysql";
				$backup_class_path = WEBAPP_DIR ."/components/database/".$db_kind."/Backup.class.php";
				if(!file_exists($backup_class_path)) {
					$this->errorList->add("backup", BACKUP_UNSUPPORTED_DB);
					return false;
				}
			} else {
				$this->errorList->add("backup", BACKUP_UNSUPPORTED_DB);
				return false;
			}
		}
		include_once $backup_class_path;
		$backup =& new Database_Backup;
			
		$adodb = $this->db->getAdoDbObject();
		
    		
		//
		// uploadsテーブルInsert
		//
		$file_path = "backup/";
		$extension = BACKUP_COMPRESS_EXT;
		$download_action_name = BACKUP_DOWNLOAD_ACTION_NAME;
		$file_name = BACKUP_FULL_FILE_NAME.".".BACKUP_COMPRESS_EXT;
		$mimetype = $this->uploadsView->mimeinfo("type", $file_name);
		$garbage_flag = _ON;
		
		$params = array(
            'room_id'             => 0,
            'module_id'           => $this->module_id,
            'unique_id'           => 0,
            'file_name' => $file_name,
            'physical_file_name'  => "",	//$encode_file_name,
            'file_path' => $file_path,
            'action_name'     => $download_action_name,
            'file_size'     => 0,
            'mimetype'     => $mimetype,
            'extension'     => $extension,
            'garbage_flag'     => $garbage_flag
        );
        $upload_id = $this->uploadsAction->insUploads($params);
		if($upload_id === false) return false;
		$this->_upload_id = $upload_id;
		
		$params = array(
			'upload_id'           => $upload_id,
            'site_id'             => $this->session->getParameter("_site_id"),
            'url'                  => '',
			'parent_id'           => 0,
            'thread_num'          => 0,
            'space_type'          => _OFF,
            'private_flag'        => _OFF,
            'room_id'             => 0
        );
		$result = $this->db->insertExecute("backup_uploads", $params, true);
		if($result === false) {
			$this->_delUploads();
			return false;
		}
		// バックアップ処理中をセッションに保持
		$this->session->setParameter(array("backup", "backingup", $this->_upload_id), _ON);
		
		$temporary_file_path_dir = WEBAPP_DIR."/templates_c/".BACKUP_BACKUP_DIR_NAME."/";
    	if(file_exists($temporary_file_path_dir)) {
			// 現在、同じバックアップファイル作成中
			// エラーとする
			$this->_delUploads();
			$this->errorList->add("backup", BACKUP_BACKINGUP);
			return false;
		}
		
		if(!mkdir($temporary_file_path_dir, 0777)) {
			$this->_delUploads();
			$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
			return false;
		}
		$temporary_file_path = $temporary_file_path_dir.$upload_id."/";
		if(!mkdir($temporary_file_path, 0777)) {
			$this->_delUploads();
			$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
			return false;
		}
		
    	$tables_name = $adodb->MetaTables(false, false, $this->db->getPrefix()."%");
    	$db_prefix = $this->db->getPrefix();
		
    	$dump_handle = fopen($temporary_file_path . BACKUP_FULL_SQL_FILE_NAME, "w");
    	if(!$dump_handle){
    		$this->_delUploads();
        	$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
			return false;
    	}
    	
    	//
		// SQLダンプファイル作成
		//
    	foreach($tables_name as $table_name) {
    		if($table_name == $this->db->getPrefix()."session") {
    			fwrite($dump_handle, $backup->getBackupSqlDump($table_name, true, true, false));
    		} else {
    			fwrite($dump_handle, $backup->getBackupSqlDump($table_name));	
    		}
    	}
    	fclose($dump_handle);
		
		$startIndexDirs = split('/', START_INDEX_DIR);
		$htdocsDirs = split('/', HTDOCS_DIR);
		$baseDirs = split('/', BASE_DIR);
		$installIncDirs = split('/', INSTALL_INC_DIR);
		$styleDirs = split('/', STYLE_DIR);
		$uploadDirs = split('/', FILEUPLOADS_DIR);
		$archiveBaseDir = '';
		$directory = $startIndexDirs[0];
		while ($directory == $htdocsDirs[0]
				&& $directory == $installIncDirs[0]
				&& $directory == $baseDirs[0]
				&& $directory == $styleDirs[0]
				&& $directory == $uploadDirs[0]) {
			$archiveBaseDir = $directory . '/';

			array_shift($startIndexDirs);
			array_shift($htdocsDirs);
			array_shift($baseDirs);
			array_shift($installIncDirs);
			array_shift($styleDirs);
			array_shift($uploadDirs);
			
			if(!isset($startIndexDirs[0])) break;
			$directory = $startIndexDirs[0];
		}

		$source = array();
		$source[] = $temporary_file_path. BACKUP_FULL_SQL_FILE_NAME;

		$archiveDir = (isset($startIndexDirs[0])) ? $archiveBaseDir . implode('/', $startIndexDirs) : substr($archiveBaseDir, 0, strlen($archiveBaseDir) - 1);
		$this->_readStartFile($source, START_INDEX_DIR, $archiveDir);

		if (file_exists(dirname(START_INDEX_DIR) . INDEX_FILE_NAME)) {
			$handle = fopen(dirname(START_INDEX_DIR) . INDEX_FILE_NAME, "r");
			if ($handle) {
				$contents = fread($handle, 94);
				fclose($handle);
				if (strpos($contents, 'NetCommons2.0')) {
					$source[] = File_Archive::read(dirname(START_INDEX_DIR) . INDEX_FILE_NAME, dirname($archiveDir) . INDEX_FILE_NAME);
				}
			}
		} elseif (file_exists(START_INDEX_DIR . '/htdocs' . INDEX_FILE_NAME)) {
			$source[] = File_Archive::read(START_INDEX_DIR . '/htdocs' . INDEX_FILE_NAME, $archiveDir . '/htdocs' . INDEX_FILE_NAME);
		}

		$archiveDir = (isset($htdocsDirs[0])) ? $archiveBaseDir . implode('/', $htdocsDirs) : substr($archiveBaseDir, 0, strlen($archiveBaseDir) - 1);
		$this->_readStartFile($source, HTDOCS_DIR, $archiveDir);

		$source[] = File_Archive::read(START_INDEX_DIR . INDEX_FILE_NAME, $archiveDir . INDEX_FILE_NAME);
		$source[] = File_Archive::read(HTDOCS_DIR . '/images', $archiveDir . '/images');
		$source[] = File_Archive::read(HTDOCS_DIR . '/js', $archiveDir . '/js');
		$source[] = File_Archive::read(HTDOCS_DIR . '/themes', $archiveDir . '/themes');

		if (_FULLBACKUP_OUTPUT_SOURCE) {
			$archiveDir = $archiveBaseDir . implode('/', $baseDirs) . '/';
			$source[] = File_Archive::read(BASE_DIR . '/' . MAPLE_DIR, $archiveDir . basename(MAPLE_DIR));

			$webappDirHandle = opendir(WEBAPP_DIR);
			while (false !== ($file = readdir($webappDirHandle))) {
				if ($file == '.'
					|| $file == '..'
					|| $file == 'config'
					|| $file == 'style'
					|| $file == 'uploads'
					|| $file == 'templates_c') {
					continue;
				}

				$filePath = WEBAPP_DIR . '/' . $file;
				$source[] = File_Archive::read($filePath, $archiveDir . basename(WEBAPP_DIR) . '/' . $file);
			}
			closedir($webappDirHandle);

			$configDirHandle = opendir(WEBAPP_DIR . '/config');
			while (false !== ($file = readdir($configDirHandle))) {
				if ($file == '.'
					|| $file == '..') {
					continue;
				}

				if ($file == 'install.inc.php') {
					$filePath = WEBAPP_DIR . '/config/install.inc.dist.php';
					$source[] = File_Archive::read($filePath, $archiveDir . basename(WEBAPP_DIR) . '/config/install.inc.php');

					continue;
				}

				$filePath = WEBAPP_DIR . '/config/' . $file;
				$source[] = File_Archive::read($filePath, $archiveDir . basename(WEBAPP_DIR) . '/config/' . $file);
			}
			closedir($configDirHandle);
		}

		$archiveDir = $archiveBaseDir . implode('/', $installIncDirs) . '/';
		$source[] = File_Archive::read(INSTALL_INC_DIR . '/install.inc.php', $archiveDir . '/install.inc.php');

		$archiveDir = $archiveBaseDir . implode('/', $styleDirs);
		$source[] = File_Archive::read(STYLE_DIR, $archiveDir);

		$archiveDir = $archiveBaseDir . implode('/', $uploadDirs);
		$uploadDirHandle = opendir(FILEUPLOADS_DIR);
		$upload_dir_flag = false;
		while (false !== ($file = readdir($uploadDirHandle))) {
			if ($file == '.'
				|| $file == '..'
				|| $file == 'backup') {
				continue;
			}

			$filePath = FILEUPLOADS_DIR . $file;
			$source[] = File_Archive::read($filePath, $archiveDir  . $file);
			$upload_dir_flag = true;
		}
		closedir($uploadDirHandle);

		$backupDir = FILEUPLOADS_DIR . 'backup';
		if (is_dir($backupDir)) {
			$uploadIDs = array();
			$params = array(
				_OFF
			);
			$sql = "SELECT upload_id ".
					"FROM {backup_uploads} ".
					"WHERE space_type = ?";
			$uploadIDs = $this->db->execute($sql, $params);
			if ($uploadIDs === false) {
				$this->db->addError();
				return false;
			}

			$backupDirHandle = opendir($backupDir);
			while (false !== ($file = readdir($backupDirHandle))) {
				if ($file == '.'
					|| $file == '..') {
					continue;
				}

				$uploadID = str_replace('.' . BACKUP_COMPRESS_EXT, '', $file);
				$needle = array(
					'upload_id' => $uploadID
				);
				if (in_array($needle, $uploadIDs)) {
					continue;
				}

				$filePath = $backupDir . '/' . $file;
				$source[] = File_Archive::read($filePath, $archiveDir . basename($backupDir) . '/' . $file);
				$upload_dir_flag = true;
			}
			closedir($backupDirHandle);
		}
		
		File_Archive::setOption("tmpDirectory", $temporary_file_path);
		$target_file = FILEUPLOADS_DIR."backup/".$upload_id.".".$extension;
		$dest = File_Archive::toArchive($target_file, File_Archive::toFiles());
		if($upload_dir_flag === false) {
			// uploadsされたものが1件も無い場合
			$archiveDir = $archiveBaseDir . implode('/', $uploadDirs);
			$archiveDir = substr($archiveDir, 0, strlen($archiveDir) - 1);
			$temp_stat = stat(FILEUPLOADS_DIR);
			$stat = array(2=>$temp_stat[2], "mode"=>$temp_stat["mode"]);
			$dest->newFile($archiveDir, $stat);
		}
		if(_FULLBACKUP_OUTPUT_SOURCE) {
			$archiveDir = $archiveBaseDir . implode('/', $baseDirs) . '/'. basename(WEBAPP_DIR) . '/' . 'templates_c';
			
			$temp_stat = stat(WEBAPP_DIR."/templates_c");
			$stat = array(2=>$temp_stat[2], "mode"=>$temp_stat["mode"]);
			$dest->newFile($archiveDir, $stat);
		}
			
		File_Archive::extract(
			$source,
			$dest
		);
		
		// file_size 更新
		$params = array(
			'file_size' => $this->fileView->getSize($target_file),
			'garbage_flag'     => _OFF
		);
		$where_params = array(
			"upload_id" => $upload_id
		);
		$result = $this->uploadsAction->updUploads($params, $where_params);
		if($result === false) {
			$this->_delUploads();
			return false;
		}
		//
		// ダンプファイル削除
		//
		unlink($temporary_file_path. BACKUP_FULL_SQL_FILE_NAME);
		rmdir($temporary_file_path);	
		rmdir(WEBAPP_DIR."/templates_c/".BACKUP_BACKUP_DIR_NAME."/");		
    	return true;
	}
	
    /**
	 * ルームバックアップ処理
	 * @param  array page
	 * @return boolean true or false
	 * @access	private
	 */
	function _roomBackUp(&$page) 
	{
		//
		// uploadsテーブルInsert
		//
		$file_path = "backup/";
		$extension = BACKUP_COMPRESS_EXT;
		$download_action_name = BACKUP_DOWNLOAD_ACTION_NAME;
		$file_name = $page['page_name'].".".BACKUP_COMPRESS_EXT;
		$mimetype = $this->uploadsView->mimeinfo("type", $file_name);
		$garbage_flag = _ON;
		
		$params = array(
            'room_id'             => 0,			//$page['page_id'],
            'module_id'           => $this->module_id,
            'unique_id'           => 0,
            'file_name' => $file_name,
            'physical_file_name'  => "",	//$encode_file_name,
            'file_path' => $file_path,
            'action_name'     => $download_action_name,
            'file_size'       => 0,
            'mimetype'        => $mimetype,
            'extension'       => $extension,
            'garbage_flag'    => $garbage_flag
        );
        $upload_id = $this->uploadsAction->insUploads($params);
		if($upload_id === false) return false;
		
		$params = array(
			'upload_id' => $upload_id,
			'site_id' => $this->session->getParameter("_site_id"),
			'url' => '',
			'parent_id' => $page['parent_id'],
			'thread_num' => $page['thread_num'],
			'space_type' => $page['space_type'],
			'private_flag' => $page['private_flag'],
			'room_id' => $page['page_id']
		);
		$result = $this->db->insertExecute("backup_uploads", $params, true);
		if($result === false) {
			$this->_delUploads();
			return false;
		}
		
		if($upload_id > 0) {
			$this->_upload_id = $upload_id;
			
			// バックアップ処理中をセッションに保持
			$this->session->setParameter(array("backup", "backingup", $this->_upload_id), _ON);
		
			$temporary_file_path = FILEUPLOADS_DIR."backup/".BACKUP_TEMPORARY_DIR_NAME."/".BACKUP_BACKUP_DIR_NAME. "/". $page['page_id']. "/";
			if(file_exists($temporary_file_path)) {
				// 現在、同じバックアップファイル作成中
				// エラーとする
				$this->_delUploads();
				$this->errorList->add("backup", BACKUP_BACKINGUP);
				return false;
			}
			
			$this->backupRestore->mkdirTemporary(BACKUP_BACKUP_DIR_NAME);
			if(mkdir($temporary_file_path, 0755)) {
				//
				// BackUp XML 作成
				//
				
		    	$write_xml_data = "<?xml version=\"1.0\" encoding=\""._CHARSET."\"?>\n<rooms>\n";

				//
				// Version情報書き込み
				//
				$options = array( 
					XML_SERIALIZER_OPTION_ROOT_NAME => "version", 
					XML_SERIALIZER_OPTION_INDENT => "\t", 
					//XML_SERIALIZER_OPTION_XML_ENCODING => _CHARSET, 
					XML_SERIALIZER_OPTION_XML_DECL_ENABLED => false,
					XML_SERIALIZER_OPTION_ENTITIES => XML_UTIL_ENTITIES_NONE //<, >, ", ' and &を変換しない
					//XML_SERIALIZER_OPTION_CDATA_SECTIONS => true // すべてにCDATAタグがつくため、コメントとした
				);
				
				$serializer = new XML_Serializer($options); 
		
				$data_arr = array();
				$data_arr['_version'] = _NC_VERSION;
				$modules = $this->modulesView->getModules(array("system_flag" => _OFF));
				if($modules === false) {
					$this->_delUploads();
					return false;
				}
				if( isset($modules[0]) ) {
					foreach($modules as $module) {
						$pathList = explode("_",$module['action_name']);
						$dirname =$pathList[0];
						$data_arr[$dirname] = $module['version'];
						$data_arr["_".$dirname] = "<![CDATA[".$module['module_name']."]]>";
						$data_arr["__".$module['module_id']] = $dirname;
					}
				} 
				
				$serializer->serialize($data_arr); 
				$xml_data = $serializer->getSerializedData(); 
				
				if($xml_data != "") $xml_data .= "\n";
				$write_xml_data .= $xml_data;
				//fwrite ($this->xml_handle, $xml_data);
				
				//
				// ルーム情報書き込み
				//
				$options = array( 
					XML_SERIALIZER_OPTION_ROOT_NAME => "_room_inf", 
					XML_SERIALIZER_OPTION_INDENT => "\t", 
					//XML_SERIALIZER_OPTION_XML_ENCODING => _CHARSET, 
					XML_SERIALIZER_OPTION_XML_DECL_ENABLED => false,
					XML_SERIALIZER_OPTION_DEFAULT_TAG => "row",
					XML_SERIALIZER_OPTION_ENTITIES => XML_UTIL_ENTITIES_NONE //<, >, ", ' and &を変換しない
					//XML_SERIALIZER_OPTION_CDATA_SECTIONS => true // すべてにCDATAタグがつくため、コメントとした
				);
				
				$serializer = new XML_Serializer($options); 
				
				$data_arr = $this->_serializeXML("pages", array("page_id" => $page['page_id']), null, "");
				
				$serializer->serialize($data_arr); 
				$xml_data = $serializer->getSerializedData(); 
				
				if($xml_data != "") $xml_data .= "\n";
				$write_xml_data .= $xml_data;
				//fwrite ($this->xml_handle, $xml_data);
				
				
				//
				// アップロードファイルコピー処理
				//
				if(!$this->_uploadCopy($temporary_file_path, $page)) {
					$this->_delUploads();
					return false;
				}
				
				$write_xml_data .= $this->_createDumpXML($page['page_id']);
				//if(!$this->_createDumpXML($page['page_id'])) return false;
				
				// 子グループがあれば、その子グループもバックアップ対象とする
				$where_params = array(
									"parent_id" => $page['page_id'],
									"page_id = room_id" => null
								);
				$child_pages = $this->pagesView->getPages($where_params);
				if($child_pages === false) return false;
				if(count($child_pages) > 0) {
					foreach($child_pages as $child_page) {
						if(!$this->_uploadCopy($temporary_file_path, $child_page)) {
							$this->_delUploads();
							return false;
						}
						$write_xml_data .= $this->_createDumpXML($child_page['page_id'], true);
					}
				}
				
				//
				// 書き込み
				//
				$write_xml_data .="</rooms>";
				$xml_handle = fopen($temporary_file_path . BACKUP_ROOM_XML_FILE_NAME, "w");
				if(!$xml_handle) {
					$this->_delUploads();
		        	$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
					return false;
		    	}
				$result = fwrite ($xml_handle, $write_xml_data);
				fclose($xml_handle);
				if($result === false) {
					$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
					return false;
				}
				//
				// 暗号化したペアキーの有効期限書き込み
				//
				$ini_handle = fopen($temporary_file_path . BACKUP_ROOM_XML_INI_NAME, "w");
				if(!$ini_handle) {
		        	$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
		        	$this->_delUploads();
					return false;
		    	}
		    	
		    	// ハッシュ化データを秘密鍵で暗号化
		    	$encryption = $this->encryption->getEncryptionKeys();
		    	$hash_text = sha1($write_xml_data);
		    	$encrypt_text = $this->encryption->encrypt($hash_text, $encryption['private_key']);
				
				$xml_data = "<?xml version=\"1.0\" encoding=\""._CHARSET."\"?>\n".
							"<_expiration>\n".
								"\t<encrypt_text>".$encrypt_text."</encrypt_text>\n".
								"\t<host_field><![CDATA[".BASE_URL.INDEX_FILE_NAME."]]></host_field>\n".
								"\t<expiration_time>".$encryption['expiration_time']."</expiration_time>\n".
							"</_expiration>\n"; 
				fwrite ($ini_handle, $xml_data);
				fclose($ini_handle);
				
				//
				// 圧縮
				//
				//$physical_file = FILEUPLOADS_DIR."backup/".$upload_id.".".$extension;
				if(!is_dir(FILEUPLOADS_DIR."backup/")) {
					mkdir(FILEUPLOADS_DIR."backup/", 0755);
				}
				$target_file = FILEUPLOADS_DIR."backup/".$upload_id.".".$extension;
				//$target_file = mb_convert_encoding($file_obj["file_name"], $this->encode, "auto").".".$file_obj["extension"];
				if (file_exists($temporary_file_path)) {
					File_Archive::extract(File_Archive::read($temporary_file_path), File_Archive::toArchive($target_file, File_Archive::toFiles()));
					
					// テンポラリーファイル削除
					$this->fileAction->delDir($temporary_file_path);
					
					// file_size 更新
					$params = array(
						'file_size' => $this->fileView->getSize($target_file),
						'garbage_flag'     => _OFF
					);
					$where_params = array(
						"upload_id" => $upload_id
					);
					$result = $this->uploadsAction->updUploads($params, $where_params);
					if($result === false) {
						$this->_delUploads();
						return false;
					}
				}
				
				//
				// バックアップ暗号データ履歴登録
				//
				$params = array(
					"encrypt_data" => $encrypt_text,
					"room_id" => $this->backup_page_id
				);
				$result = $this->db->insertExecute("backup_encrypt_history", $params, true);
	        	if ($result === false) {
		       		return $result;
				}
			} else {
				$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
				$this->_delUploads();
				return false;
			}
			
			//$physical_file_name = "${upload_id}.${extension}";
			//$this->fileUpload->move($i, FILEUPLOADS_DIR.$file_path.$physical_file_name);
		}
		return true;
	}
	
	/**
	 * 指定したディレクトリのファイルをFile_Archiveに読み込む処理
	 * @param  array &$source
	 * @param  string $dirname
	 * @param  string $archiveDir
	 * @access	private
	 */
	function _readStartFile(&$source, $dirname, $archiveDir) 
	{
		$handle = opendir($dirname);
		while (false !== ($file = readdir($handle))) {
			if ($file != 'index.php'
				&& $file != 'css.php'
				&& $file != 'js.php'
				&& $file != 'install'
				&& !preg_match("/^.htaccess/", $file)) {
				continue;
			}

			$filePath = $dirname . '/' . $file;
			$source[] = File_Archive::read($filePath, $archiveDir . '/' . $file);
		}
		closedir($handle);

		if(!file_exists($dirname . '/css.php')) {
			$archiveDir .= '/htdocs';
			$source[] = File_Archive::read($dirname . '/htdocs/css.php', $archiveDir . '/css.php');
			$source[] = File_Archive::read($dirname . '/htdocs/js.php', $archiveDir . '/js.php');
			$source[] = File_Archive::read($dirname . '/htdocs/install', $archiveDir . '/install');
		}
		return $source;
	}

	/**
	 * アップロードファイルコピー処理
	 * @param  array page
	 * @access	private
	 */
	function _uploadCopy($temporary_file_path , &$page) 
	{
		// uploadファイルコピー
		// backupファイル以外のuploadしたファイルを取得
		$uploads = $this->db->selectExecute("uploads", array("room_id" => $page['page_id'], "module_id !=". $this->module_id => null));
		if($uploads === false) return 'error';
		if(isset($uploads[0])) {
			foreach($uploads as $upload){
				// copy
				if(!$this->fileAction->copyFile(FILEUPLOADS_DIR.$upload['file_path'].$upload['physical_file_name'], BACKUP_UPLOADS_DIR_NAME."/".$upload['file_path'].$upload['physical_file_name'], $temporary_file_path)) {
					// エラー
					$this->fileAction->delDir($temporary_file_path);
					$this->_delUploads();
					$this->errorList->add("backup", BACKUP_MKDIR_ERROR);
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * エラー処理：uploadsテーブルから削除
	 * @access	private
	 */
	function _delUploads() 
	{
		$this->uploadsAction->delUploadsById($this->_upload_id);
		$this->db->deleteExecute("backup_uploads", array("upload_id" => $this->_upload_id));
		
		// テンポラリーファイル削除(フルバックアップ時)
		$temporary_file_path = WEBAPP_DIR."/templates_c/".BACKUP_BACKUP_DIR_NAME."/";
		if (is_dir( $temporary_file_path )) {
			if ($handle = opendir($temporary_file_path)) {
				while (false !== ($file = readdir($handle))) {
					if ($file == "." || $file == "..") continue;
					$file = $temporary_file_path."/".$file;
					unlink($file);
				}
				closedir($handle);
				rmdir($temporary_file_path);
			}	
		}
	}
	
	/**
	 * SQLのXML_Dump処理
	 * @param  array page
	 * @access	private
	 */
	function _createDumpXML($page_id, $child_flag = false)
	{
		//$database_name = $this->_getDatabaseName();
		$options = array( 
			XML_SERIALIZER_OPTION_ROOT_NAME => "room".strval($page_id), //"room", 
			XML_SERIALIZER_OPTION_INDENT => "\t", 
			//XML_SERIALIZER_OPTION_XML_ENCODING => _CHARSET, 
			XML_SERIALIZER_OPTION_XML_DECL_ENABLED => false,
			XML_SERIALIZER_OPTION_DEFAULT_TAG => "row",
			XML_SERIALIZER_OPTION_ENTITIES => XML_UTIL_ENTITIES_NONE //<, >, ", ' and &を変換しない
			//XML_SERIALIZER_OPTION_CDATA_SECTIONS => true // すべてにCDATAタグがつくため、コメントとした
		);
		
		$serializer = new XML_Serializer($options); 
		
		//
		// pages情報
		//
		//if($child_flag == false) {
		//	// リストアのメインルームの情報をヘッダー情報に含める
		//	$data_arr = $this->_serializeXML("pages", array("page_id" => $page_id), null, "_rooms_inf");
		//	$result = $this->_serializeXML("pages", array("room_id" => $page_id));
		//	$data_arr['pages'] = $result['pages'];
		//} else {
		//	$data_arr = $this->_serializeXML("pages", array("room_id" => $page_id));
		//}
		$data_arr = $this->_serializeXML("pages", array("room_id" => $page_id), array("thread_num" => "ASC"));
		
		$result = $this->_serializeXML("pages_modules_link", array("room_id" => $page_id));
		if(is_array($result) && count($result) > 0) {
			$data_arr['pages_modules_link'] = $result['pages_modules_link'];
			//$data_arr = array_merge($data_arr, $result);
		}
		$result = $this->_serializeXML("pages_users_link", array("room_id" => $page_id));
		if(is_array($result) && count($result) > 0) {
			$data_arr['pages_users_link'] = $result['pages_users_link'];
			//$data_arr = array_merge($data_arr, $result);
		}
		
		//monthly_number 
		$result = $this->_serializeXML("monthly_number", array("room_id" => $page_id));
		if(is_array($result) && count($result) > 0) {
			$data_arr['monthly_number'] = $result['monthly_number'];
		}
		
		//
		// uploads情報
		//
		$result = $this->_serializeXML("uploads", array("room_id" => $page_id, "module_id !=".intval($this->module_id) => null));
		if(is_array($result) && count($result) > 0) {
			$data_arr['uploads'] = $result['uploads'];
			//$data_arr = array_merge($data_arr, $result);
		}
		//
		// pages_style - blocks情報
		//
		$pages = $this->db->selectExecute("pages", array("room_id" => $page_id));
		if($pages === false || !isset($pages[0])) return false;
		$row_pages_style_count = 0;
		$row_blocks_count = 0;
		foreach($pages as $page) {
			$result = $this->_serializeXML("pages_style", array("set_page_id" => $page['page_id']));
			if(is_array($result) && count($result) > 0) {
				if(!isset($data_arr['pages_style'])) {
					$data_arr['pages_style'] = array();
				}
				foreach($result['pages_style'] as $key => $pages_style) {
					//if($key === "primary_key") {
					//	if(!isset($data_arr['pages_style']["primary_key"])) {
					//		$data_arr['pages_style']["primary_key"] = $pages_style;
					//	}
					//} else {
						$data_arr['pages_style'][$row_pages_style_count] = $pages_style;
						$row_pages_style_count++;
					//}
				}
			}
			$result = $this->_serializeXML("pages_meta_inf", array("page_id" => $page['page_id']));
			if(is_array($result) && count($result) > 0) {
				if(!isset($data_arr['pages_meta_inf'])) {
					$data_arr['pages_meta_inf'] = array();
				}
				foreach($result['pages_meta_inf'] as $key => $pages_style) {
					$data_arr['pages_meta_inf'][$row_pages_style_count] = $pages_style;
					$row_pages_style_count++;
				}
			}
			if($page['action_name'] == "") continue;
			$result = $this->_serializeXML("blocks", array("page_id" => $page['page_id']),array("thread_num" => "DESC","col_num" => "ASC","row_num" => "ASC"));
			if(is_array($result) && count($result) > 0) {
				if(!isset($data_arr['blocks'])) {
					$data_arr['blocks'] = array();
				}
				
				foreach($result['blocks'] as $key => $blocks) {
					//if($key === "primary_key") {
					//	if(!isset($data_arr['blocks']["primary_key"])) {
					//		$data_arr['blocks']["primary_key"] = $blocks;
					//	}
					//} else {
						$data_arr['blocks'][$row_blocks_count] = $blocks;
						$row_blocks_count++;
					//}
				}
			}
		}
		
		//
		// 一般モジュール
		//
		$adodb = $this->db->getAdoDbObject();
		$modules = $this->modulesView->getModules(array("system_flag" => _OFF));
		if($modules === false) return false;
		if( isset($modules[0]) ) {
			foreach($modules as $module) {
				if($module['backup_action'] == "auto") {
					$pathList = explode("_",$module['action_name']);
					$dirname =$pathList[0];
					$tableList = $this->databaseSqlutility->getTableList($dirname, false);
					foreach($tableList as $table) {
						$metaColumns = $adodb->MetaColumns($this->db->getPrefix().$table);
						if(isset($metaColumns["ROOM_ID"])) {
							// room_idのカラムが等しいか、NULLのものを取得する
							$result = $this->_serializeXML($table, array("(room_id = ".$page_id." OR room_id IS NULL)"=>null));
							if(is_array($result) && count($result) > 0) {
								if(!isset($data_arr[$table])) {
									$data_arr[$table] = array();
								}
								$row_count = 0;
								foreach($result[$table] as $key => $data_detail) {
									//if($key === "primary_key") {
									//	if(!isset($data_arr[$table]["primary_key"])) {
									//		$data_arr[$table]["primary_key"] = $data_detail;
									//	}
									//} else {
										$data_arr[$table][$row_count] = $data_detail;
										$row_count++;
									//}
									//$data_arr[$table][] = $data_detail;
								}
							}
						}
					}
				} else if($module['backup_action'] != "") {
					// backup_actionを呼び出す
					// TODO:テストを行っていない
					// room_idから指定フォーマットのXMLを返さなければリストア時に使用できない
					// 基本、autoで問題なく動作するため、相当複雑なモジュールができないかぎり使用されない予定
					$params = array(
									"room_id" => $page_id,
									"_header" => "0"
								);
			    	$result = $this->preexecuteMain->preExecute($module['backup_action'], $params);
			    	if(is_array($result)) {
						$data_arr = array_merge($data_arr, $result);
					}
				}
			}
		}
		
		$serializer->serialize($data_arr); 
		$xml_data = $serializer->getSerializedData(); 
		//
		// 書き込み
		//
		if($xml_data != "") $xml_data .= "\n";
		return $xml_data;
	}
	
	/**
	 * SQLのXML_Dump処理
	 * @param  string $tableName
	 * @param  array  $where_params
	 * @param  array  $order_params
	 * @param  string $symbolic_name
	 * @access	private
	 */
	function _serializeXML($tableName, $where_params, $order_params = null, $symbolic_name = null)
	{
		$adodb = $this->db->getAdoDbObject();
		
		$data = array();
		
		//
		// テーブルSELECT
		//
		$table_datas = $this->db->selectExecute($tableName, $where_params, $order_params);
		if($table_datas === false) return false;
		if(!isset($table_datas[0])) return $data;
		
		// $symbolic_name
		$symbolic_name = ($symbolic_name !== null) ? $symbolic_name : $tableName;
		
		//primary_key
		//$primary_key_arr = $adodb->MetaPrimaryKeys($this->db->getPrefix().$tableName);
		$columns_arr = $adodb->MetaColumns($this->db->getPrefix().$tableName);
		/*
		if(is_array($primary_key_arr)) {
			$primary_key_str = "";
			foreach($primary_key_arr as $primary_key) {
				if($primary_key_str == "") {
					$primary_key_str = $primary_key;
				} else {
					$primary_key_str .= "," . $primary_key;
				}
			}
			if($primary_key_str != "") {
				//$table_datas[0]['primary_key'] = $primary_key_str;
				if($symbolic_name === "") {
					$data['primary_key'] = $primary_key_str;
				} else {
					$data[$symbolic_name]['primary_key'] = $primary_key_str;
				}
			}
		}
		*/
		if($symbolic_name === "") {
			$set_data =& $data;
		} else {
			$set_data =& $data[$symbolic_name];
		}
		$count = 0;
		foreach($table_datas as $table_data) {
			foreach($table_data as $column_name => $column_value) {
				if(is_null($column_value)) {
					// NULL
					// 予約語とする（BACKUP_NULL_COLUMN）
					$table_data[$column_name] = "<![CDATA[".BACKUP_NULL_COLUMN."]]>";
				} else if(isset($columns_arr[strtoupper($column_name)]) && 
					($columns_arr[strtoupper($column_name)]->type != "int" &&
					 $columns_arr[strtoupper($column_name)]->type != "smallint" &&
					 $columns_arr[strtoupper($column_name)]->type != "mediumint" &&
					 $columns_arr[strtoupper($column_name)]->type != "bigint" &&
					 $columns_arr[strtoupper($column_name)]->type != "tinyint"
					)) {
					// 整数以外
					$table_data[$column_name] = "<![CDATA[".$column_value."]]>";
				}
			}
			$set_data[$count] = $table_data;
			$count++;
		}
		
		
		return $data;
	}
	
	/**
	 * DB名取得処理
	 * @return string $database_name
	 * @access	private
	 */
	function &_getDatabaseName()
	{
		// DATABASE_DSNからdatabase名取得
		$database_dsn_arr = explode("/", DATABASE_DSN);
		$database_name = $database_dsn_arr[count($database_dsn_arr) - 1];
		$database_name_arr = explode("?", $database_name);
		$database_name = $database_name_arr[0];
		
		return $database_name;
	}
	
	/**
	 * fetch時コールバックメソッド(uploads)
	 * @param result adodb object
	 * @access	private
	 */
	function &_fetchcallbackUploads($result) {
		$ret = array();
		
		while ($row = $result->fetchRow()) {
			$ret[$row['upload_id']] = $row;
		}
		return $ret;
	}
	
}
?>
