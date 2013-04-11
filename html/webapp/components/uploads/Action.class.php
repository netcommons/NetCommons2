<?php
/**
 * アップロードテーブル登録用クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Uploads_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	var $_container = null;

	/**
	 * @var FileExtraクラスに手動でセットしたかどうか？
	 *
	 * @access	private
	 */
	var $_file_temporary_path = "";

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Uploads_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	/**
	 * ファイルアップロード処理
	 * @param int    garbage_flag
	 * @param string add_dir	ディレクトリ構造を追加し、その下にファイルを保存したい場合用いる（現状、未使用）
	 * 							"フォルダ1/フォルダ2/"のように指定
	 * @param array　thumbnail_arr(thumbnail_width, thumbnail_height)	サムネイルを作成する場合、指定
	 * @return array filelist
	 * @access	public
	 */
	function uploads($garbage_flag=0, $add_dir = "", $thumbnail_arr = null)
	{
		$getdata =& $this->_container->getComponent("GetData");
		$fileUpload =& $this->_container->getComponent("FileUpload");
		$commonMain =& $this->_container->getComponent("commonMain");
        $fileAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/Action.class.php', "File_Action", "fileAction");
        $session =& $this->_container->getComponent("Session");

		$page_id = $fileUpload->getPageid();
       	$module_id = intval($fileUpload->getModuleid());

       	$unique_id = $fileUpload->getUniqueid();
       	if(!isset($unique_id)) {
       		$unique_id = 0;
       	}
        $download_action_name = $fileUpload->getDownLoadactionName();

        //dir_name取得
        $actionChain =& $this->_container->getComponent("ActionChain");
        $action_name =& $actionChain->getCurActionName();
        $pathList = explode("_", $action_name);
    	$dirname = $pathList[0];

		$filelist = array();
		$download_action_name = ($download_action_name == null) ? "common_download_main" : $download_action_name;
	    $garbage_flag = ($garbage_flag == null || $garbage_flag == 0) ? 0 : 1;
		if($page_id == null || $page_id == 0) {
    		$room_id = 0;
    	} else {
			$pages_obj = $getdata->getParameter("pages");
	    	$room_id = $pages_obj[intval($page_id)]['room_id'];
    	}
    	if($room_id !== null && $download_action_name != null && $garbage_flag !== null) {
	    	//$count        = $fileUpload->count();
	        $originalNames = $fileUpload->getOriginalName();
	        $mimeType     = $fileUpload->getMimeType();
	        $filesize     = $fileUpload->getFilesize();
	        $errormes     = $fileUpload->getErrorMes();
	        $tmp_name     = $fileUpload->getTmpName();
	        foreach($originalNames as $i => $originalName) {
	            //$pathinfo = pathinfo($originalName[$i]);
	            $file_name = "";
	            $physical_file_name = "";
	            $extension = "";
	            if(isset($originalName) && $originalName != "") {

					//
					// tar.gzの対応
					// tar.gzのほかにも同じような拡張子があるかも
					//
					if(preg_match("/.+\.tar\.gz$/i", $originalName)) {
						$extension = "tar.gz";
					} else {
						$pathinfo = pathinfo($originalName);
						if(isset($pathinfo['extension'])) {
							$extension = $pathinfo['extension'];
						} else {
							$extension = "";
						}
					}
	            }

				$insert_time = timezone_date();
				$upload_id = 0;
				$file_name = $originalName;
				// エラーがなければ、テーブル登録＆アップロード
				if($originalName != "" && ((!isset($errormes[$i]) || (isset($errormes[$i]) && $errormes[$i] == "")))) {
					$dest_dir = FILEUPLOADS_DIR . $dirname."/";
					if(!file_exists($dest_dir)) {
						mkdir($dest_dir, octdec(_UPLOAD_FOLDER_MODE));
					}
					//$dest_dir = FILEUPLOADS_DIR.$dirname."/".$room_id."/";
					//if(!file_exists($dest_dir)) {
					//	mkdir($dest_dir, octdec(_UPLOAD_FOLDER_MODE));
					//}
					//$file_path = $dirname."/".$room_id."/";
					$file_path = $dirname."/";
					//階層をもって保存したい場合
					if($add_dir != "") {
						$add_dir = urlencode($add_dir);	//エンコード
						$addPathList = explode("/", $add_dir);
						foreach($addPathList as $addDirName) {
							$dest_dir = $dest_dir.$addDirName."/";
							$file_path .= $addDirName."/";
							if(!file_exists($dest_dir)) {
								mkdir($dest_dir, octdec(_UPLOAD_FOLDER_MODE));
							}
						}
					}

		            //
					//テーブル登録処理
					//
					$params = array(
		                'room_id'             => $room_id,
		                'module_id'           => $module_id,
		                'unique_id'           => $unique_id,
		                'file_name' => $file_name,
		                'physical_file_name'  => "",	//$encode_file_name,
		                'file_path' => $file_path,
		                'action_name'     => $download_action_name,
		                'file_size'     => $filesize[$i],
		                'mimetype'     => $mimeType[$i],
		                'extension'     => $extension,
		                'garbage_flag'     => $garbage_flag
		            );
					$upload_id = $this->insUploads($params);
					if($upload_id > 0) {
						//
						//ファイルアップロード
						//
						$physical_file_name = "${upload_id}.${extension}";
						if($this->_file_temporary_path == "") {
							$fileUpload->move($i, FILEUPLOADS_DIR.$file_path.$physical_file_name);
						} else {
							$tmp_name = $fileUpload->getTmpName();
					        if (isset($tmp_name[$i])) {
					        	if ($fileAction->copyFile($tmp_name[$i], FILEUPLOADS_DIR.$file_path.$physical_file_name)) {
					                chmod(FILEUPLOADS_DIR.$file_path.$physical_file_name, $fileUpload->getFilemode());
					        	}
					        	$encode = $this->getEncode();
					        	$tmp_name[$i] = mb_convert_encoding($tmp_name[$i], _CHARSET, $encode);
					        	$cur_sess_id = $session->getID();
					        	$file_path = FILEUPLOADS_DIR . $pathList[0]."/".strtolower($cur_sess_id);
					        	$tmp_name[$i] = preg_replace("/^".preg_quote($file_path, '/') . "/i", "", $tmp_name[$i]);
					        }
						}
						//
						// サムネイル作成
						//
						if($thumbnail_arr != null) {
							$result = $fileUpload->resize(FILEUPLOADS_DIR.$file_path.$physical_file_name, $thumbnail_arr[0], $thumbnail_arr[1], FILEUPLOADS_DIR.$file_path."${upload_id}_thumbnail.${extension}");
							if($result === false) {
								copy(FILEUPLOADS_DIR.$file_path.$physical_file_name, FILEUPLOADS_DIR.$file_path."${upload_id}_thumbnail.${extension}");
							}
						}
						//$fileUpload->move($i, FILEUPLOADS_DIR.$file_path.$encode_file_name);

						//
						// 携帯用縮小画像を準備作成
						// AllCreator
						//
						$result = $fileUpload->resize(FILEUPLOADS_DIR.$file_path.$physical_file_name, MOBILE_IMGDSP_SIZE_240, 0, FILEUPLOADS_DIR.$file_path.$upload_id."_mobile_".MOBILE_IMGDSP_SIZE_240.".${extension}");
						$result = $fileUpload->resize(FILEUPLOADS_DIR.$file_path.$physical_file_name, MOBILE_IMGDSP_SIZE_480, 0, FILEUPLOADS_DIR.$file_path.$upload_id."_mobile_".MOBILE_IMGDSP_SIZE_480.".${extension}");

					}
				}
				if(!isset($errormes[$i])) {
					$errormes[$i] = "";
				}
				if($file_name != "") {
		            $filelist[$i] = array(
		                'upload_id'           => $upload_id,
		                'file_name' => $file_name,
		                'physical_file_name' => $physical_file_name,
		                'action_name'     => $download_action_name,
		                'file_size'     => $filesize[$i],
		                'mimetype'     => $mimeType[$i],
		                'extension'     => $extension,
		                'garbage_flag'     => $garbage_flag,
		                'insert_time'     => $insert_time,
		                'error_mes'     => $errormes[$i],
		                'tmp_name'     => $tmp_name[$i]
		            );
		            //値セット
		            $fileUpload->setUploadid($i,$upload_id);
		            $fileUpload->setExtension($i,$extension);
		            $fileUpload->setInserttime($i,$insert_time);
				}
	        }
    	}
    	if($this->_file_temporary_path != "") {
    		$fileAction->delDir($this->_file_temporary_path);
    	}

		return $filelist ;
	}

	/**
	 * Uploads Insert
	 * @param array(page_id, module_id, file_name, file_path, action_name, file_size, mimetype, extension, garbage_flag)
	 * @return int upload_id
	 * @access	public
	 */
	function insUploads($params)
	{
		$upload_id = $this->_db->nextSeq("uploads");
		$params['upload_id'] = $upload_id;
		if($params['physical_file_name'] == "") {
			$params['physical_file_name'] = $params['upload_id'] . "." . $params['extension'];
		}
		if(!isset($params['sess_id']) || $params['sess_id'] == "") {
			$session =& $this->_container->getComponent("Session");
			$params['sess_id'] = $session->getID();
		}
		$footer_flag = false;
        if(!isset($params['insert_time'])){
	        $footer_flag = true;
        }
        $result = $this->_db->insertExecute("uploads", $params, $footer_flag);
		if($result === false) {
			return false;
		}

		return $upload_id;
	}

	/**
	 * Uploads Update
	 *
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   boolean $footer_flag
	 * @return boolean true or false
	 * @access	public
	 */
	function updUploads($params=array(), $where_params=array(), $footer_flag=true) {
		return $this->_db->updateExecute("uploads", $params, $where_params, $footer_flag);
	}

	/**
	 * ガーベージフラグを更新する
	 * @param  upload_id, garbage_flag
	 * @return boolean true or false
	 * @access	public
	 */
	function updGarbageFlag($upload_id, $garbage_flag = _OFF)
	{
		$session =& $this->_container->getComponent("Session");

		$params = array(
        	"garbage_flag" => $garbage_flag,
        	"upload_id" =>$upload_id
		);

		$sql = "UPDATE {uploads} SET ".
						"garbage_flag=? ".
					"WHERE upload_id=?";

        $result = $this->_db->execute($sql,$params);
        if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * upload_idによるUploads削除処理
	 * @param int upload_id
	 * @return boolean true or false
	 * @access	public
	 */
	function delUploadsById($upload_id)
	{
		if(!$this->_delUploadFile($upload_id))return false;
		$params = array(
			"upload_id" => $upload_id
		);
		$result = $this->_db->execute("DELETE FROM {uploads} WHERE upload_id=?" .
									" ",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}

		return true;
	}

	function _delUploadFile($upload_id, $upload = null)
	{
		if (empty($upload_id)) return true;
		if($upload == null) {
			$uploads =& $this->_db->selectExecute("uploads", array("upload_id" => $upload_id));
			if($uploads === false) {
				return false;
			}
			if(!isset($uploads[0])) return true; //エラーとしない
			$upload = $uploads[0];
		}

		$file_name = $upload['physical_file_name'];//$upload['upload_id'].".".$upload['extension'];
		$path = FILEUPLOADS_DIR.$upload['file_path'].$file_name;
		if(file_exists($path)) {
			@chmod($path, 0777);
			unlink($path);
		}
		// サムネイル画像があれば、削除
		$thumbnail_path = FILEUPLOADS_DIR.$upload['file_path'].$upload['upload_id']."_thumbnail.".$upload['extension'];
		if(file_exists($thumbnail_path)) {
			@chmod($thumbnail_path, 0777);
			unlink($thumbnail_path);
		}
		// モバイル画像があれば、削除
		$mobile_img = array( MOBILE_IMGDSP_SIZE_240, MOBILE_IMGDSP_SIZE_480 );
		foreach( $mobile_img as $mbl ) {
			$thumbnail_path = FILEUPLOADS_DIR.$upload['file_path'].$upload['upload_id']."_mobile_". $mbl . "." .  $upload['extension'];
			if(file_exists($thumbnail_path)) {
				@chmod($thumbnail_path, 0777);
				unlink($thumbnail_path);
			}
		}
		//実ファイルがない場合でもtrueを返している(手動でファイルを消した場合等、DBのデータのみ残るため)
		return true;
	}

	/**
	 * 条件に該当するアップロードデータを削除する。
	 * 
	 * @param string $whereClause where句文字列
	 * @param array $bindValues バインド値配列
	 * @return boolean true or false
	 * @access	public
	 */
	function deleteByWhereClause($whereClause, $bindValues)
	{
		$sql = "SELECT upload_id, "
					. "physical_file_name, "
					. "file_path, "
					. "extension "
				. "FROM {uploads} "
				. "WHERE " . $whereClause;
		$uploads = $this->_db->execute($sql, $bindValues);
		if ($uploads === false) {
			$this->_db->addError();
			return false;
		}

		$inValue = '';
		foreach($uploads as $upload) {
			$this->_delUploadFile($upload['upload_id'], $upload);
			$inValue .= $upload['upload_id'] . ',';
		}
		if (empty($inValue)) {
			return true;
		}

		$inValue = substr($inValue, 0, -1);
		$sql = "DELETE FROM {uploads} "
				. "WHERE upload_id IN (" . $inValue . ")";
		if (!$this->_db->execute($sql)) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param string
	 * @return	string
	 * @access	public
	 **/
	function setGarbageflag($garbage_flag, $content, $download_action_name="common_download_main&amp;upload_id=") {
		$upload_id = $this->getUploadId($content, $download_action_name);
		if (!count($upload_id)) {
			return false;
		}

		foreach ($upload_id as $value) {
			$this->updGarbageFlag($value, $garbage_flag);
		}
		return true;
	}

	/**
	 * upload_idの一覧取得
	 * @param string
	 * @return	array
	 * @access	public
	 **/
	function getUploadId($string, $download_action_name="common_download_main&amp;upload_id=", $uploads_where_params = array()) {
		$container =& DIContainerFactory::getContainer();
        $request =& $container->getComponent("Request");

		$upload_id_arr = array();
		$content = $string;

		$count = substr_count($content, $download_action_name);
		if (!$count) {
			return $upload_id_arr;
		}

		$parts = explode($download_action_name, $content);

		for ($i = 1; $i <= $count; $i++) {
			if(preg_match("/^([0-9]+)/", $parts[$i], $matches)) {
				if(isset($matches[1])) {
					$id = $matches[1];
					$upload_id_arr[$id] = $id;
				}
			}
		}
		$module_id = $request->getParameter("module_id");
		if(!isset($module_id)) {
			$actionChain =& $container->getComponent("ActionChain");
			$modulesView =& $container->getComponent("modulesView");
        	$curAction = $actionChain->getCurActionName();
        	$pathList = explode("_", $curAction);
        	$module = $modulesView->getModuleByDirname($pathList[0]);
        	if(isset($module['module_id'])) $module_id = $module['module_id'];
		}

		$where_params = array(
			"upload_id IN ('". implode("','", $upload_id_arr). "') " => null,
			"room_id" => $request->getParameter("room_id"),
			"module_id" => $module_id
		);
		if(count($uploads_where_params) > 0) {
			$where_params = array_merge($where_params, $uploads_where_params);
		}
		$upload_id_arr = $this->_db->selectExecute("uploads", $where_params, null, null, null, array($this, "_fetchcallbackgetUploadId"));
		if($upload_id_arr === false) {
			return array();
		}

		return $upload_id_arr;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackgetUploadId($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row["upload_id"]] = $row["upload_id"];
		}
		return $ret;
	}

	/**
	 * FileExtraクラスに指定したパス下のファイルをセット
	 * @param string  $set_path
	 * @param string  $name
	 * @access	public
	 **/
	function setFileByPath($set_path, $name = "uploads") {
		$file_extra =& $this->_container->getComponent("File");
		$file_extra->removeParameter($name);
		$this->setFileTemporaryPath($set_path);
		$files = array();
		$this->_setFileByPath($set_path, $files);
		$file_extra->setParameter($name, $files);
	}

	/**
	 * FileExtraクラスに指定したパス下のファイルをセット
	 * @param string  $set_path
	 * @param array   $files
	 * @access	private
	 **/
	function _setFileByPath($set_path, &$files) {
		$uploadsView =& $this->_container->getComponent("uploadsView");
		if(!is_object($uploadsView)) {
			if(!class_exists("Uploads_View")) {
				include_once WEBAPP_DIR .'/components/uploads/View.class.php';
			}
			$uploadsView =& new Uploads_View;
		}

		$encode = $this->getEncode();

		if ($handle = opendir($set_path)) {
			while (false !== ($file_name = readdir($handle))) {
				if ($file_name == "." || $file_name == "..") continue;
				$path = $set_path."/".$file_name;
				if (!file_exists($path)) continue;
				if(isset($files['name']) && is_array($files['name'])) {
					$count = count($files['name']);
				} else {
					$count = 0;
				}
				if (is_dir($path)) {
					/*
					if($dir_flag) {
						$files['name'][$count] = mb_convert_encoding($file_name, _CHARSET, $encode);
						$files['type'][$count] = "";
						$files['type'][$count] = "";
						$files['size'][$count] = "";
						$files['tmp_name'][$count] = $path;
						$files['error'][$count] = "";
					}
					*/
					$this->_setFileByPath($path, $files);
				} else {
					$files['name'][$count] = mb_convert_encoding($file_name, _CHARSET, $encode);
					$files['type'][$count] = $uploadsView->mimeinfo("type", $file_name);
					$files['size'][$count] = filesize($path);
					$files['tmp_name'][$count] = $path;

					//$files['tmp_name'][$count] = mb_convert_encoding($path, _CHARSET, $encode);
					$files['error'][$count] = "";
				}
			}
		}
	}

	function getEncode() {
		if (stristr($_SERVER['HTTP_USER_AGENT'], "Mac")) {
			// Macの場合
			$encode = "UTF-8";
		} else if (stristr($_SERVER['HTTP_USER_AGENT'], "Windows")) {
			// Windowsの場合
			$encode = "SJIS";
		} else {
			$encode = _CHARSET;
		}
		return $encode;
	}

	function setFileTemporaryPath($file_path) {
		$this->_file_temporary_path = $file_path;
	}

	function getFileTemporaryPath() {
		return $this->_file_temporary_path;
	}
}
?>
