<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネット取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Components_View
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
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Cabinet_Components_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_session =& $this->_container->getComponent("Session");
	}

	/**
	 * サイズリスト取得
	 *
	 * @access	public
	 */
	function getSizeList() 
	{
		$commonMain =& $this->_container->getComponent("commonMain");
		$fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        
		$size_list = explode("|", CABINET_MAX_SIZE);
		
		$result = array();
		foreach ($size_list as $i=>$val) {
			if ($val == 0) {
				$result[$val] = CABINET_MAX_SIZE_UNRESTRICTED;
			} else {
				$result[$val] = $fileView->formatSize($val);
			}
		}
		
		return $result;
	}

	/**
	 * キャビネットが配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock() 
	{
		$params = array("cabinet_id" => $this->_request->getParameter("cabinet_id"));
		$sql = "SELECT cab.room_id, block.block_id".
				" FROM {cabinet_manage} cab".
				" INNER JOIN {cabinet_block} block ON (cab.cabinet_id = block.cabinet_id)".
				" WHERE block.cabinet_id = ?".
				" ORDER BY block.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}

	/**
	 * 配置データ取得(デフォルト)
	 *
	 * @access	public
	 */
	function &getDefaultBlock($db_only=false) 
	{
    	$module_id = $this->_request->getParameter("module_id");

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
    		return 'error';
    	}

		$default = array(
			"disp_line" => $config["disp_line"]["conf_value"],
			"disp_standard_btn" => $config["disp_standard_btn"]["conf_value"],
			"disp_address" => $config["disp_address"]["conf_value"],
			"disp_folder" => $config["disp_folder"]["conf_value"],
			"disp_size" => $config["disp_size"]["conf_value"],
			"disp_download_num" => $config["disp_download_num"]["conf_value"],
			"disp_comment" => $config["disp_comment"]["conf_value"],
			"disp_insert_user" => $config["disp_insert_user"]["conf_value"],
			"disp_insert_date" => $config["disp_insert_date"]["conf_value"],
			"disp_update_user" => $config["disp_update_user"]["conf_value"],
			"disp_update_date" => $config["disp_update_date"]["conf_value"]
		);
		
		return $default;
	}

	/**
	 * キャビネットが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function cabExists() 
	{
		$count = $this->getCabCount($this->_request->getParameter("cabinet_id"));
		if ($count > 0) {
			return true;
		}
		return false;
	}

	/**
	 * ルームIDのキャビネット件数を取得する
	 *
     * @return string	キャビネット件数
	 * @access	public
	 */
	function getCabCount($cabinet_id=null) 
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	if (isset($cabinet_id)) {
    		$params["cabinet_id"] = $cabinet_id;
    	}
    	$count = $this->_db->countExecute("cabinet_manage", $params);
		return $count;
	}

	/**
	 * 在配置されているキャビネットIDを取得する
	 *
     * @return string	配置されているキャビネットID
	 * @access	public
	 */
	function &getCurrentCabinetID() 
	{
		$params = array("block_id" => $this->_request->getParameter("block_id"));		
		$sql = "SELECT cabinet_id ".
				"FROM {cabinet_block} ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0]["cabinet_id"];
	}

	/**
	 * キャビネット一覧データを取得する
	 *
     * @return array	キャビネット一覧データ配列
	 * @access	public
	 */
	function &getCabinets() 
	{
		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sort_col = $this->_request->getParameter("sort_col");
		if (empty($sort_col)) {
			$sort_col = "insert_time";
		}
		$sort_dir = $this->_request->getParameter("sort_dir");
		if (empty($sort_dir)) {
			$sort_dir = "DESC";
		}

		$room_id = $this->_request->getParameter("room_id");
		
		$sql = "SELECT manage.*, SUM(file.size) AS total_size" .
				" FROM {cabinet_manage} manage" .
				" LEFT JOIN {cabinet_file} file ON (manage.cabinet_id=file.cabinet_id)" .
				" WHERE manage.room_id = ?" .
				" GROUP BY manage.cabinet_id";
    	if ($sort_col == "total_size") {
			$sql .= " ORDER BY ".$sort_col." ".$sort_dir;
    	} else {
			$sql .= " ORDER BY manage.".$sort_col." ".$sort_dir;
    	}
    	$sql .= ", manage.cabinet_id DESC";

        $result = $this->_db->execute($sql, array("room_id"=>$room_id), $limit, $offset, true, array($this,"_getCabinets"));
		if ($result === false) {
	       	$this->_db->addError();
		}
		return $result;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array navigation_links
	 * @access	private
	 */
	function &_getCabinets(&$recordSet)
	{
		$commonMain =& $this->_container->getComponent("commonMain");
		$fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$row["total_size_format"] = $fileView->formatSize($row["total_size"], 2);
			$result[] = $row;
		}
		return $result;
	}

	/**
	 * キャビネット用デフォルトデータを取得する
	 *
     * @return array	キャビネット用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultCabinet()
	{
		$room_id = $this->_request->getParameter("room_id");
		$module_id = $this->_request->getParameter("module_id");

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
    		return 'error';
    	}
    	if (defined($config["active_flag"]["conf_value"])) {
    		$active_flag = constant($config["active_flag"]["conf_value"]);
    	} else {
	    	$active_flag = intval($config["active_flag"]["conf_value"]);
    	}
    	if (defined($config["add_authority_id"]["conf_value"])) {
    		$add_authority_id = constant($config["add_authority_id"]["conf_value"]);
    	} else {
			$add_authority_id = intval($config["add_authority_id"]["conf_value"]);
    	}
    	$default = array(
			"room_id" => $room_id,
			"cabinet_name" => "",
			"active_flag" => $active_flag,
			"add_authority_id" => $add_authority_id,
			"cabinet_max_size" => $config["cabinet_max_size"]["conf_value"],
			"upload_max_size" => $config["upload_max_size"]["conf_value"]
		);
		return $default;
	}

	/**
	 * キャビネットデータを取得する
	 *
     * @return array	キャビネットデータ配列
	 * @access	public
	 */
	function &getCabinet() 
	{
		$params = array(
			"room_id" => $this->_request->getParameter("room_id"),
			"cabinet_id" => $this->_request->getParameter("cabinet_id")
		);
		$result = $this->_db->selectExecute("cabinet_manage", $params);
        if ($result === false) {
        	return $result;
        }
        $default = $this->getDefaultCabinet();
        $result[0] = array_merge($default, $result[0]);

        $default = $this->getDefaultBlock();
        $result[0] = array_merge($default, $result[0]);
		
		$result[0]["cabinet_max_size"] = 0;
		$result[0]["hasAddAuthority"] = $this->_hasAddAuthority($result[0]);
		$result[0]["compress_download"] = $this->getCompressDownload();
        return $result[0];
	}

	/**
	 * 現在配置されているキャビネットデータを取得する
	 *
     * @return array	配置されているキャビネットデータ配列
	 * @access	public
	 */
	function &getCurrentCabinet() 
	{
		$sql = "SELECT *" .
				" FROM {cabinet_block} block" .
				" INNER JOIN {cabinet_manage} cab ON (block.cabinet_id=cab.cabinet_id)" .
				" WHERE block.block_id = ?";

		$params = array("block_id" => $this->_request->getParameter("block_id"));

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
		}
		if (empty($result)) {
			return $result;
		}
		$default_blk = $this->getDefaultBlock();
        $default_cab = $this->getDefaultCabinet();

		$result[0]["disp_line"] = $default_blk["disp_line"];
		$result[0]["hasAddAuthority"] = $this->_hasAddAuthority($result[0]);
		$result[0]["compress_download"] = $this->getCompressDownload();

		$getdata =& $this->_container->getComponent("GetData");
		$pages = $getdata->getParameter("pages");
		$page_id = $this->_request->getParameter("page_id");
		if (isset($pages[$page_id])) {
			$private_flag = $pages[$page_id]["private_flag"];
		} else {
			$private_flag = _OFF;
		}
		if ($private_flag == _ON) {
			$result[0]["upload_max_size"] = $default_cab["upload_max_size"];
		}

		return $result[0];
	}

	/**
	 * キャビネット使用サイズを取得する
	 *
     * @return int	使用サイズ
	 * @access	public
	 */
	function getUsedSize() 
	{
		$sql = "SELECT SUM(size) AS total_size" .
				" FROM {cabinet_file}" .
				" WHERE room_id = ?" .
				" AND cabinet_id = ?";

		$params = array(
			"room_id" => $this->_request->getParameter("room_id"),
			"cabinet_id" => $this->_request->getParameter("cabinet_id")
		);

        $result = $this->_db->execute($sql, $params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if (empty($result)) {
			return 0;
		} else {
	        return $result[0]["total_size"];
		}
	}

	/**
	 * 使用サイズをチェックする
	 *
     * @return int	使用サイズ
	 * @access	public
	 */
	function checkCapacitySize($sum_size=0) 
	{

		$room_id = $this->_request->getParameter("room_id");
		if ($room_id == 0) { return true; }
		
    	$getdata =& $this->_container->getComponent("GetData");
    	$pages = $getdata->getParameter("pages");
    	if (!isset($pages[$room_id])) {
			$pagesView =& $this->_container->getComponent("pagesView");
			$page = $pagesView->getPageById($room_id);
    	} else {
    		$page =& $pages[$room_id];
    	}

		$configView =& $this->_container->getComponent("configView");
    	
    	$max_capacity = 0;
    	if ($page["private_flag"] == _ON) {
    		$max_capacity = $this->_session->getParameter("_private_max_size");

    	} elseif($page["space_type"] == _SPACE_TYPE_GROUP) {
    		$upload_max_capacity_group = $configView->getConfigByConfname(_SYS_CONF_MODID, "upload_max_capacity_group");
			if(isset($upload_max_capacity_group["conf_value"])) {
				$max_capacity = intval($upload_max_capacity_group["conf_value"]);
			} else {
				$max_capacity = 0;
			}

    	} else {
    		$upload_max_capacity_public = $configView->getConfigByConfname(_SYS_CONF_MODID, "upload_max_capacity_public");
			if(isset($upload_max_capacity_public["conf_value"])) {
				$max_capacity = intval($upload_max_capacity_public["conf_value"]);
			} else {
				$max_capacity = 0;
			}
    	}

    	if ($max_capacity != 0) {
            $commonMain =& $this->_container->getComponent("commonMain");
            $fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        
            $db_sum_size = intval($this->_db->sumExecute("uploads", "file_size", array("room_id"=>$room_id)));
            $sum_size += $db_sum_size;

        	if ($max_capacity < $sum_size) {
        		if ($max_capacity - $db_sum_size < 0) {
        			$rest_size = 0;
        		} else {
        			$rest_size = $max_capacity - $db_sum_size;
        		}
        		// エラー
        		return sprintf(_FILE_UPLOAD_ERR_MAX_CAPACITY, $page["page_name"], $fileView->formatSize($max_capacity), $fileView->formatSize($rest_size));
        	}
    	}
    	return true;
	}

	/**
	 * フォルダリスト取得
	 *
	 * @access	public
	 */
	function getFolders() 
	{
		$sql = "SELECT file_id, parent_id, file_name, depth ".
				"FROM {cabinet_file} ";
		$sql .= "WHERE cabinet_id = ? ";
		$sql .= "AND file_type = ".CABINET_FILETYPE_FOLDER." ";
		$sql .= "ORDER BY depth, parent_id, file_name";

		$params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id")
		);
		$parentID = null;
		$branches = array();
        $result = $this->_db->execute($sql, $params, null, null, true, array($this, "_getFolders"), array($parentID, $branches));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array navigation_links
	 * @access	private
	 */
	function &_getFolders(&$recordSet, &$params)
	{
		$parentID = $params[0];
		$branches = $params[1];
		$parentArray = array();
		$pattern = array("/T/", "/L/");
		$replacement = array("I", "B");

		// 元になる記事データ配列のループ
		while ($row = $recordSet->fields) {
			if (!isset($parentID)) {
				$parentID = $row["parent_id"];
			}
			
			if ($row["parent_id"] == $parentID) {
				// === スレッド記事配列を生成 ===
				$parentArray[$parentID][$row["file_id"]] = $row;
				
				// === 枝配列を生成 ===
				// 次の記事を取得
				$recordSet->MoveNext();
				if ($row["parent_id"] == "0") {
					// 枝配列無し
					$branches[$row["file_id"]] = array();
					$parentArray[$parentID][$row["file_id"]]["branches"] = $branches[$row["file_id"]];
					continue;
				} else {
					// 根記事までの枝配列（先祖）に対してT字型をI字型、L字型をB字型に変換
					$tmpBranches = preg_replace($pattern, $replacement, $branches[$row["parent_id"]]);
				}

				$nextPost = $recordSet->fields;
				if ($nextPost && $nextPost["parent_id"] == $parentID) {
					// 兄弟の弟がある場合T字型を付加
					$branches[$row["file_id"]] = array_merge($tmpBranches, array("T"));
				} else {
					// 兄弟の弟がない場合L字型を付加
					$branches[$row["file_id"]] = array_merge($tmpBranches, array("L"));
				}
				$parentArray[$parentID][$row["file_id"]]["branches"] = $branches[$row["file_id"]];
			} else {
				// === 親記事IDが変わった場合は、変わった親記事IDを元に再帰処理 ===
				$tmpParentArray =& call_user_func(array("Cabinet_Components_View", "_getFolders"), $recordSet, array($row["parent_id"], $branches));
				if (!empty($tmpParentArray)) {
					$parentArray += $tmpParentArray;
				}
				return $parentArray;
			}
		}
		return $parentArray;
	}

	/**
	 * フォルダ件数を取得する
	 *
     * @return string	キャビネット件数
	 * @access	public
	 */
	function getFileCount() 
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$params["cabinet_id"] = $this->_request->getParameter("cabinet_id");
    	$params["parent_id"] = $this->_request->getParameter("folder_id");
		$count = $this->_db->countExecute("cabinet_file", $params);
		return $count;
	}

	/**
	 * ファイルが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function fileExists($file_id) 
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$params["cabinet_id"] = $this->_request->getParameter("cabinet_id");
    	$params["file_id"] = $file_id;

		$count =  $this->_db->countExecute("cabinet_file", $params);
		if ($count > 0) {
			return true;
		}
		return false;
	}

	/**
	 * フォルダが空かどうかするか判断する
	 *
	 * @param	string	$folde_id	フォルダID
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function childExists($folde_id) 
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$params["cabinet_id"] = $this->_request->getParameter("cabinet_id");
    	$params["parent_id"] = $folde_id;

		$count =  $this->_db->countExecute("cabinet_file", $params);
		if ($count > 0) {
			return true;
		}

		return false;
	}

	function &getDefaultFile()
	{
		$room_id = $this->_request->getParameter("room_id");
		$module_id = $this->_request->getParameter("module_id");

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
    		return 'error';
    	}
    	if (defined($config["active_flag"]["conf_value"])) {
    		$active_flag = constant($config["active_flag"]["conf_value"]);
    	} else {
	    	$active_flag = intval($config["active_flag"]["conf_value"]);
    	}
    	if (defined($config["add_authority_id"]["conf_value"])) {
    		$add_authority_id = constant($config["add_authority_id"]["conf_value"]);
    	} else {
			$add_authority_id = intval($config["add_authority_id"]["conf_value"]);
    	}
    	$default = array(
			"room_id" => $room_id,
			"cabinet_name" => "",
			"active_flag" => $active_flag,
			"add_authority_id" => $add_authority_id,
			"cabinet_max_size" => $config["cabinet_max_size"]["conf_value"],
			"upload_max_size" => $config["upload_max_size"]["conf_value"]
		);
		return $default;
	}

	/**
	 * カレントアドレスの取得
	 *
	 * @access	public
	 */
	function getFolderPathName($folder_id, $setArray=array()) 
	{
		if ($folder_id == 0) {
			return $setArray;
		}
		$cabinet_id = $this->_request->getParameter("cabinet_id");

		$sql = "SELECT file_id, parent_id, depth, file_name ".
				"FROM {cabinet_file} ";
		$sql .= "WHERE cabinet_id = ? ";
		$sql .= "AND file_id = ? ";

		$params = array(
			"cabinet_id" => $cabinet_id,
			"file_id" => $folder_id
		);

        $result = $this->_db->execute($sql, $params, 1);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		
		$setArray[$result[0]["depth"]] = $result[0]["file_name"];
		$retArr = $this->getFolderPathName($result[0]["parent_id"], $setArray);
		ksort($retArr);
		return $retArr;
	}

	/**
	 * ファイルリスト取得
	 *
	 * @access	public
	 */
	function getFileList($offset, $limit) 
	{
		$sql = "SELECT F.*, C.comment ".
				"FROM {cabinet_file} F ";
		$sql .= "LEFT JOIN {cabinet_comment} C ".
					"ON (F.file_id = C.file_id) ";
		$sql .= "WHERE F.cabinet_id = ? ";
		$sql .= "AND F.parent_id = ? ";

		$cabinet_id = $this->_request->getParameter("cabinet_id");
		$folder_id = $this->_request->getParameter("folder_id");

		$sort_col = $this->_request->getParameter("sort_col");
		$sort_dir = $this->_request->getParameter("sort_dir");
		
    	if (isset($sort_col)) {
    		if ($sort_col == "comment") {
		    	$order_params = array(
		    		"F.file_type" => ($sort_dir == "ASC" ? "DESC" : "ASC"),
		    		"C.".$sort_col => $sort_dir
		    	);
    		} else {
		    	$order_params = array(
		    		"F.file_type" => ($sort_dir == "ASC" ? "DESC" : "ASC"),
		    		"F.".$sort_col => $sort_dir
		    	);
    		}
    	} else {
    		$order_params = null;
    	}

		if (isset($order_params)) {
			$order_params = array_merge(array("F.file_type"=>"DESC"), $order_params);
		} else {
			$order_params = array("F.file_type"=>"DESC", "F.display_sequence"=>"ASC", "F.file_name"=>"ASC");
		}
		$sql .= $this->_db->getOrderSQL($order_params);
		$params = array(
			"F.cabinet_id" => $cabinet_id, 
			"F.parent_id" => $folder_id
		);
		
		if ($folder_id > 0) {
			if ($offset == 0) {
				$limit--;
			} else {
				$offset--;
			}
		}
		
        $result = $this->_db->execute($sql, $params, $limit, $offset, true, array($this,"_getFileList"));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param recordSet adodb object
	 * @return array navigation_links
	 * @access	private
	 */
	function &_getFileList(&$recordSet)
	{
		$commonMain =& $this->_container->getComponent("commonMain");
		$fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        
		$decompress_files = explode("|", CABINET_DECOMPRESS_FILE);

		while ($row = $recordSet->fetchRow()) {
			$row["prefix_size"] = $fileView->formatSize($row["size"]);
			if ($row["file_type"] == CABINET_FILETYPE_FILE) {
				$row["file_name"] = $row["file_name"].".".$row["extension"];
			}
			if ($row["file_type"] == CABINET_FILETYPE_FILE && in_array(strtolower($row["extension"]), $decompress_files)) {
				$row["decompress_flag"] = true;
			} else {
				$row["decompress_flag"] = false;
			}
			$result[] = $row;
		}
		return $result;
	}


	/**
	 * ファイル名リスト取得
	 *
	 * @access	public
	 */
	function getFileNameList() 
	{
		$cabinet_id = $this->_request->getParameter("cabinet_id");
		$folder_id = $this->_request->getParameter("folder_id");
		$file_id = $this->_request->getParameter("file_id");
		
		$sql = "SELECT file_name, extension, file_type".
				" FROM {cabinet_file}";
		$sql .= " WHERE cabinet_id = ?".
				" AND parent_id = ?" .
				(isset($file_id) ? " AND file_id <> ?" : "");
		
		$params = array(
			"cabinet_id" => $cabinet_id,
			"parent_id" => $folder_id
		);
		if (isset($file_id)) {
			$params["file_id"] = $file_id;
		}
        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getFileNameList"));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * ファイル名リスト取得
	 *
	 * @access	private
	 */
	function &_getFileNameList(&$recordSet) 
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[] = $row["file_name"].($row["file_type"] == CABINET_FILETYPE_FOLDER ? "" : ".".$row["extension"]);
		}
		return $result;
	}

	/**
	 * ファイル名リスト取得
	 *
	 * @access	public
	 */
	function getFile($file_id) 
	{
		$cabinet_id = $this->_request->getParameter("cabinet_id");
		
		$sql = "SELECT F.*, C.comment".
				" FROM {cabinet_file} F".
				" LEFT JOIN {cabinet_comment} C ON (F.file_id = C.file_id)".
				" WHERE F.cabinet_id = ?" .
				" AND F.file_id = ?";

		$params = array(
			"cabinet_id" => $cabinet_id,
			"file_id" => $file_id
		);
        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getFile"));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * ファイル名リスト取得
	 *
	 * @access	public
	 */
	function _getFile(&$recordSet) 
	{
		$commonMain =& $this->_container->getComponent("commonMain");
		$fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        
		$decompress_files = explode("|", CABINET_DECOMPRESS_FILE);
		
		$row = $recordSet->fetchRow();
		$row["org_file_name"] = $row["file_name"];
		if ($row["file_type"] == CABINET_FILETYPE_FILE) {
			$row["prefix_size"] = $fileView->formatSize($row["size"]);
			$row["file_name"] = $row["file_name"].".".$row["extension"];
		}
		if ($row["file_type"] == CABINET_FILETYPE_FILE && in_array(strtolower($row["extension"]), $decompress_files)) {
			$row["decompress_flag"] = true;
		} else {
			$row["decompress_flag"] = false;
		}
		$row["hasEditAuthority"] = $this->_hasEditAuthority($row);
		return $row;
	}

	/**
	 * ファイルサイズ取得
	 *
	 * @access	public
	 */
	function getSize($folder_id, $total=0) 
	{
		$sql = "SELECT file_id, file_type, size ".
				" FROM {cabinet_file}";
		$sql .= " WHERE cabinet_id = ?".
				" AND parent_id = ?";
		
		$params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id"),
			"parent_id" => $folder_id
		);

        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getSize"), array($total));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array navigation_links
	 * @access	private
	 */
	function _getSize($recordSet, $params)
	{
		$total = $params[0];
		while ($row = $recordSet->fetchRow()) {
			if ($row["file_type"] == CABINET_FILETYPE_FOLDER) {
				$total += $this->getSize($row["file_id"], $total);
			} else {
				$total += $row["size"];
			}
		}
		return $total;
	}

	/**
	 * 移動不可リスト取得
	 *
	 * @access	public
	 */
	function getMoveErrFolder($folder_id, $errList=array()) 
	{
		$sql = "SELECT file_id FROM {cabinet_file}".
				" WHERE cabinet_id = ?" .
				" AND parent_id = ?" .
				" AND file_type = ?";

		$params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id"), 
			"parent_id" => $folder_id, 
			"file_type" => CABINET_FILETYPE_FOLDER
		);

        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getMoveErrFolder"), array($errList));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array navigation_links
	 * @access	private
	 */
	function _getMoveErrFolder($recordSet, $params)
	{
		$errList = $params[0];
		while ($row = $recordSet->fetchRow()) {
			$errList[] = $row["file_id"];
			$errList = $this->getMoveErrFolder($row["file_id"], $errList);
		}
		return $errList;
	}

	/**
	 * カレントの親フォルダリスト取得
	 *
	 * @access	public
	 */
	function getCurrentParentFolder($folder_id, $parentList=array()) 
	{
		$sql = "SELECT parent_id FROM {cabinet_file}".
				" WHERE cabinet_id = ?" .
				" AND file_id = ?" .
				" AND file_type = ?";

		$params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id"), 
			"file_id" => $folder_id, 
			"file_type" => CABINET_FILETYPE_FOLDER
		);

        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getCurrentParentFolder"), array($parentList));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array navigation_links
	 * @access	private
	 */
	function _getCurrentParentFolder($recordSet, $params)
	{
		$parentList = $params[0];
		while ($row = $recordSet->fetchRow()) {
			$parentList[] = $row["parent_id"];
			$parentList = $this->getCurrentParentFolder($row["parent_id"], $parentList);
		}
		return $parentList;
	}

	/**
	 * アドレス切り替え取得
	 *
	 * @access	public
	 */
	function switchFolder() 
	{
		$address = $this->_request->getParameter("address");
    	if ($address == "") {
    		return "0";
    	}
		$addressArr = explode("/", $address);
		if (count($addressArr) == 1) {
			return "0";
		}

		$sql = "SELECT file_id, parent_id, depth".
				" FROM {cabinet_file}".
				" WHERE cabinet_id = ?" .
				" AND file_type = ?";

		$params = array(
			"cabinet_id" => $this->_request->getParameter("cabinet_id"),
			"file_type" => CABINET_FILETYPE_FOLDER
		);

		$sql_where = array();
		foreach ($addressArr as $i=>$file_name) {
			if ($i == 0) { continue; }

			$sql_where[] = "(depth = ? AND file_name = ?)";
			$params["depth".$i] = $i;
			$params["file_name".$i] = $file_name;
		}
		$sql .= " AND (". implode(" OR ", $sql_where) .")";

        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_switchFolder"));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * カレントアドレスの取得
	 *
	 * @access	private
	 */
	function _switchFolder($recordSet) 
	{
		$folderList = array();
		while ($row = $recordSet->fetchRow()) {
			$folderList[$row["depth"]][$row["parent_id"]][] = $row["file_id"];
		}
		return $this->_getSwitchFolderId($folderList, 1);
	}
	/**
	 * カレントアドレスの取得
	 *
	 * @access	private
	 */
	function _getSwitchFolderId(&$folderList, $depth, $parent_id="0") 
	{
		if (isset($folderList[$depth]) && isset($folderList[$depth][$parent_id])) {
			return $this->_getSwitchFolderId($folderList, $depth+1, $folderList[$depth][$parent_id][0]);
		}
		return $parent_id;
	}

	/**
	 * リネーム処理
	 *
	 * @access	public
	 */
	function renameFile($file_name, $extension, $nameList=null) 
	{
		if (!isset($nameList)) {
	    	$nameList = $this->getFileNameList();
	        if ($nameList === false) {
	        	return "";
	        }
		}
        if ($extension != "") {
        	$extension = "." . $extension;
        }
		if (in_array($file_name.$extension, $nameList)) {
	        $i = 1;
	        $base_file_name = $file_name;
	        while (true) {
	        	if (!in_array($file_name.$extension, $nameList)) { break; }
        		$file_name = sprintf("%s%03d", $base_file_name, $i);
        		$i++;
	        }
		}
		return $file_name;
	}

	/**
	 * 圧縮コンフィグ取得
	 *
	 * @access	private
	 */
	function getCompressDownload() 
	{
   		$module_id = $this->_request->getParameter("module_id");
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfigByConfname($module_id, "compress_download");
		if ($config === false) {
    		return false;
    	}
		if (defined($config["conf_value"])) {
			return constant($config["conf_value"]);
		} else {
			return $config["conf_value"];
		}
	}

	/**
	 * 解凍コンフィグ取得
	 *
	 * @access	private
	 */
	function getDecompressNewFolder() 
	{
   		$module_id = $this->_request->getParameter("module_id");
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfigByConfname($module_id, "decompress_new_folder");
		if ($config === false) {
    		return false;
    	}
		if (defined($config["conf_value"])) {
			return constant($config["conf_value"]);
		} else {
			return $config["conf_value"];
		}
	}

	/**
	 * 追加権限チェック
	 *
	 * @access	private
	 */
	function _hasAddAuthority(&$cabinet) 
	{
		$_user_id = $this->_session->getParameter("_user_id");
		$_auth_id = $this->_session->getParameter("_auth_id");

		if ($_auth_id >= _AUTH_CHIEF) {
			return _ON;
		}
		if ($_auth_id >= $cabinet["add_authority_id"]) {
			return _ON;
		}
		return _OFF;
	}

	/**
	 * 編集権限チェック
	 *
	 * @access	public
	 */
	function _hasEditAuthority(&$file) 
	{
		$_user_id = $this->_session->getParameter("_user_id");
		$_auth_id = $this->_session->getParameter("_auth_id");
		$_hierarchy = $this->_session->getParameter("_hierarchy");

		if ($_auth_id >= _AUTH_CHIEF) {
			return true;
		}
		if ($file["file_type"] == CABINET_FILETYPE_FOLDER && $this->childExists($file["file_id"])) {
			return false;
		}

    	$authCheck =& $this->_container->getComponent("authCheck");
		$file_hierarchy = $authCheck->getPageHierarchy($file["insert_user_id"], $this->_request->getParameter("room_id"));
		if ($file["insert_user_id"] == $_user_id || $_hierarchy > $file_hierarchy) {
	        return true;
		} else {
	        return false;
		}
	}

}
?>