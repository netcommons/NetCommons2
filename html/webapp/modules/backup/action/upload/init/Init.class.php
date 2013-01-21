<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * バックアップファイルアップロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Action_Upload_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;
	var $fileAction = null;
	var $backupRestore = null;
	var $db = null;
	var $actionChain = null;
	var $pagesView = null;
	var $session = null;
	var $authoritiesView = null;
	
	//値をセットするため
	var $_upload_id = 0;
	
	/**
     * アップロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {
    	$errorList =& $this->actionChain->getCurErrorList();
    	$user_id = $this->session->getParameter("_user_id");
    	$site_id = $this->session->getParameter("_site_id");
    	$role_auth_id = $this->session->getParameter("_role_auth_id");
    	$authority = $this->authoritiesView->getAuthorityById($role_auth_id);
    	
    	$filelist = $this->uploadsAction->uploads();
    	// $site_id = $this->session->getParameter("_site_id");
    	// 圧縮してあるファイルを解凍し、site_id,parent_id,thread_num,space_type,private_flag,room_idを取得する
    	if(isset($filelist[0])) {
    		$this->_upload_id = $filelist[0]['upload_id'];
    		$add_tmp_dirname = "add_".$this->_upload_id;
    		$temporary_file_path = FILEUPLOADS_DIR."backup/".BACKUP_TEMPORARY_DIR_NAME."/".BACKUP_RESTORE_DIR_NAME."/" . $add_tmp_dirname. "/";
			$this->backupRestore->mkdirTemporary(BACKUP_RESTORE_DIR_NAME);
			
	    	$ret = $this->backupRestore->getRestoreArray($this->_upload_id, 0, $this->module_id, $temporary_file_path);
	    	$this->fileAction->delDir($temporary_file_path);
	    	if($ret === false) {
	    		$this->_delUploads();
	    		return 'error';
	    	}
	    	list($room_inf, $restore_modules, $version_arr, $modules) = $ret;
			$params = array(
				'upload_id'           => $this->_upload_id,
	            'site_id'             => $room_inf['site_id'],
	            'url'                  => $room_inf['url'],
	            'parent_id'          => $room_inf['parent_id'],
	            'thread_num'          => $room_inf['thread_num'],
	            'space_type'          => $room_inf['space_type'],
	            'private_flag'        => $room_inf['private_flag'],
	            'room_id'             => $room_inf['room_id']
	        );
	        // 権限チェック
	        if($restore_modules["system"]['self_flag'] && $room_inf['site_id'] != $site_id) {
	        	// 他サイトの場合、sitesテーブルに登録されているかどうかチェック
	        	// エラーとする
	        	$errorList->add("backup", BACKUP_INVALID_AUTH);
		        $this->_delUploads();
		        return 'error';	
	        }
	        if($restore_modules["system"]['self_flag'] || ($room_inf['private_flag'] == _OFF && $room_inf['thread_num'] == 0)) {
		        $page = $this->pagesView->getPageById($room_inf['room_id']);
		        if($page === false) {
		        	$this->_delUploads();
		        	return 'error';	
		        }
	        } else {
	        	$page = array();
	        }
	        
        	if($room_inf['private_flag'] == _ON && $room_inf['space_type'] == _SPACE_TYPE_GROUP) {
				// プライベートスペース
				// 他サイトからならば、チェックしない
				if($restore_modules["system"]['self_flag']) {
					$buf_page_private =& $this->pagesView->getPrivateSpaceByUserId($user_id, 0, 0, false);
					if($buf_page_private === false) return 'error';
					if($room_inf['default_entry_flag'] == _ON && isset($buf_page_private[1])) {
			    		$index_count = 1;
			    	} else {
			    		$index_count = 0;
			    	}
					if($room_inf['room_id'] != $buf_page_private[$index_count]['page_id']) {
						$errorList->add("backup", BACKUP_INVALID_AUTH);
		        		$this->_delUploads();
		        		return 'error';	
					}
				}
			} else if($room_inf['space_type'] == _SPACE_TYPE_PUBLIC && $authority['public_createroom_flag'] == _OFF) {
	    		// パブリックスペースでルーム作成権限なし
	    		$errorList->add("backup", BACKUP_INVALID_AUTH);
	        	$this->_delUploads();
	        	return 'error';	
	    	} else if($room_inf['space_type'] == _SPACE_TYPE_GROUP && $authority['group_createroom_flag'] == _OFF) {
	    		// グループスペースでルーム作成権限なし
	    		$errorList->add("backup", BACKUP_INVALID_AUTH);
	        	$this->_delUploads();
	        	return 'error';	
	    	}
	        if(isset($page['page_id'])) {
		        if($page['authority_id'] < _AUTH_CHIEF) {
		        	$errorList->add("backup", BACKUP_INVALID_AUTH);
		        	$this->_delUploads();
		        	return 'error';	
		        }
		        if($page['thread_num'] == 2) {
		        	// サブグループならば、その親のルームも主担
		        	$parent_page = $this->pagesView->getPageById($room_inf['parent_id']);
			        if($parent_page === false) {
			        	$this->_delUploads();
			        	return 'error';	
			        }
			        if($parent_page['authority_id'] < _AUTH_CHIEF) {
			        	$errorList->add("backup", BACKUP_INVALID_AUTH);
			        	$this->_delUploads();
		        		return 'error';	
			        }
		        }
	        }
	        
			$result = $this->db->insertExecute("backup_uploads", $params, true);
			if($result === false) {
				$this->_delUploads();
				return 'error';	
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
	}
}
?>
