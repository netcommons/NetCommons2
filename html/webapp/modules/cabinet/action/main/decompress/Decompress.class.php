<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

include_once MAPLE_DIR.'/includes/pear/File/Archive.php';

/**
 * 解凍アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Action_Main_Decompress extends Action
{
	// 使用コンポーネントを受け取るため
 	var $cabinetView = null;
 	var $cabinetAction = null;
	var $fileAction = null;

    // validatorから受け取るため
    var $file = null;
 	var $file_path = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		if (stristr($_SERVER['HTTP_USER_AGENT'], "Mac")) {
			// Macの場合
			$this->cabinetAction->encode = "UTF-8";
		} else if (stristr($_SERVER['HTTP_USER_AGENT'], "Windows")) {
			// Windowsの場合
			$this->cabinetAction->encode = "SJIS";
		} else {
			$this->cabinetAction->encode = _CHARSET;
		}
		
		$decompress_new_folder = $this->cabinetView->getDecompressNewFolder();
		
		if ($decompress_new_folder == _ON) {
			$params = array(
				"upload_id" => 0,
				"parent_id" => $this->file["parent_id"],
				"file_name" => $this->cabinetView->renameFile($this->file["org_file_name"], ""),
				"extension" => "",
				"depth" => $this->file["depth"],
				"size" => 0,
				"file_type" => CABINET_FILETYPE_FOLDER,
			);
			$file_id = $this->cabinetAction->execDecompress($params);
	    	if ($file_id === false) {
	    		return 'error';
	    	}
	    	$result = $this->cabinetAction->decompressFile(FILEUPLOADS_DIR.$this->file_path, $file_id, $this->file["depth"]+1);
		} else {
	    	$result = $this->cabinetAction->decompressFile(FILEUPLOADS_DIR.$this->file_path, $this->file["parent_id"], $this->file["depth"], _ON);
		}

    	$this->fileAction->delDir(FILEUPLOADS_DIR.$this->file_path);
    	if ($result === false) {
    		return 'error';
    	}
    	return 'success';
    }
}
?>