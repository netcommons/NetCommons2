<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* バックアップ画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
    var $detail_flag = null;
   
	// Filterによりセット
    var $room_list = null;
    var $room_arr_flat = null;
    
    // 使用コンポーネントを受け取るため
    var $session = null;
    var $fileView = null;
    var $db = null;
    var $authoritiesView = null;
    var $pagesView = null;

    // 値をセットするため
    var $backup_files = array();
    var $maxNum = 0;
    var $authority = null;
    var $check_flag = true;

    /**
     * バックアップ画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$backup_files_count = 0;
    	//-------------------------------------------------------------------------------
    	// 現在、処理中のもの取得
    	//-------------------------------------------------------------------------------
    	$temp_dir_path = FILEUPLOADS_DIR."backup/".BACKUP_TEMPORARY_DIR_NAME."/".BACKUP_BACKUP_DIR_NAME. "/";
    	
    	$user_auth_id = $this->session->getParameter("_user_auth_id");
    	$site_id = $this->session->getParameter("_site_id");
    	
    	$auth_pages = $this->pagesView->getRoomIdByUserId();
    	if($auth_pages=== false) {
    		return 'error';	
    	}
    	if(count($auth_pages) == 0) {
    		$this->check_flag= false;	
    	}
    	
    	/*
    	$dir_list = $this->fileView->getCurrentDir($temp_dir_path);
    	// Backupテンポラリーの一覧とuploads/backup下の一覧を表示
    	// 権限によって表示させる項目を変更すること
    	
    	if(is_array($dir_list)) {
			foreach($dir_list as $dir_name) {
				if(isset($this->room_arr_flat[intval($dir_name)]) &&
					($user_auth_id == _AUTH_ADMIN || $this->room_arr_flat[intval($dir_name)]['private_flag'] == _ON)
					) {
					// 管理者ならばプライベートスペース以外のすべてのバックアップファイル表示
					// それ以外ならば、プライベートスペースのみ表示
					$this->backup_files[$backup_files_count]['file_name'] = $this->room_arr_flat[intval($dir_name)]['page_name']. "." .BACKUP_COMPRESS_EXT;
					$this->backup_files[$backup_files_count]['state'] = _OFF;	// 処理中
					$this->backup_files[$backup_files_count]['size'] = $this->fileView->formatSize($this->fileView->getSize($temp_dir_path.$dir_name));
					$this->backup_files[$backup_files_count]['create_time'] = timezone_date(date("YmdHis", filemtime($temp_dir_path.$dir_name)), true);
					$this->backup_files[$backup_files_count]['room_backup_flag'] = true;
					$this->backup_files[$backup_files_count]['upload_id'] = _OFF;
					$this->backup_files[$backup_files_count]['page_id'] = intval($dir_name);
					
					$backup_files_count++;
				} else if($dir_name == "full" && $user_auth_id == _AUTH_ADMIN) {
					$this->backup_files[$backup_files_count]['file_name'] = BACKUP_FULL_FILE_NAME."." .BACKUP_COMPRESS_EXT;
					$this->backup_files[$backup_files_count]['state'] = _OFF;	// 処理中
					$this->backup_files[$backup_files_count]['size'] = $this->fileView->formatSize($this->fileView->getSize($temp_dir_path.$dir_name));
					$this->backup_files[$backup_files_count]['create_time'] = timezone_date(date("YmdHis", filemtime($temp_dir_path.$dir_name)), true);
					$this->backup_files[$backup_files_count]['room_backup_flag'] = false;
					$this->backup_files[$backup_files_count]['upload_id'] = _OFF;
					$this->backup_files[$backup_files_count]['page_id'] = 0;
					$backup_files_count++;
				}
			}
		}
		*/
		//-------------------------------------------------------------------------------
    	// バックアップファイル一覧取得
    	// 
    	// 一覧を見ている会員で検索
    	// パブリックスペース直下は、パブリックスペースで主担であれば表示する
    	// 「パブリックスペース内にルームの作成を許可する」ならばパブリックスペースの内のルームのバックアップを表示する
    	//      (かつ、自サイトのルームならば、そのルームの主担かどうかもチェック)
    	// 「パブリックスペース内にルームの作成を許可する」ならばグループルームのバックアップを表示する
    	//      (かつ、自サイトのルームならば、そのルームの主担かどうかもチェック
    	// サブグループならば、その親のルームの主担であればできる
    	// プライベートスペースは、自分のもののみ表示
    	// （よって、一般はプライベートスペースのみバックアップを行える）
    	//-------------------------------------------------------------------------------
    	////$uploads = $this->db->selectExecute("uploads", array("module_id" => $this->module_id), array("insert_time" => "DESC"));
		$backup_uploads = $this->_getBackupUploads();
		
		if(is_array($backup_uploads)) {
			foreach($backup_uploads as $upload) {
				
				//if((isset($this->room_arr_flat[intval($upload['room_id'])]) || $upload['room_id'] == 0) &&
					//($user_auth_id == _AUTH_ADMIN || $this->room_arr_flat[intval($upload['room_id'])]['private_flag'] == _ON)
					//) {
					// 管理者ならばプライベートスペース以外のすべてのバックアップファイル表示
					// それ以外ならば、プライベートスペースのみ表示
					//if($upload['room_id'] != 0) {
					//	$this->backup_files[$backup_files_count]['file_name'] = $this->room_arr_flat[intval($upload['room_id'])]['page_name']."." .BACKUP_COMPRESS_EXT;
					//} else {
					//	$this->backup_files[$backup_files_count]['file_name'] = $upload['file_name'];
					//}
				
				$list_show_flag = false;
				if($upload['room_id'] == 0 && $upload['url'] != '' &&
					($user_auth_id == _AUTH_ADMIN || $upload['insert_user_id'] == $this->session->getParameter("_user_id"))) {
					// 他サイト　かつ、
					$list_show_flag = true;
				} else if($upload['room_id'] == 0) {
					// フルバックアップ
					if($user_auth_id == _AUTH_ADMIN) {
						$list_show_flag = true;
					}
				} else if($user_auth_id == _AUTH_ADMIN) {
					$list_show_flag = true;
				} else if($upload['thread_num'] == 0 && $upload['space_type'] == _SPACE_TYPE_PUBLIC) {
					// パブリックスペース直下
					if(isset($this->room_arr_flat[intval($upload['room_id'])]) && 
						$this->room_arr_flat[intval($upload['room_id'])]['authority_id'] >= _AUTH_CHIEF
						&& $user_auth_id == _AUTH_ADMIN) {
						// ルームの主担かどうか
						$list_show_flag = true;
					}
				} else if($upload['thread_num'] == 2) {
					// サブグループ
					if(isset($this->room_arr_flat[intval($upload['parent_id'])]) && 
						$this->room_arr_flat[intval($upload['parent_id'])]['authority_id'] >= _AUTH_CHIEF
						&& $this->room_arr_flat[intval($upload['parent_id'])]['createroom_flag'] == _ON) {
						// ルームの主担かどうか
						$list_show_flag = true;
					}
				} else {
					// グループルーム　パブリックルーム
					if($upload['site_id'] == $site_id) {
						// 自サイト
						if(isset($this->room_arr_flat[intval($upload['room_id'])])) {
							if($this->room_arr_flat[intval($upload['room_id'])]['authority_id'] >= _AUTH_CHIEF) {
								// そのルームの主担かどうかもチェック
								$list_show_flag = true;
							}
						} else {
							// room_arr_flatでは、表示可能なページを取得してきているので
							// 存在しないかどうか再チェック
							$page = $this->pagesView->getPageById(intval($upload['room_id']));
							if($page === false || !isset($page['page_id'])) {
								// 自サイトだが、存在していないルームならば無条件で表示
								$list_show_flag = true;
							}
						}
					} else {
						// 他サイトの場合、sitesテーブルに登録されているかどうかチェック
						// 既にアップロードされているのでスルー
						if($upload['private_flag'] == _ON) {
							if($upload['insert_user_id'] == $this->session->getParameter("_user_id")) {
								$list_show_flag = true;
							}
						} else {
							$list_show_flag = true;
						}
					}
				}
				//if((isset($this->room_arr_flat[intval($upload['room_id'])]) || $upload['room_id'] == 0) &&
				//	($this->room_arr_flat[intval($upload['room_id'])]['authority_id'] >= _AUTH_CHIEF)
				//  ) {
				if($list_show_flag) {
					$this->backup_files[$backup_files_count]['file_name'] = $upload['file_name'];
					
					if($upload['garbage_flag'] == _OFF) {
						$this->backup_files[$backup_files_count]['state'] = _ON;	// 処理済
					} else if($upload['garbage_flag'] == _ON) {
						$this->backup_files[$backup_files_count]['state'] = _OFF;	// 処理中
					} else {
						$this->backup_files[$backup_files_count]['state'] = 2;		// 処理失敗
					}
					if($upload['garbage_flag'] == _OFF) {
						$this->backup_files[$backup_files_count]['size'] = $this->fileView->formatSize($upload['file_size']);
					} else {
						// バックアップで処理中
						$target_file = FILEUPLOADS_DIR."backup/".$upload['physical_file_name'];
						if(file_exists($target_file)) {
							$this->backup_files[$backup_files_count]['size'] = $this->fileView->formatSize($this->fileView->getSize($target_file));
						} else {
							$this->backup_files[$backup_files_count]['size'] = "0K";
						}
					}
					$this->backup_files[$backup_files_count]['create_time'] = $upload['update_time'];
					$this->backup_files[$backup_files_count]['room_backup_flag'] = ($upload['room_id'] == 0 && ($upload['site_id'] == $site_id)) ? false : true;
					$this->backup_files[$backup_files_count]['upload_id'] = $upload['upload_id'];
					$this->backup_files[$backup_files_count]['page_id'] = intval($upload['room_id']);
					$this->backup_files[$backup_files_count]['insert_user_name'] = $upload['insert_user_name'];
					
					$backup_files_count++;
				}
			}
		}

    	$this->maxNum = $backup_files_count;
    	if($this->detail_flag) {
    		return 'success_detail';
    	} 
    	return 'success';
    }
    
    /**
	 * backup_uploads取得
	 * @access	private
	 */
	function &_getBackupUploads()
	{
		$user_id = $this->session->getParameter("_user_id");
		$role_auth_id = $this->session->getParameter("_role_auth_id");
		$this->authority = $this->authoritiesView->getAuthorityById($role_auth_id);
		
		$sql = "SELECT {backup_uploads}.*,{uploads}.file_name,{uploads}.physical_file_name,{uploads}.file_path,{uploads}.action_name,{uploads}.file_size,{uploads}.garbage_flag".
				" FROM {backup_uploads} INNER JOIN {uploads} ON ({backup_uploads}.upload_id={uploads}.upload_id)";
		$sql .= " WHERE 1=1";
		$where_str = " AND ({backup_uploads}.private_flag="._OFF." OR {backup_uploads}.insert_user_id='" . $user_id . "') ";
		if($this->authority['public_createroom_flag'] == _OFF) {
    		// パブリックスペース
    		$where_str .= " AND ({backup_uploads}.space_type != "._SPACE_TYPE_PUBLIC.") " ;
    	}
    	if($this->authority['group_createroom_flag'] == _OFF) {
    		// グループスペース以外か、プライベートスペース
    		$where_str .= " AND (!({backup_uploads}.space_type = "._SPACE_TYPE_GROUP." AND {backup_uploads}.thread_num = 1) OR {backup_uploads}.private_flag="._ON." ) " ;
    	}
    	//$where_params = array(
    	//	"private_flag="._OFF." OR insert_user_id=" . $user_id => null
    	//);
    	$order_str = "ORDER BY {backup_uploads}.insert_time DESC";
    	
		$result = $this->db->execute($sql.$where_str.$order_str, array());
		if($result === false) {
			$this->db->addError();
			return $result;
		}
		return $result;
	}
}
?>