<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* バックアップファイル削除処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Action_Main_Delete extends Action
{
    // リクエストパラメータを受け取るため
    var $upload_id = null;
    var $backup_page_id = null;
    var $module_id = null;
    
    // バリデートによりセット
    var $page = null;

    // 使用コンポーネントを受け取るため
    var $fileAction = null;
    var $db = null;
    //var $uploadsView = null;
    var $uploadsAction = null;
    
    // 値をセットするため
    
    /**
     * バックアップファイル削除処理
     *
     * @access  public
     */
    function execute()
    {
    	$uploads = $this->db->selectExecute("uploads", array("upload_id" => $this->upload_id, "module_id" => $this->module_id));
		if($uploads === false || !isset($uploads[0]) || count($uploads) > 1) return 'error';
		$result = $this->uploadsAction->delUploadsById($this->upload_id);
		if($result === false)  return 'error';
		$result = $this->db->deleteExecute("backup_uploads", array("upload_id" => $this->upload_id));
		if($result === false)  return 'error';
		
		// バックアップファイル作成中のファイル
		if($this->backup_page_id != 0) {
			$temporary_file_path = FILEUPLOADS_DIR."backup/".BACKUP_TEMPORARY_DIR_NAME."/".BACKUP_BACKUP_DIR_NAME. "/". $this->backup_page_id. "/";
		} else {
			$temporary_file_path = WEBAPP_DIR."/templates_c/".BACKUP_BACKUP_DIR_NAME."/";
		}
		// テンポラリーファイル削除
		if(file_exists($temporary_file_path)) {
			$this->fileAction->delDir($temporary_file_path);
		}
        return 'success';
    }
}
?>
