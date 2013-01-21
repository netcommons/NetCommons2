<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録処理
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Components_Action
{
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var cabinetViewオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_cabinetView = null;

	/**
	 * @var uploadsActionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_uploadsAction = null;

	/**
	 * @var uploadsViewオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_uploadsView = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var 圧縮・解凍用メンバ変数
	 */
	var $archive_full_path = null;
	var $encode = null;
	var $_source = array();


	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Cabinet_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_cabinetView =& $this->_container->getComponent("cabinetView");
		$commonMain =& $this->_container->getComponent("commonMain");
		$this->_uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$this->_uploadsView =& $this->_container->getComponent("uploadsView");
		$this->_request =& $this->_container->getComponent("Request");
	}

	/**
	 * キャビネットを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setCabinet()
	{
		$getdata =& $this->_container->getComponent("GetData");
		$pages = $getdata->getParameter("pages");
		$page_id = $this->_request->getParameter("page_id");
		$private_flag = $pages[$page_id]["private_flag"];

		if ($private_flag == _ON) {
			$params = array(
				"cabinet_name" => $this->_request->getParameter("cabinet_name"),
				"add_authority_id" => intval($this->_request->getParameter("add_authority"))
			);
		} else {
			$params = array(
				"cabinet_name" => $this->_request->getParameter("cabinet_name"),
				"add_authority_id" => intval($this->_request->getParameter("add_authority")),
				//"cabinet_max_size" => intval($this->_request->getParameter("cabinet_max_size")),
				"upload_max_size" => intval($this->_request->getParameter("upload_max_size"))
			);
		}

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($actionName == "cabinet_action_edit_create") {
			$default = $this->_cabinetView->getDefaultCabinet();
			$params = array_merge($default, $params);
			$result = $this->_db->insertExecute("cabinet_manage", $params, true, "cabinet_id");
		} else {
			$cabinet_id = $this->_request->getParameter("cabinet_id");
			$result = $this->_db->updateExecute("cabinet_manage", $params, array("cabinet_id"=>$cabinet_id), true);
		}
		if (!$result) {
			return false;
		}

        if (empty($cabinet_id)) {
			$this->_request->setParameter("cabinet_id", $result);
	        if (!$this->setBlock()) {
				return false;
			}
        }
		return true;
	}

	/**
	 * キャビネット用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$block_id = $this->_request->getParameter("block_id");
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$count = $this->_db->countExecute("cabinet_block", array("block_id"=>$block_id));

		$params = array(
			"block_id" => $block_id,
			"cabinet_id" => $this->_request->getParameter("cabinet_id")
		);

		if ($count == 0) {
			$default = $this->_cabinetView->getDefaultBlock(true);
			$params = array_merge($params, $default);
		}

		if ($actionName == "cabinet_action_edit_style") {
	    	$params["disp_standard_btn"] = _ON;
			$params["disp_address"] = intval($this->_request->getParameter("disp_address"));
			$params["disp_folder"] = intval($this->_request->getParameter("disp_folder"));
			$params["disp_size"] = intval($this->_request->getParameter("disp_size"));
			$params["disp_download_num"] = intval($this->_request->getParameter("disp_download_num"));
			$params["disp_comment"] = intval($this->_request->getParameter("disp_comment"));
			$params["disp_insert_user"] = intval($this->_request->getParameter("disp_insert_user"));
			$params["disp_insert_date"] = intval($this->_request->getParameter("disp_insert_date"));
			$params["disp_update_user"] = intval($this->_request->getParameter("disp_update_user"));
			$params["disp_update_date"] = intval($this->_request->getParameter("disp_update_date"));
		}

		if ($count > 0) {
			$result = $this->_db->updateExecute("cabinet_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("cabinet_block", $params, true);
		}
        if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * キャビネット削除処理
	 *
	 * @return boolean
	 * @access public
	 */
	function delCabinet()
	{
    	$params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id")
		);
    	$result = $this->_db->deleteExecute("cabinet_block", $params);
    	if($result === false) {
    		return false;
    	}
    	$result = $this->_db->deleteExecute("cabinet_manage", $params);
    	if($result === false) {
    		return false;
    	}
    	$cabinet_file = $this->_db->selectExecute("cabinet_file", $params);
    	if($cabinet_file === false) {
    		return false;
    	}
    	foreach ($cabinet_file as $i=>$value) {
    		$result = $this->delFile($value["file_id"]);
    		if (!$result) {
    			return false;
    		}
    	}
    	return true;
	}

	/**
	 * ファイル削除処理
	 *
	 * @param  int    file_id
	 * @return boolean
	 * @access public
	 */
	function delFile($file_id)
	{
		if ($file_id == 0) { return false; }

		$sql = "SELECT file_id FROM {cabinet_file} ";
		$sql .= "WHERE parent_id = ? ";
    	$params = array(
			"parent_id" => $file_id
		);
        $result = $this->_db->execute($sql, $params);
    	if($result === false) {
    		return false;
    	}
    	if (!empty($result)) {
	    	foreach ($result as $i=>$value) {
	    		$result = $this->delFile($value["file_id"]);
	    		if (!$result) { return false; }
	    	}
    	}

     	$params = array(
			"file_id"=>$file_id
		);
    	$result = $this->_db->selectExecute("cabinet_file", $params);
    	if($result === false) {
    		return false;
    	}
    	if (!empty($result)) {
	    	$upload_id = $result[0]["upload_id"];
	    	$result = $this->_db->deleteExecute("cabinet_file", $params);
	    	if($result === false) {
	    		return false;
	    	}
	    	$result = $this->_db->deleteExecute("cabinet_comment", $params);
	    	if($result === false) {
	    		return false;
	    	}
			$result = $this->_uploadsAction->delUploadsById($upload_id);
	    	if($result === false) {
	    		return false;
	    	}
    	}
		//--新着情報関連 Start--
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$result = $whatsnewAction->delete($file_id);
    	if ($result === false) {
			return false;
		}
		//--新着情報関連 End--
    	return true;
	}

	/**
	 * ファイル・フォルダを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setFile()
	{
    	$cabinet = $this->_request->getParameter("cabinet");
    	$folder = $this->_request->getParameter("folder");
    	$folder_id = $this->_request->getParameter("folder_id");
		$file_name = $this->_request->getParameter("file_name");
		$comment = $this->_request->getParameter("comment");

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$insertFlag = false;

		if ($actionName == "cabinet_action_main_add_folder") {
	    	$params = array(
				"cabinet_id" => $cabinet["cabinet_id"],
				"upload_id" => 0,
				"parent_id" => $folder_id,
				"file_name" => $file_name,
				"extension" => "",
				"depth" => intval($folder["depth"]) + 1,
				"size" => 0,
				"download_num" => 0,
				"file_type" => CABINET_FILETYPE_FOLDER,
				"display_sequence" => 0
			);
	    	$file_id = $this->_db->insertExecute("cabinet_file", $params, true, "file_id");
	    	if ($file_id === false) {
	    		return false;
	    	}
	    	$params = array(
				"file_id" => $file_id,
				"comment" => $comment
			);
	    	$result = $this->_db->insertExecute("cabinet_comment", $params, true);
	    	if ($result === false) {
	    		return false;
	    	}
	    	$file_type = CABINET_FILETYPE_FOLDER;
			$insertFlag = true;
		} elseif ($actionName == "cabinet_action_main_add_file") {
			$extension = $this->_request->getParameter("extension");
	    	$params = array(
				"cabinet_id" => $cabinet["cabinet_id"],
				"upload_id" => $this->_request->getParameter("upload_id"),
				"parent_id" => $folder_id,
				"file_name" => $file_name,
				"extension" => $extension,
				"depth" => intval($folder["depth"]) + 1,
				"size" => $this->_request->getParameter("size"),
				"download_num" => 0,
				"file_type" => CABINET_FILETYPE_FILE,
				"display_sequence" => 0
			);
	    	$file_id = $this->_db->insertExecute("cabinet_file", $params, true, "file_id");
	    	if ($file_id === false) {
	    		return false;
	    	}
	    	$params = array(
				"file_id" => $file_id,
				"comment" => $comment
			);
	    	$result = $this->_db->insertExecute("cabinet_comment", $params, true);
	    	if ($result === false) {
	    		return false;
	    	}
	    	$file_type = CABINET_FILETYPE_FILE;
	    	$file_name .= ".".$extension;
			$insertFlag = true;
		} else {
			$file_id = $this->_request->getParameter("file_id");
			$file = $this->_request->getParameter("file");
	    	$params = array(
				"file_name" => $file_name
			);
	    	$result = $this->_db->updateExecute("cabinet_file", $params, array("file_id"=>$file_id), true);
	    	if ($result === false) {
	    		return false;
	    	}
	    	$params = array(
				"comment" => $comment
			);
	    	$result = $this->_db->updateExecute("cabinet_comment", $params, array("file_id"=>$file_id), true);
	    	if ($result === false) {
	    		return false;
	    	}
	    	$file_type = $file["file_type"];
	    	$file_name = ($file_type == CABINET_FILETYPE_FOLDER ? $file_name : $file_name.".".$file["extension"]);
		}

		//--新着情報関連 Start--
		if ($file_type == CABINET_FILETYPE_FOLDER) {
			$description = sprintf(CABINET_WHATSNEW_DIR, $cabinet["cabinet_name"], $file_name);
		} else {
			$description = sprintf(CABINET_WHATSNEW_FILE, $cabinet["cabinet_name"], $file_name);
		}
		$description .= !empty($comment) ? sprintf(CABINET_WHATSNEW_COMMENT, $comment) : "";
		$whatsnew = array(
			"unique_id" => $file_id,
			"title" => $file_name,
			"description" => $description,
			"action_name" => "cabinet_view_main_init",
			"parameters" => "cabinet_id=". $cabinet["cabinet_id"]."&folder_id=".$folder["file_id"]
		);
		if (!$insertFlag) {
			$cabinet_file = $this->_db->selectExecute("cabinet_file", array("file_id"=>$file_id));
			$whatsnew["insert_time"] = $cabinet_file[0]["insert_time"];
			$whatsnew["insert_user_id"] = $cabinet_file[0]["insert_user_id"];
			$whatsnew["insert_user_name"] = $cabinet_file[0]["insert_user_name"];
		}
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$whatsnewAction->auto($whatsnew);
		//--新着情報関連 End--
		return true;
	}

	/**
	 * ファイル・フォルダの移動
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function moveFile()
	{
    	$cabinet = $this->_request->getParameter("cabinet");
    	$folder = $this->_request->getParameter("folder");
    	$folder_id = $this->_request->getParameter("folder_id");
    	$file = $this->_request->getParameter("file");
    	$params = array(
			"parent_id" => $folder_id,
			"file_name" => $this->_request->getParameter("file_name"),
			"depth" => intval($folder["depth"]) + 1
		);
    	$result = $this->_db->updateExecute("cabinet_file", $params, array("file_id"=>$file["file_id"]), true);
    	if ($result === false) {
    		return false;
    	}
		if ($file["file_type"] == CABINET_FILETYPE_FOLDER) {
			return $this->_setDepth($file["file_id"], $params["depth"]);
		} else {
			return true;
		}
	}
	/**
	 * ファイル・フォルダの移動
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function _setDepth($parent_id, $depth)
	{
    	$params = array(
			"depth" => $depth + 1
		);
    	$result = $this->_db->updateExecute("cabinet_file", $params, array("parent_id"=>$parent_id), true);
    	if ($result === false) {
    		return false;
    	}

		$files =& $this->_db->selectExecute("cabinet_file", array("parent_id"=>$parent_id, "file_type"=>CABINET_FILETYPE_FOLDER));
	    if ($files === false) {
	    	return false;
	    }
	    if (isset($files[0])) {
	    	foreach ($files as $i=>$file) {
				$result = $this->_setDepth($file["file_id"], $params["depth"]);
		        if ($result === false) {
		        	return false;
		        }
	    	}
	    }
		return true;
	}

	/**
	 * ダウンロード処理
	 * @param  int    file_id
	 * @return boolean
	 * @access public
	 */
	function setDownload($file_id)
	{
		$sql = "UPDATE {cabinet_file} SET download_num = download_num + 1".
				" WHERE file_id = ?";
    	$params = array(
			"file_id" => $file_id
		);
        $result = $this->_db->execute($sql, $params);
		if (!$result) {
			$this->_db->addError();
			return false;
		}
    	return true;
	}

	/**
	 * 圧縮処理
	 *
	 * @return boolean
	 * @access public
	 */
	function compressFile()
	{
    	$cabinet = $this->_request->getParameter("cabinet");
    	$file = $this->_request->getParameter("file");
    	$module_id = $this->_request->getParameter("module_id");

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfigByConfname($module_id, "compress_ext");

		if ($config === false) {
    		return false;
    	}
		if (defined($config['conf_value'])) {
			$extension = constant($config['conf_value']);
		} else {
			$extension = $config['conf_value'];
		}

		$file_path = "cabinet/";
	   	if (!file_exists(FILEUPLOADS_DIR."/".$file_path)) {
    		mkdir(FILEUPLOADS_DIR."/".$file_path, octdec(_UPLOAD_FOLDER_MODE));
    	}

		$file_name = $this->_cabinetView->renameFile($file["org_file_name"], $extension);

    	$params = array(
			"room_id" => $this->_request->getParameter("room_id"),
			"module_id" => $module_id,
			"unique_id" => $file["cabinet_id"],
			"file_name" => $file_name.".".$extension,
			"physical_file_name" => "",
			"file_path" => $file_path,
			"action_name" => "common_download_main",
			"file_size" => 0,
			"mimetype" => $this->_uploadsView->mimeinfo("type", $file_name.".".$extension),
			"extension" => $extension,
			"garbage_flag" => _ON
		);
		$upload_id = $this->_uploadsAction->insUploads($params);
    	if ($upload_id === false) {
    		return false;
    	}

		$archive_file_name = $upload_id;
		$this->archive_full_path = FILEUPLOADS_DIR."/".$file_path."/".$archive_file_name.".".$extension;

		if (stristr($_SERVER['HTTP_USER_AGENT'], "Mac")) {
			// Macの場合
			$this->encode = "UTF-8";
		} else if (stristr($_SERVER['HTTP_USER_AGENT'], "Windows")) {
			// Windowsの場合
			$this->encode = "SJIS";
		} else {
			$this->encode = _CHARSET;
		}

		if ($file["file_type"] == CABINET_FILETYPE_FOLDER) {
			$result = $this->_compressFile($file["file_id"], mb_convert_encoding($file["org_file_name"], $this->encode, "auto")."/");
			if ($result === false) {
				return false;
			}
			if (count($this->_source) > 0) {
				File_Archive::extract($this->_source, File_Archive::toArchive($this->archive_full_path, File_Archive::toFiles()));
			}
		} else {
			$sql = "SELECT F.file_id, F.parent_id, F.file_type, F.file_name, F.extension, U.file_path, U.physical_file_name ".
					"FROM {cabinet_file} F ".
					"LEFT JOIN {uploads} U ON (F.upload_id=U.upload_id) ";
			$sql .= "WHERE F.cabinet_id = ? ";
			$sql .= "AND F.file_id = ? ";
			$params = array(
				"cabinet_id" => $file["cabinet_id"],
				"file_id" => $file["file_id"]
			);
	        $result = $this->_db->execute($sql, $params);
			if ($result === false) {
		       	$this->_db->addError();
		       	return false;
			}
			$physical_file = FILEUPLOADS_DIR.$result[0]["file_path"].$result[0]["physical_file_name"];
			$target_file = mb_convert_encoding($file["org_file_name"], $this->encode, "auto").".".$file["extension"];
			if (file_exists($physical_file)) {
				File_Archive::extract(File_Archive::read($physical_file, $target_file), File_Archive::toArchive($this->archive_full_path, File_Archive::toFiles()));
			}
		}

		if (file_exists($this->archive_full_path)) {
			$file_size = filesize($this->archive_full_path);
		} else {
			return false;
		}

		if ($cabinet["compress_download"] == _OFF) {
			$result = $this->_uploadsAction->updUploads(array("file_size"=>$file_size, "garbage_flag"=>_OFF), array("upload_id"=>$upload_id));
			if ($result === false) {
		       	return false;
			}
	    	$params = array(
				"cabinet_id" => $file["cabinet_id"],
				"upload_id" => $upload_id,
				"parent_id" => $file["parent_id"],
				"file_name" => $file_name,
				"extension" => $extension,
				"depth" => $file["depth"],
				"size" => $file_size,
				"download_num" => 0,
				"file_type" => CABINET_FILETYPE_FILE,
				"display_sequence" => 0
			);
	    	$file_id = $this->_db->insertExecute("cabinet_file", $params, true, "file_id");

	    	$params = array(
				"file_id" => $file_id,
				"comment" => ""
			);
	    	$result = $this->_db->insertExecute("cabinet_comment", $params, true);
	    	if ($result === false) {
	    		return false;
	    	}
		} else {
			$result = $this->_uploadsAction->updUploads(array("file_size"=>$file_size), array("upload_id"=>$upload_id));
			if ($result === false) {
		       	return false;
			}
			$pathname = FILEUPLOADS_DIR.$file_path;
			$filename = $file_name.".".$extension;
			$physical_file_name = $archive_file_name.".".$extension;
	    	clearstatcache();

    		$this->_uploadsView->headerOutput($pathname, $filename, $physical_file_name);
			$result = $this->_uploadsAction->delUploadsById($upload_id);
			if ($file['file_type'] == CABINET_FILETYPE_FILE) {
				$this->setDownload($file['file_id']);
			}
			exit;
		}
    	return true;
	}
	/**
	 * 圧縮処理
	 *
	 * @return boolean
	 * @access private
	 */
	function _compressFile($parent_id, $folder_path="")
	{
		$sql = "SELECT F.file_id, F.parent_id, F.file_type, F.file_name, F.extension, U.file_path, U.physical_file_name ".
				"FROM {cabinet_file} F ".
				"LEFT JOIN {uploads} U ON (F.upload_id=U.upload_id) ";
		$sql .= "WHERE F.cabinet_id = ? ";
		$sql .= "AND F.parent_id = ? ";
		$params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id"),
			"parent_id" => $parent_id
		);
        $files = $this->_db->execute($sql, $params);
		if ($files === false) {
	       	$this->_db->addError();
	       	return false;
		}
		if (empty($files)) { return true; }

		foreach ($files as $i=>$db_file) {
			if ($db_file["file_type"] == CABINET_FILETYPE_FOLDER) {
				$result = $this->_compressFile($db_file["file_id"], $folder_path.mb_convert_encoding($db_file["file_name"], $this->encode, "auto")."/");
				if ($result === false) {
					return $result;
				}
			} else {
				$physical_file = FILEUPLOADS_DIR.$db_file["file_path"].$db_file["physical_file_name"];
				$target_file = $folder_path.mb_convert_encoding($db_file["file_name"], $this->encode, "auto").".".$db_file["extension"];
				if (file_exists($physical_file)) {
					$this->_source[] = File_Archive::read($physical_file, $target_file);
					//File_Archive::extract(File_Archive::read($physical_file, $target_file), $this->archive_full_path);
				}
		    	$cabinet = $this->_request->getParameter("cabinet");
				if ($cabinet["compress_download"] == _ON) {
					$this->setDownload($db_file["file_id"]);
				}
			}
		}
		return true;
	}

	/**
	 * 解凍処理
	 *
	 * @return boolean
	 * @access public
	 */
	function decompressFile($path, $parent_id, $depth, $rename=0)
	{
    	$handle = opendir($path);
    	if (!$handle) { return false; }

		if ($rename == _ON) {
			$nameList = $this->_cabinetView->getFileNameList();
		}

		while (false !== ($file_name = readdir($handle))) {
			if ($file_name == "." || $file_name == "..") { continue; }

			$file_path = $path."/".$file_name;
			if (!file_exists($file_path)) { continue; }

			$encode_name = mb_convert_encoding($file_name, _CHARSET, $this->encode);

			if (is_dir($file_path)) {
				if ($rename == _ON) {
					$encode_name = $this->_cabinetView->renameFile($encode_name, "", $nameList);
				}
				$params = array(
					"upload_id" => 0,
					"parent_id" => $parent_id,
					"file_name" => $encode_name,
					"extension" => "",
					"depth" => $depth,
					"size" => 0,
					"file_type" => CABINET_FILETYPE_FOLDER,
				);
				$file_id = $this->execDecompress($params);
				if ($file_id === false ) { return false; }

				$this->decompressFile($file_path, $file_id, $depth + 1);
			} else {
				$pathinfo = pathinfo($file_name);
				if ($pathinfo === false) {
					$commonMain =& $this->_container->getComponent("commonMain");
					$fileAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/Action.class.php', "File_Action", "fileAction");
        			$result = $fileAction->delDir($file_path);
					continue;
				}
				if ($rename == _ON) {
					$encode_name = $this->_cabinetView->renameFile(strtr($encode_name, array(".".$pathinfo['extension']=>"")), $pathinfo['extension'], $nameList);
				}
				$params = array(
					"upload_id" => 0,
					"parent_id" => $parent_id,
					"file_name" => strtr($encode_name, array(".".$pathinfo['extension']=>"")),
					"extension" => $pathinfo['extension'],
					"depth" => $depth,
					"size" => filesize($file_path),
					"file_type" => CABINET_FILETYPE_FILE,
				);
				$file_id = $this->execDecompress($params, $file_path);
				if ($file_id === false ) { return false; }
			}
		}
		closedir($handle);

    	return true;
	}
	/**
	 * 解凍のデータベースに登録処理
	 *
	 * @return boolean
	 * @access private
	 */
    function execDecompress($params, $file_path="")
    {
    	if ($params["file_type"] == CABINET_FILETYPE_FILE) {
	    	$upload_params = array(
				"room_id" => $this->_request->getParameter("room_id"),
				"module_id" => $this->_request->getParameter("module_id"),
				"unique_id" => $this->_request->getParameter("cabinet_id"),
				"file_name" => $params["file_name"].".".$params["extension"],
				"physical_file_name" => "",
				"file_path" => "cabinet/",
				"action_name" => "common_download_main",
				"file_size" => $params["size"],
				"mimetype" => $this->_uploadsView->mimeinfo("type", $params["file_name"].".".$params["extension"]),
				"extension" => $params['extension'],
				"garbage_flag" => _OFF
			);
			$upload_id = $this->_uploadsAction->insUploads($upload_params);
	    	if ($upload_id === false) {
	    		return false;
	    	}
	    	$result = $this->_uploadsView->getUploadById($upload_id);
	    	if ($result === false) {
	    		return false;
	    	}

			copy($file_path, FILEUPLOADS_DIR.$result[0]["file_path"].$result[0]["physical_file_name"]);
			chmod(FILEUPLOADS_DIR.$result[0]["file_path"].$result[0]["physical_file_name"], 0666);

			$params["upload_id"] = $upload_id;
    	}

    	$file_params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id"),
			"upload_id" => $params["upload_id"],
			"parent_id" => $params["parent_id"],
			"file_name" => $params["file_name"],
			"extension" => $params["extension"],
			"depth" => $params["depth"],
			"size" => $params["size"],
			"download_num" => 0,
			"file_type" => $params["file_type"],
			"display_sequence" => 0
		);
    	$file_id = $this->_db->insertExecute("cabinet_file", $file_params, true, "file_id");
    	if ($file_id === false) {
    		return false;
    	}
    	$file_params = array(
			"file_id" => $file_id,
			"comment" => ""
		);
    	$result = $this->_db->insertExecute("cabinet_comment", $file_params, true);
    	if ($result === false) {
    		return false;
    	}
    	return $file_id;
    }
}
?>
