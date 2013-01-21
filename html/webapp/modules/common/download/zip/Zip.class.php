<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * WYSIWYGエディター編集テキストZip形式ダウンロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Download_Zip extends Action
{
	// リクエストパラメータを受け取るため
	var $content = null;
	var $download_action = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsView = null;
	
    /**
     * ダウンロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {

		$header = <<<EOD
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>NetCommons</title>
</head>
<body>

EOD;
		$footer = <<<EOD
</body>
</html>
EOD;
		
		require_once "File/Archive.php";
		$dest = File_Archive::toArchive("document.zip", File_Archive::toOutput());
		$dest->newFile("document.html");
		
		$download_url = "?action=".$this->download_action."&upload_id=";
		$count = substr_count($this->content, $download_url);
		if (!$count) {
			$dest->writeData($header.$this->content.$footer);
			$dest->close();
			exit;
		}
		
		$upload_id = array();
		$files = array();
		$trans = array();
		$parts = explode($download_url, $this->content);
		for ($i = 1; $i <= $count; $i++) {
			$id = substr($parts[$i], 0, strpos($parts[$i], '"'));			
			if (!isset($upload_id[$id])) {
				$upload_id[$id] = true;
				list($pathname, $filename,$physical_file_name, $space_type) = $this->uploadsView->downloadCheck($id, null, 0, "common_download_main");
				if ($pathname != null) {
					$files[] = $pathname.$physical_file_name;
					$trans[BASE_URL.'/'.$download_url.$id] = "./".$physical_file_name;
					$trans[$download_url.$id] = $physical_file_name;
				}
			}
		}
		clearstatcache();
		
		$dest->writeData($header.strtr($this->content, $trans).$footer);
		File_Archive::extract($files, $dest);
	}
}
?>
