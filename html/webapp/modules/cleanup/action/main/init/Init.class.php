<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* ファイルクリーンアップ実行処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cleanup_Action_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $target_module = null;
    
    // 使用コンポーネントを受け取るため
	var $modulesView = null;
	var $uploadsView = null;
	var $uploadsAction = null;
	var $db = null;
    
    // 値をセットするため
    var $ret_module_name_arr = array();
	var $ret_str_arr = array();
    /**
     * ファイルクリーンアップ実行処理
     *
     * @access  public
     */
    function execute()
    {
    	$modules = $this->modulesView->getModules(null, array("{modules}.system_flag"=>"DESC", "{modules}.display_sequence" => "ASC"), null, null, array($this, "_fetchcallbackModules"));
		foreach($modules as $module) {
			$dirname = $module['dirname'];
			if(!(isset($modules[$dirname]) && $this->target_module != null && in_array($modules[$dirname]['module_id'], $this->target_module))) {
				continue;
			}
			
			$file_path = MODULE_DIR."/".$dirname.'/install.ini';
			if (file_exists($file_path)) {
				if(version_compare(phpversion(), "5.0.0", ">=")){
		        	$initializer =& DIContainerInitializerLocal::getInstance();
		        	$install_ini = $initializer->read_ini_file($file_path, true);
		        } else {
		 	        $install_ini = parse_ini_file($file_path, true);
		        }
		        if(isset($install_ini['CleanUp']) && is_array($install_ini['CleanUp'])) {
		        	// クリーンアップ
		        	$modinfo_ini = $this->modulesView->loadModuleInfo($dirname);
		        	if($modinfo_ini && isset($modinfo_ini["module_name"])) {
		        		$this->_fileCleanUp($modules[$dirname], $modinfo_ini["module_name"], $install_ini['CleanUp']);
		        	}
		        	
		        }
		        
			}
		}
        return 'success';
    }
    
    /**
	 * ファイルクリーンアップ処理
	 * @access	private
	 */
	function _fileCleanUp($module, $module_name, $cleanup_params) 
	{
		$this->ret_module_name_arr[$module['dirname']] = $module_name;
		$this->_setMes($module['dirname'], CLEANUP_MES_RESULT_START);
		$end_error_flag = false;
		$uploads = $this->uploadsView->getUploadByModuleid($module['module_id']);
		$error_flag = false;
		$sum_data_count = 0;
		$file_exists_name_arr = array();
		if(count($uploads) > 0) {
			foreach($uploads as $upload) {
				if($upload['update_time'] >= date("YmdHis",timezone_date(null, true, "U") - _CLEANUP_DEL_DAY*60*60)) {
					// ファイルが使用されていたものを配列に格納
					$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] . $upload['physical_file_name']] =  true;
					$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] .$upload['upload_id']."_thumbnail.".$upload['extension']] = true;
					$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] .$upload['upload_id']."_mobile_".MOBILE_IMGDSP_SIZE_240 . "." . $upload['extension']] = true;
					$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] .$upload['upload_id']."_mobile_".MOBILE_IMGDSP_SIZE_480 . "." . $upload['extension']] = true;
					continue;	
				}
				// ファイルが存在しなくてもチェック
				//$file_full_path = FILEUPLOADS_DIR.$upload['file_path'].$upload['physical_file_name'];
				//if(file_exists($file_full_path)){
					//$where_sql = " WHERE 1!=1 ";
					$data_count = 1;
					$error_flag = false;
					foreach($cleanup_params as $table_name => $column_name_list) {
						$where_sql = " WHERE 1!=1 ";
						$column_name_arr = explode(",", $column_name_list);
						foreach($column_name_arr as $column_name) {
				        	if($column_name == "") continue;
				        	if(preg_match("/upload_id/i", $column_name)) {
				        		// カラム名に「upload_id」が含まれている
				        		$where_sql .= " OR {".$table_name."}.".$column_name . " = ".$upload['upload_id'];
				        	} else {
				        		$url = $upload['action_name'] . "&upload_id=".$upload['upload_id'];
				        		$where_sql .= " OR {".$table_name."}.".$column_name. " LIKE '%".$url."%'";
				        		$url = $upload['action_name'] . "&amp;upload_id=".$upload['upload_id'];
				        		$where_sql .= " OR {".$table_name."}.".$column_name. " LIKE '%".$url."%'";
				        	}
				        }
				        $sql = "SELECT COUNT(*) FROM {".$table_name."} ".$where_sql;
						$result = $this->db->execute($sql, null, null, null, false);
						if ($result === false) {
							$error_flag = true;
							//$this->db->addError();
							//return 'error';
						}
						$data_count = $result[0][0];
						if($data_count > 0) {
							//$sum_data_count++;
							break;	
						}
					}
					
					if($error_flag == false && $data_count == 0) {
						// ファイル削除
						$sum_data_count++;
						if($this->uploadsAction->delUploadsById($upload['upload_id'])) {
							$this->_setMes($module['dirname'], sprintf(CLEANUP_MES_DELETE_END, $upload['file_name']), 1);
						} else {
							// エラー
						  $error_flag = true;
						}
					} else {
						// ファイルが使用されていたものを配列に格納
						$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] . $upload['physical_file_name']] =  true;
						$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] . $upload['upload_id']."_thumbnail.".$upload['extension']] = true;
						$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] . $upload['upload_id']."_mobile_" . MOBILE_IMGDSP_SIZE_240 . "." .$upload['extension']] = true;
						$file_exists_name_arr[FILEUPLOADS_DIR.$upload['file_path'] . $upload['upload_id']."_mobile_" . MOBILE_IMGDSP_SIZE_480 . "." .$upload['extension']] = true;
					}
					if($error_flag) {
						$this->_setMes($module['dirname'], sprintf(CLEANUP_MES_DELETE_ER, $upload['file_name']), 1);
						$end_error_flag = true;
					}
				//}
			}
		}
		if($error_flag == false) {
			if(file_exists(FILEUPLOADS_DIR.$module['dirname']."/")){
				$this->_delUploadsFile(FILEUPLOADS_DIR.$module['dirname'], $module['dirname'], $sum_data_count, $file_exists_name_arr);
			}
		}
		if($error_flag == false && $sum_data_count == 0) {
			$this->_setMes($module['dirname'], CLEANUP_MES_NONEXISTS, 1);	
		}
		if(!$end_error_flag) {
			$this->_setMes($module['dirname'], CLEANUP_MES_RESULT_END);
		} else {
			$this->_setMes($module['dirname'], CLEANUP_MES_RESULT_ERROR);
		}
	}
	
	/**
	 * メッセージセット
	 * @access	private
	 */
    function _setMes($dirname, $mes, $tabLine=0) {
    	if(empty($this->ret_str_arr[$dirname])) $this->ret_str_arr[$dirname] = "";
    	$this->ret_str_arr[$dirname] .= $this->modulesView->getShowMes($mes, $tabLine + 1);
    }
     
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function _fetchcallbackModules($result, $func_param) 
	{
		$ret = array();
		while ($row = $result->fetchRow()) {
			$pathList = explode("_", $row["action_name"]);
			$row['dirname'] = $pathList[0];
			$ret[$pathList[0]] = $row;
		}
		return $ret;
	}
	
	//
	// uploadsテーブルになく、ファイルだけあるものを削除
	//
	function _delUploadsFile($path, $dirname, &$sum_data_count, &$file_exists_name_arr) {
		// ファイルまたはディレクトリの削除
		if ( is_dir($path) ) { 
			$handle = opendir($path);
			while ( false !== ($file = readdir($handle)) ) {
				if ( $file == '.' || $file == '..' ) { continue; }
				if($dirname == "") {
					$dirname = $file;
				}
				$this->_delUploadsFile($path. "/". $file, $dirname, $sum_data_count, $file_exists_name_arr);
			} 
			closedir($handle);
		} else {
			if(!isset($file_exists_name_arr[$path])) {
				@chmod($path, 0777);
				unlink($path);
				$sum_data_count++;
				$pathList = explode("/" , $path);
				//$dirname = $pathList[count($pathList) - 2];
				$filename = $pathList[count($pathList) - 1];
				$this->_setMes($dirname, sprintf(CLEANUP_MES_DELETE_END, $filename), 1);
			} 
		}
	}
}
?>
