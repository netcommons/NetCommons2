<?php
 /**
 * 共通ファイル操作クラス(Action)
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class File_Action {
	var $_className = "File_Action";
	
	/**
	 * ディレクトリ再帰削除関数
	 * 
	 * @param  string	$path	対象パス
	 * @return	bool	true:正常,false:異常
	 **/
	function delDir($path) {
		// ファイルまたはディレクトリの削除
		if ( is_dir($path) ) { 
			$handle = opendir($path);
			while ( false !== ($file = readdir($handle)) ) {
				if ( $file == '.' || $file == '..' ) { continue; }
				$this->delDir($path. "/". $file);
			} 
			closedir($handle);
			return @rmdir($path);
		} else {
			@chmod($path, 0777);
			return @unlink($path); 
		}
	}
	
	/**
	 * ディレクトリ再帰コピー関数
	 * 
	 * @param  string	$path	    対象パス
	 * @poaram  string	$copy_path	コピー先パス
	 * @return	bool	true:正常,false:異常
	 **/
	function copyDir($path, $copy_path) {
		if(!is_dir( $path )) { 
			return false;
		}
		$oldmask = umask(0);
		//$mod= stat($path);
		//mkdir($copy_path, $mod[2]);
		if(!@file_exists($copy_path)) {
			$mk_result = mkdir($copy_path, 0777);
			if(!$mk_result) {
				return false;
			}
		}
		
		$fileArray=glob( $path."*" );
		if(is_array($fileArray)) {
			foreach( $fileArray as $key => $value){
				preg_match("/^(.*[\/])(.*)/",$value, $matches);
				//mb_ereg("^(.*[\/])(.*)",$value, $matches);
				$data=$matches[2];
				if(is_dir($value)){
	   				$this->copyDir( $value.'/', $copy_path."/".$data.'/' );
	  			} else {
	  				if(!copy( $value, $copy_path."/".$data )) return false;
	   				//$mod=stat($data_ );
	   				//chmod( $copy_path."/".$data, $mod[2] );
	   				chmod($copy_path."/".$data, 0777);
	   			}
			}
		}
		umask($oldmask);
 		return true;
	}
	/**
	 * ファイルコピー関数
	 * $path_fileのファイルを$prefix_copy_path . $copy_path_fileにコピーする
	 * $copy_path_file内のパスでディレクトリがないものは作成する
	 * 
	 * @param  string	$path_file
	 * @param  string  $copy_path_file
	 * @param  string  $prefix_path
	 * 
	 * 	
	 * @return	bool	true:正常,false:異常
	 **/
	function copyFile($path_file, $copy_path_file, $prefix_copy_path = "")
	{    
		if(!file_exists($path_file)) {
			// 	コピー元がなくてもエラーとしない
			return true;
		}
		$copy_path_file_list = explode("/", $copy_path_file);
		$copy_path_len = count($copy_path_file_list);
		if($copy_path_len > 1) {
			$full_path = $prefix_copy_path;
			$count = 0;
			foreach($copy_path_file_list as $path) {
				$full_path .= $path . "/";
				$count++;
				if($full_path != '/' && !is_dir($full_path)) {
					@mkdir($full_path, 0777);
				}
				if($count == $copy_path_len - 1) {
					break;	
				}
			}
			if(!copy($path_file, $prefix_copy_path.$copy_path_file)) return false;
		}
		return true;
	}
}
?>
