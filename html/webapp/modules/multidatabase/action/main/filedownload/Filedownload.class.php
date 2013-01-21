<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルダウンロード
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Filedownload extends Action
{
    // リクエストパラメータを受け取るため
    var $download_flag = null;
	var	$w = null;	// AllCreator
	var	$h = null;	// AllCreator
    
    // バリデートによりセット
    var $file = null;
    var $mdbAction = null;
    var $uploadsView = null;

    // 使用コンポーネントを受け取るため
	var $actionChain = null;
 
    // 値をセットするため
    
    /**
     * [[機能説明]]
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
    	if($this->download_flag == _ON) {
    		$this->mdbAction->setDownloadCount($this->file['upload_id']);
			list($pathname,$filename,$physical_file_name, $cache_flag) = $this->uploadsView->downloadCheck($this->file['upload_id'], null, 0, null, $resize);	//AllCreator
			clearstatcache();
			if($pathname != null) {
			$this->uploadsView->headerOutput($pathname, $filename, $physical_file_name, $cache_flag);
			}
			exit;
    	}
    	return 'success';
    }
}
?>