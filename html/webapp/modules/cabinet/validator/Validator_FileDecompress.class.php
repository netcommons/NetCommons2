<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルの解凍チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_FileDecompress extends Validator
{
	var $_fileAction = null;
	var $_fileView = null;
	var $_uploadsView = null;

	var $_decompression = array();
	var $_cabinet = null;
	var $_allow_extension = null;

    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$this->_cabinet = $attributes["cabinet"];
		$file = $attributes["file"];

		$container =& DIContainerFactory::getContainer();
        $commonMain =& $container->getComponent("commonMain");
		$this->_fileAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/Action.class.php', "File_Action", "fileAction");
        $this->_fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        
		$this->_uploadsView =& $container->getComponent("uploadsView");

    	$file_path = "cabinet/".strtolower(session_id()).timezone_date();
    	if (file_exists(FILEUPLOADS_DIR.$file_path)) {
    		$result = $this->_fileAction->delDir(FILEUPLOADS_DIR.$file_path);
	        if ($result === false) {
	        	return $errStr;
	        }
    	}
    	mkdir(FILEUPLOADS_DIR.$file_path, octdec(_UPLOAD_FOLDER_MODE));
		
		$request =& $container->getComponent("Request");
		$request->setParameter("file_path", $file_path);
		
 		$result = $this->_uploadsView->getUploadById($file["upload_id"]);
        if ($result === false) {
        	return $errStr;
        }
        $upload = $result[0];

		File_Archive::extract(File_Archive::read(FILEUPLOADS_DIR.$upload["file_path"].$upload["physical_file_name"]."/"), $dest = FILEUPLOADS_DIR.$file_path); 
		
		$configView =& $container->getComponent("configView");
	    $config = $configView->getConfigByConfname(_SYS_CONF_MODID, "allow_extension");
	    if (!isset($config["conf_value"])) {
	    	return $errStr;	
	    }
	    $this->_allow_extension = $config["conf_value"];

        $cabinetView =& $container->getComponent("cabinetView");
		$used_size = $cabinetView->getUsedSize();
		if ($used_size === false) {
			return $errStr;
		}
		
		$total_size = $used_size;
		$result = $this->_check(FILEUPLOADS_DIR.$file_path, $total_size);
		if ($result !== true) {
			$this->_fileAction->delDir(FILEUPLOADS_DIR.$file_path);
			return $result;
		}

		$decompress_size = $this->_fileView->getSize(FILEUPLOADS_DIR.$file_path);

		if ($this->_cabinet["cabinet_max_size"] != 0 && $this->_cabinet["cabinet_max_size"] < $used_size + $decompress_size) {
			$this->_fileAction->delDir(FILEUPLOADS_DIR.$file_path);
			$suffix_compresssize = $this->_fileView->formatSize($used_size+$decompress_size);
			$suffix_maxsize = $this->_fileView->formatSize($this->_cabinet["cabinet_max_size"]);
			return sprintf(CABINET_ERROR_DECOMPRESS_MAX_SIZE, $suffix_compresssize, $suffix_maxsize);
		}

		$result = $cabinetView->checkCapacitySize($decompress_size);
		if ($result !== true) {
			$this->_fileAction->delDir(FILEUPLOADS_DIR.$file_path);
			return $result;
		}
    }

    function _check($path, &$total_size)
    {
    	$handle = opendir($path);
    	if (!$handle) { return CABINET_ERROR_DECOMPRESS; }

		while (false !== ($file_name = readdir($handle))) {
			if ($file_name == "." || $file_name == "..") { continue; }

			$file_path = $path."/".$file_name;
			if (!file_exists($file_path)) { continue; }

			if (is_dir($file_path)) {
				$result = $this->_check($file_path, $total_size);
				if ($result !== true) {
					return $result;
				}
			} else {
				$pathinfo = $this->_uploadsView->checkExtension($file_name, $this->_allow_extension);
				if ($pathinfo === false) { 
					$result = $this->_fileAction->delDir($file_path);
					continue; 
				}
				$file_size = filesize($file_path);
				$total_size += $file_size;
				if ($this->_cabinet["upload_max_size"] != 0 && $this->_cabinet["upload_max_size"] < $file_size) {
					$suffix_maxsize = $this->_fileView->formatSize($this->_cabinet["upload_max_size"]);
					return sprintf(_FILE_UPLOAD_ERR_SIZE, $suffix_maxsize);
				}
			}
		}
		closedir($handle);
    	return true;
    }
}
?>