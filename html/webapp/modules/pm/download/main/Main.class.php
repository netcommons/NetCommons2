<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ダウンロードメイン表示クラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Download_Main extends Action
{
	// リクエストパラメータを受け取るため
	var $upload_id = null;
	var $thumbnail_flag = null;
	var $module_id = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsView = null;
	var $pmView = null;
	
    /**
     * ダウンロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {
    	$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$user_id = $session->getParameter("_user_id");
		
		if(!$user_id){
			return "error";
		}
		
		$message = $this->pmView->getUploadMessage($this->upload_id);
		if($message != false && $message["send_all_flag"] == _ON){
		}else{
			$owner = &$this->pmView->getUploadOwner($this->upload_id);
			if($user_id != $owner){
				$readers = &$this->pmView->getUploadReaders($this->upload_id);
				if(!in_array($user_id, $readers)){
					return "error";
				}
			}
		}
		
		list($pathname,$filename,$physical_file_name,$cache_flag) = 
			$this->uploadsView->downloadCheck($this->upload_id, null, $this->thumbnail_flag);
    	clearstatcache();
    	if($pathname != null) {
    		$cache_flag = false;
    		$this->uploadsView->headerOutput($pathname, $filename, $physical_file_name, $cache_flag);
    	}
    	exit;
        //return 'success';
    }
}
?>
