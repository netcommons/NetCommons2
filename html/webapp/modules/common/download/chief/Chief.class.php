<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ダウンロード表示クラス(主担以上のルーム権限だとダウンロード可能)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Download_Chief extends Action
{
	// リクエストパラメータを受け取るため
	var $upload_id = null;
	var $thumbnail_flag = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsView = null;
	
    /**
     * ダウンロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {
    	list($pathname,$filename,$physical_file_name,$cache_flag) = $this->uploadsView->downloadCheck($this->upload_id, _AUTH_CHIEF, $this->thumbnail_flag);
    	clearstatcache();
    	if($pathname != null) {
    		$this->uploadsView->headerOutput($pathname, $filename, $physical_file_name,false);
    	}
    	
        //return 'success';
    }
}
?>
