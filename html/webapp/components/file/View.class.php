<?php
 /**
 * 共通ファイル操作クラス(View)
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class File_View {
	var $_className = "File_View";
	
	/**
	 * ファイルサイズ取得
	 * @param  string	$path	対象パス
	 * @return int     ファイルサイズ (失敗：false)
	 **/
	function getSize($path){
	    if (!is_dir($path))
	        return filesize($path);
	    $size=0;
	    $handle = opendir($path);
		while ( false !== ($file = readdir($handle)) ) {
	        if ($file=='.' or $file=='..')
	            continue;
	        if ( $file == 'CVS' || strtolower($file) == '.svn') { continue; }
	    	$size+=$this->getSize($path.'/'.$file);
	  	}
	  	closedir($handle);
		return $size;
	}
	
	
	/**
	 * 指定パスにあるディレクトリ一覧を返す関数
	 * 
	 * @param  string	$path	対象パス
	 * @return	array	array:正常,false:異常
	 **/
	function getCurrentDir($path) {
		if ( is_dir($path) ) { 
			$dir_list = array();
			$handle = opendir($path);
			while ( false !== ($file = readdir($handle)) ) {
				if ( $file == '.' || $file == '..' ) { continue; }
				if ( $file == 'CVS' || strtolower($file) == '.svn') { continue; }
				if ( is_dir($path. "/". $file) ) { 
					$dir_list[] = $file;
				}
			} 
			closedir($handle);
			return $dir_list;
		} else {
			return false; 
		}
	}
	
	/**
	 * 指定パスにあるファイル一覧を返す関数
	 * 
	 * @param  string	$path	対象パス
	 * @return	array	array:正常,false:異常
	 **/
	function getCurrentFiles($path) {
		if ( is_dir($path) ) { 
			$files_list = array();
			$handle = opendir($path);
			while ( false !== ($file = readdir($handle)) ) {
				if ( $file == '.' || $file == '..' ) { continue; }
				if ( $file == 'CVS' || strtolower($file) == '.svn') { continue; }
				if ( !is_dir($path. "/". $file) ) { 
					$files_list[] = $file;
				}
			} 
			closedir($handle);
			return $files_list;
		} else {
			return false; 
		}
	}
	
	/**
	 * 単位付きのサイズに変換し返す
	 * @param float $size(size)
	 * @param int $precision(小数点いくつまで表示するか（default:小数点１位まで）)
	 * @return string 
	 * @access	public
	 */
	function formatSize($size, $precision=1) {
		$UnitArray = array("", "K", "M", "G", "T");
		//$UnitArray = array("", "Ki", "Mi", "Gi", "Ti");
			
	    $Byte = 1024;
	    foreach ($UnitArray as $val) {
	        if ($size < $Byte) break;
	        $size = $size / $Byte;
	    }
	
	    if ($size < 100 && $val != $UnitArray[0]) {
	        return round($size, $precision). $val;
	    } else {
	        return round($size). $val;
	    }
	}
}
?>
