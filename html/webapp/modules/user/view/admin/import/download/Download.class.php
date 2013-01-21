<?php

/**
 * 会員管理>>インポート>>フォーマットファイルのダウンロード
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Import_Download extends Action
{
    // 使用コンポーネントを受け取るため
    var $db = null;
	var $csvmain = null;
    
    // 値をセットするため
    var $formatfilename = "usr_importfile";

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$header_name = $this->csvmain->make_header();
    
		/* CSVファイル作成 */
    	if (isset($header_name)) {
	    	$this->csvmain->add($header_name, $header_name);
	    	$this->csvmain->download($this->formatfilename);
    	} else {
			return 'error';
    	}
    	
		return 'success';
    }
}
?>