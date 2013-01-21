<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * JSファイル読み込みアクション(インストール用)
 * common_download_jsは、DB接続がうまくできていないと取得できないため
 * 個別に実装
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */ 
class Install_View_Js extends Action
{
	// リクエストパラメータを受け取るため
	
	// 使用コンポーネントを受け取るため
	
    /**
     * JSファイル読み込みアクション(インストール用)
     *
     * @access  public
     */
    function execute()
    {
    	
    	$filepath = BASE_DIR . "/webapp/modules/common/files/js/prototype.js";
		$handle = fopen($filepath, "r");
		if(!$handle) {
			exit;
		}
		$filesize = filesize($filepath);
		$contents = fread($handle, $filesize);
		fclose($handle);
		$rec_sets[] = $this->_replaceComment($contents);
		
		$filepath = BASE_DIR . "/webapp/modules/common/files/js/common.js";
		$handle = fopen($filepath, "r");
		if(!$handle) {
			exit;
		}
		$filesize = filesize($filepath);
		$contents = fread($handle, $filesize);
		fclose($handle);
		$rec_sets[] = $this->_replaceComment($contents);
		
		$max_update_time = time();
		
		if(is_array($rec_sets)) {
			//if(!defined("_SCRIPT_COMPRESS_GZIP") || _SCRIPT_COMPRESS_GZIP == true) {
			//	ob_start ("ob_gzhandler");
			//	header("Content-Encoding: gzip");
			//}
			header("Content-type: text/javascript; charset=UTF-8");
			header("Cache-Control: cache");
			/*header("Cache-Control: must-revalidate");*/
			
			// 有効期限セット（1時間）
			$offset = 60 * 60;
			$ExpStr = "Expires: " .
			gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
			header($ExpStr);
			
			// 最終更新日付セット
			$timestamp = mktime(substr($max_update_time, 8,2), substr($max_update_time, 10,2), substr($max_update_time, 12,2), substr($max_update_time, 4,2), substr($max_update_time, 6,2), substr($max_update_time, 0,4));
			$ExpStr = "Last-Modified: " .
			gmdate("D, d M Y H:i:s", $timestamp) . " GMT";
			header($ExpStr);
			
			//header("Content-Disposition: inline; filename=install_view_js.js");
			
			foreach($rec_sets as $data) {
				print $data."\n";
			}
			
			exit;
		}
	}
	
	function _replaceComment($data) {
		//コメント除去
		$pattern = array("/^\s+/s", "/\n\s+/s", "/^(\/\/).*?(?=\n)/s", "/\n\/\/.*?(?=\n)/s", "/\s+(\/\/).*?(?=\n)/s", "/^\/\*((.|\n)*?)\*\//s", "/\n\/\*(.*)\*\//Us","/\s+(?=\n)/s");
		$replacement = array ("","\n","","\n","","","","");
		$data = preg_replace($pattern, $replacement, $data);
		return $data;
	}	
}
?>
