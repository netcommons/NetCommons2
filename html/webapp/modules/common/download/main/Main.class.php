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
class Common_Download_Main extends Action
{
	// リクエストパラメータを受け取るため
	var $upload_id = null;
	var $thumbnail_flag = null;
	var $w = null;
	var $h = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsView = null;
	
    /**
     * ダウンロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {
		if( $this->w != null || $this->h != null ) {
			$resize = array( $this->w, $this->h );
		}
		else {
			$resize = null;
		}
    	list($pathname,$filename,$physical_file_name, $cache_flag) = $this->uploadsView->downloadCheck($this->upload_id, null, $this->thumbnail_flag, null, $resize);
    	clearstatcache();
    	if($pathname != null) {
    		$this->uploadsView->headerOutput($pathname, $filename, $physical_file_name, $cache_flag);
    	}
    	
        //return 'success';
    }
}
?>
