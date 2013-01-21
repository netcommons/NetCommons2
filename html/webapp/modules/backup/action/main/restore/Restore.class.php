<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
include_once MAPLE_DIR.'/includes/pear/File/Archive.php';
include_once MAPLE_DIR.'/includes/pear/XML/Unserializer.php';

/**
* バックアップファイル-リストア処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Action_Main_Restore extends Action
{
    // リクエストパラメータを受け取るため
    var $upload_id = null;
    var $backup_page_id = null;
    var $module_id = null;
    
    // バリデートによりセット
    var $page = null;

    // 使用コンポーネントを受け取るため
    var $backupRestore = null;
    var $fileAction = null;
    var $pagesView = null;
	var $configView = null;
    
    // 値をセットするため
    var $maxNum = 0;
    
    var $room_inf = array();
    var $restore_type = false;
    var $restore_modules = null;
    var $tablelist = null;
    var $roomList = null;
    
    var $closesite = 0;
    
    
    /**
     * バックアップファイル-リストア処理
     *
     * @access  public
     */
    function execute()
    {
    	$temporary_file_path = FILEUPLOADS_DIR."backup/".BACKUP_TEMPORARY_DIR_NAME."/".BACKUP_RESTORE_DIR_NAME."/" . $this->backup_page_id. "/";
		$this->backupRestore->mkdirTemporary(BACKUP_RESTORE_DIR_NAME);
    	$ret = $this->backupRestore->getRestoreArray($this->upload_id, $this->backup_page_id, $this->module_id, $temporary_file_path);
    	if($ret === false) {
    		return 'error';
    	}
    	$this->fileAction->delDir($temporary_file_path);
    	list($this->room_inf, $this->restore_modules, $version_arr, $modules) = $ret;
    	$this->restore_type = $this->restore_modules["system"]['restore_type'];

		$this->maxNum = count($this->restore_modules) - 1;

		if($this->restore_type == "subgroup") {
			//
			// グループルームの一覧を取得する
			//
			$where_params = array(
								"space_type" => _SPACE_TYPE_GROUP,
								"private_flag" => _OFF,
								"thread_num" => 1,
								"{pages_users_link}.createroom_flag" => _ON,
								"{authorities}.user_authority_id" => _AUTH_CHIEF
							);
			$order_params = array("display_sequence" => "ASC");
			
			$this->roomList = $this->pagesView->getShowPagesList($where_params, $order_params);
			if($this->roomList === false) {
				$this->fileAction->delDir($temporary_file_path);
	    		return 'error';
	    	}
		}
		
		if(($this->restore_type == "public_room" || $this->restore_type == "top") && $this->room_inf['private_flag'] == _OFF) {
			// パブリックスペースであればサイト閉鎖中かどうかを取得(警告メッセージを表示するため)
			// サイト閉鎖中かどうか
			$closesite = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "closesite");
    		if($closesite === false) {
    			$this->fileAction->delDir($temporary_file_path);
    			return 'error';
    		}
			$this->closesite = $closesite['conf_value'];
		}
		
		////var_dump($this->restore_modules);
		////var_dump ($data);
		//$this->fileAction->delDir($temporary_file_path);
		
        return 'success';
    }
}
?>
