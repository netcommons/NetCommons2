<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * バックアップサイズ取得
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_View_Main_Getsize extends Action
{
    // リクエストパラメータを受け取るため
    var $chk_upload_id = null;
 
    // 使用コンポーネントを受け取るため
    var $session = null;
    var $fileView = null;
    var $db = null;
    var $response = null;
    //var $uploadsAction = null;
    var $pagesView = null;

    // 値をセットするため
    var $comp_mes = "comp_mes:";
 
    /**
     * バックアップサイズ取得
     *
     * @access  public
     */
    function execute()
    {
    	$upload = $this->db->selectExecute("uploads", array("upload_id" => $this->chk_upload_id));
    	if($upload === false) return;
    	
    	$contentDisposition = $this->response->getContentDisposition();
		$contentType        = $this->response->getContentType();
		if ($contentDisposition != "") {
			header("Content-disposition: ${contentDisposition}");
		}
		if ($contentType != "") {
			header("Content-type: ${contentType}");
		}
		if(isset($upload[0])) {
    		$user_auth_id = $this->session->getParameter("_user_auth_id");
    	
	    	$room_id = $upload[0]['room_id'];
	    	if($room_id == 0) {
	    		if($user_auth_id != _AUTH_ADMIN) return;
	    	} else {
		    	$page = $this->pagesView->getPageById($room_id);
		    	if($page === false || !isset($page['page_id']) || $page['page_id'] != $page['room_id'] || $page['authority_id'] < _AUTH_CHIEF) {
		    		return;
		    	}
	    	}
    		if($upload[0]['garbage_flag'] == _OFF) {
    			// 処理済
    			//$this->session->removeParameter(array("backup", "chksize","count",$this->chk_upload_id));
				//$this->session->removeParameter(array("backup", "chksize","size",$this->chk_upload_id));
				$upload_flag = $this->session->getParameter(array("backup", "backingup", $this->chk_upload_id));
		    	if(isset($upload_flag) && $upload_flag == _ON) {
		    		// 完了通知メッセージをまだ出力していない
		    		$this->session->removeParameter(array("backup", "backingup", $this->chk_upload_id));		
		    		print $this->comp_mes . BACKUP_END_MES;
		    	} else {
					print $this->comp_mes;
		    	}
    			//print $this->fileView->formatSize($upload[0]['file_size']);
    		} else {
    			$target_file = WEBAPP_DIR."/templates_c/".BACKUP_BACKUP_DIR_NAME."/".$upload[0]['upload_id']."/";
    			if (is_dir( $target_file ) && $handle = opendir($target_file)) {
    				$file = "";
					while (false !== ($file = readdir($handle))) {
						if ($file == "." || $file == ".." || $file == "BACKUP_FULL_SQL_FILE_NAME") continue;
						$file = $target_file."/".$file;
						break;
					}
					closedir($handle);
					$target_file = $file;
				}
    			
    			//$target_file = FILEUPLOADS_DIR."backup/".$upload[0]['physical_file_name'];
    			
				if(file_exists($target_file)) {
					$size = $this->fileView->getSize($target_file);
					/*
					$sess_count = $this->session->getParameter(array("backup", "chksize","count",$this->chk_upload_id));
					$sess_size = $this->session->getParameter(array("backup", "chksize","size",$this->chk_upload_id));
					if(!isset($sess_count) || $sess_count < 0) {
						$sess_count = 0;
						$sess_size = 0;
					}
					if($sess_size == $size) {
						$sess_count++;
					}
					if($sess_count == BACKUP_CHKSIZE_COUNT) {
						// ３回、同セッションで同じサイズならば、バックアップ失敗とみなす
						// garbage_flag=2を立てておく
						$params = array(
							'file_size' => $sess_size,
							'garbage_flag'     => 2
						);
						$where_params = array(
							"upload_id" => $this->chk_upload_id
						);
						$result = $this->uploadsAction->updUploads($params, $where_params);
						if($result === false) {
							// 処理なし
						}
						print $this->comp_mes;
						return;
					}
					$this->session->setParameter(array("backup", "chksize","count",$this->chk_upload_id), $sess_count);
					$this->session->setParameter(array("backup", "chksize","size",$this->chk_upload_id), $size);
					*/
					print $this->fileView->formatSize($size, 1, 1);
				} else {
					print "0K";
				}
    		}
    	} else {
    		print "0K";
    	}
    	
    	return;		// 'success';
    }
}
?>