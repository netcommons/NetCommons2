<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * ------------------------------------------------------------------------
 * Changed by R.Masukawa
 * page_id,module_id,block_id,unique_idをセットできるように修正
 * 拡張子によるチェックを追加
 * ------------------------------------------------------------------------
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      KeyPoint
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: FileUpload.class.php,v 1.17 2008/07/31 09:01:22 Ryuji.M Exp $
 */

/**
 * FileUpload関連の処理を行う（複数アップロード対応版）
 * 	エラーメッセージを保持するように修正
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      KeyPoint
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class FileUpload
{
    /**
     * @var フォームで指定したフィールド名を保持
     *
     * @access  private
     * @since   3.1.0
     */
    var $_name;
    
    /**
     * @var ファイル移動後のファイルのモードを保持する
     *
     * @access  private
     * @since   3.1.0
     */
    var $_filemode;
	
	/**
     * @var ファイルエラーメッセージ保持
     *
     * @access  private
     */
    var $_error_mes;
    
    /**
     * @var　ページID保持
     *
     * @access  private
     */
    var $_page_id;
    
    /**
     * @var モジュールID保持
     *
     * @access  private
     */
    var $_module_id;
    
    /**
     * @var ブロックID保持
     *
     * @access  private
     */
    var $_block_id;
    
    /**
     * @var モジュールの固有ID保持
     *
     * @access  private
     */
    var $_unique_id;
    
    /**
     * @var ダウンロードアクション名保持
     *
     * @access  private
     */
    var $_download_action_name;
    
    /**
     * @var ファイルクラスを保持
     *
     * @access  private
     */
    var $_file;
    
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.1.0
     */
    function FileUpload()
    {
        $this->_name     = "";   //ファイル名を配列に格納
        $this->_filemode = 0644;
        $this->_error_mes = array();
        
       $this->_page_id = null;
       $this->_module_id = null;
       $this->_unique_id = 0;
       $this->_download_action_name = null;
       
       $this->_upload_id = array();
       $this->_file_path = array();
       $this->_extension = array();
       $this->_insert_time = array();
       
       $container =& DIContainerFactory::getContainer();
       $this->_file =& $container->getComponent("File");
    }

    /**
     * フォームで指定したフィールド名を返却
     *
     * @return  string  フィールド名
     * @access  public
     * @since   3.1.0
     */
    function getName()
    {
        return $this->_name;
    }
    
    /**
     * フォームで指定したフィールド名をセット
     *
     * @param   string  $name   フィールド名
     * @access  public
     * @since   3.1.0
     */
    function setName($name)
    {
        $this->_name = $name;
    }
  
    /**
     * uploadIDを返却
     *
     * @return  array   ファイルアップロードIDの配列
     * @access  public
     */
    function getUploadid()
    {
        return $this->_upload_id;
    }
    
    /**
     * uploadIDをセット
     *
     * @param  int key
     * @param  int upload_id
     * @access  public
     */
    function setUploadid($key,$upload_id)
    {
        $this->_upload_id[$key] = $upload_id;
    }

    /**
     * file_pathを返却
     * TODO:後に削除   
     * @return  array   ファイルアップロードfile_pathの配列
     * @access  public
     */
    //function getFilepath()
    //{
    //    return $this->_file_path;
    //}
    
    /**
     * file_pathをセット
     * TODO:後に削除   
     * @param  int key
     * @param  string file_path
     * @access  public
     */
    //function setFilepath($key,$file_path)
    //{
    //    $this->_file_path[$key] = $file_path;
    //}
    
    /**
     * extensionを返却
     *
     * @return  array   ファイルアップロードextensionの配列
     * @access  public
     */
    function getExtension()
    {
        return $this->_extension;
    }
    
    /**
     * extensionをセット
     *
     * @param  int key
     * @param  string extension
     * @access  public
     */
    function setExtension($key,$extension)
    {
        $this->_extension[$key] = $extension;
    }
    
    /**
     * insert_timeを返却
     *
     * @return  array   ファイルアップロードinsert_time(yyyymmddhhmmss)の配列
     * @access  public
     */
    function getInserttime()
    {
        return $this->_insert_time;
    }
    
    /**
     * extensionをセット
     *
     * @param  int key
     * @param  string insert_time
     * @access  public
     */
    function setInserttime($key,$insert_time)
    {
        $this->_insert_time[$key] = $insert_time;
    }
 
    /**
     * フォームで指定したページIDを返却
     *
     * @return  int  ページID
     * @access  public
     */
    function getPageid()
    {
        return $this->_page_id;
    }
    
    /**
     * フォームで指定したページIDをセット
     *
     * @param   int  $page_id   ページID
     * @access  public
     */
    function setPageid($page_id)
    {
        $this->_page_id = $page_id;
    }
    
    /**
     * フォームで指定したモジュールIDを返却
     *
     * @return  int  モジュールID
     * @access  public
     */
    function getModuleid()
    {
        return $this->_module_id;
    }
    
    /**
     * フォームで指定したモジュールIDをセット
     *
     * @param   int  $module_id   モジュールID
     * @access  public
     */
    function setModuleid($module_id)
    {
        $this->_module_id = $module_id;
    }
 
    /**
     * フォームで指定したモジュールの固有IDを返却
     *
     * @return  int  unique_id モジュールの固有ID
     * @access  public
     */
    function getUniqueid()
    {
        return $this->_unique_id;
    }
    
    /**
     * フォームで指定したモジュールの固有IDをセット
     *
     * @param   int  $unique_id    モジュールの固有ID
     * @access  public
     */
    function setUniqueid($unique_id)
    {
        $this->_unique_id = $unique_id;
    }
    
    /**
     * フォームで指定したダウンロードアクション名を返却
     *
     * @return  string  ダウンロードアクション名
     * @access  public
     */
    function getDownLoadactionName()
    {
        return $this->_download_action_name;
    }
    
    /**
     * フォームで指定したダウンロードアクション名をセット
     *
     * @param   string  $download_action_name   ダウンロードアクション名
     * @access  public
     */
    function setDownLoadactionName($download_action_name)
    {
        $this->_download_action_name = $download_action_name;
    }
    
    /**
     * ファイル移動後のファイルのモードを返却
     *
     * @return  integer ファイルのモード
     * @access  public
     * @since   3.1.0
     */
    function getFilemode()
    {
        return $this->_filemode;
    }
    
    /**
     * ファイル移動後のファイルのモードをセット
     *
     * @param   integer $filemode   ファイルのモード
     * @access  public
     * @since   3.1.0
     */
    function setFilemode($filemode)
    {
        $this->_filemode = octdec($filemode);
    }

    /**
     * アップロードされた数を返却
     *
     * @return  integer アップロードされた数
     * @access  public
     * @since   3.1.0
     * changed 2008/02/07-Ryuji $_FILEがNULLの場合でもNoticeがでないように修正
     */
    function count() {
        $name = $this->getName();
        $global_files = $this->_file->getParameters();
        if(empty($global_files)) {
        	return 0;
        }
        $files = $this->_file->getParameterRef($name);
        
        if (is_array($files["name"])) {
            return count($files["name"]);
        } else {
            return 1;
        }
    }
    
    /**
     * クライアントマシンの元のファイル名を返却
     *
     * @return  array   クライアントマシンの元のファイル名の配列
     * @access  public
     * @since   3.1.0
     */
    function getOriginalName()
    {
        $original_name = array();
        //配列で返す
        $name = $this->getName();
        $files = $this->_file->getParameterRef($name);
        if (($name != "") && isset($files)) {
            if (is_array($files["name"])) {
                foreach ($files["name"] as $key => $value) {
                    $original_name[$key] = $value;
                }
            }else if (isset($files["name"])){
                $original_name[0] = $files["name"];
            }
        }
        return $original_name;
    }
    
    /**
     * ファイルのMIME型を返却
     *
     * @return  array   ファイルのMIME型の配列
     * @access  public
     * @since   3.1.0
     */
    function getMimeType()
    {
        $mime_type = array();
        //配列で返す
        $name = $this->getName();
        $files = $this->_file->getParameterRef($name);
        if (($name != "") && isset($files)) {
            if (is_array($files["type"])) {
                foreach ($files["type"] as $key => $value) {
                    $mime_type[$key] = $value;
                }
            }else if (isset($files["type"])){
                $mime_type[0] = $files["type"];
            }
        }
        return $mime_type;
    }
    
    /**
     * ファイルの拡張子を返却
     *
     * @return  array   ファイルの拡張子の配列
     * @access  public
     */
    function getChkExtension()
    {
        $extension = array();
        //配列で返す
        $name = $this->getName();
        $files = $this->_file->getParameterRef($name);
        if (($name != "") && isset($files)) {
            if (is_array($files["name"])) {
                foreach ($files["name"] as $key => $value) {
                	//if(preg_match("/.+\.tar\.gz$/i", $value)) {
					//	$extension_str = "tar.gz";
					//} else {
						$pathinfo = pathinfo($value);
						if(isset($pathinfo['extension'])) {
							$extension_str = $pathinfo['extension'];
						} else {
							$extension_str = "";
						}
					//}
					
                    $extension[$key] = $extension_str;
                }
            }else if (isset($files["name"])){
            	//if(preg_match("/.+\.tar\.gz$/i", $files["name"])) {
				//	$extension_str = "tar.gz";
				//} else {
					$pathinfo = pathinfo($files["name"]);
					if(isset($pathinfo['extension'])) {
						$extension_str = $pathinfo['extension'];
					} else {
						$extension_str = "";
					}
				//}
                $extension[0] = $extension_str;
            }
        }
        return $extension;
    }
    
    /**
     * アップロードされたファイルのバイト単位のサイズを返却
     *
     * @return  array   ファイルのサイズの配列
     * @access  public
     * @since   3.1.0
     */
    function getFilesize()
    {
        $filesize = array();
        //配列で返す
        $name = $this->getName();
        $files = $this->_file->getParameterRef($name);
        if (($name != "") && isset($files)) {
            if (is_array($files["size"])) {
                foreach ($files["size"] as $key => $value) {
                    $filesize[$key] = $value;
                }
            }else if (isset($files["size"])){
                $filesize[0] = $files["size"];
            }
        }
        return $filesize;
    }
    
    /**
     * テンポラリファイルの名前を返却
     *
     * @return  array   テンポラリファイルの名前の配列
     * @access  public
     * @since   3.1.0
     */
    function getTmpName()
    {
        $tmp_name = array();
        //配列で返す
        $name = $this->getName();
        $files = $this->_file->getParameterRef($name);
        if (($name != "") && isset($files)) {
            if (is_array($files["tmp_name"])) {
                foreach ($files["tmp_name"] as $key => $value) {
                    $tmp_name[$key] = $value;
                }
            }else if (isset($files["tmp_name"])){
                $tmp_name[0] = $files["tmp_name"];
            }
        }
        return $tmp_name;
    }
    
    /**
     * テンポラリファイルの名前を返却(Ref)
     *
     * @return  array   テンポラリファイルの名前の配列
     * @access  public
     */
    function &getTmpNameRef()
    {
        $tmp_name = array();
        //配列で返す
        $name = $this->getName();
        $files = $this->_file->getParameterRef($name);
        if (($name != "") && isset($files)) {
            if (is_array($files["tmp_name"])) {
            	
                foreach ($files["tmp_name"] as $key => $value) {
                	$tmp_name[$key] = $value;
                }
            }else if (isset($files["tmp_name"])){
                $tmp_name[0] =& $files["tmp_name"];
            }
        }
        return $tmp_name;
    }
    
    /**
     * ファイルアップロードに関するエラーコードを返却
     *
     * @return  array   ファイルアップロードに関するエラーコードの配列
     * @access  public
     * @since   3.1.0
     */
    function getError()
    {
        $error_list = array();
        //配列で返す
        $name = $this->getName();
        $files = $this->_file->getParameterRef($name);
        if (($name != "") && isset($files)) {
            if (is_array($files["error"])) {
                foreach ($files["error"] as $key => $value) {
                    $error_list[$key] = $value;
                }
            }else if (isset($files["error"])){
                $error_list[0] = $files["error"];
            }
        }
        return $error_list;
    }
    
    /**
     * ファイルアップロードに関するエラーメッセージをセット
     *
     * @param  int key
     * @param  string error_mes
     * @access  public
     */
    function setErrorMes($key,$error_mes)
    {
        $this->_error_mes[$key] = $error_mes;
    }
    
    /**
     * ファイルアップロードに関するエラーメッセージを返却
     *
     * @return  array   ファイルアップロードに関するエラーメッセージの配列
     * @access  public
     * @since   3.1.0
     */
    function getErrorMes()
    {
        return $this->_error_mes;
    }
    
    /**
     * 指定されたMIME型になっているか？
     *
     * @param   array    $type  MIME型の配列
     * @return  array[boolean]  指定されたMIME型になっているか？の配列
     * @access  public
     * @since   3.1.0
     */
    function checkMimeType($type_list)
    {
        $mime_type_check = array();
        $mime_type = $this->getMimeType();
        if (count($mime_type) > 0) {
            foreach ($mime_type as $key => $val) {
                if (isset($type_list[$key])) {
                    $type = $type_list[$key];
                } else if (isset($type_list["default"])){
                	$type = $type_list["default"];
                } else {
                    $type = "";
                }
                if ($type == "" || in_array($val,$type)  ) {
                    $mime_type_check[$key] = true;
                }else {
                    $mime_type_check[$key] = false;
                }
            }
        }
        return $mime_type_check;
    }
    
    
    /**
     * 指定された拡張子になっているか？
     *
     * @param   array    $extension_list  拡張子の配列
     * @return  array[boolean]  指定された拡張子になっているか？の配列
     * @access  public
     */
    function checkExtension($extension_list)
    {
        $extension_check = array();
        $extension_arr = $this->getChkExtension();
        if (count($extension_arr) > 0) {
        	foreach ($extension_arr as $key => $val) {
                if (isset($extension_list[$key])) {
                    $extension = $extension_list[$key];
                } else if (isset($extension_list["default"])){
                	$extension = $extension_list["default"];
                } else {
                    $extension = "";
                }
                if ($extension == "" || in_array(strtolower($val), $extension)  ) {
                    $extension_check[$key] = true;
                }else {
                    $extension_check[$key] = false;
                }
            }
        }
        return $extension_check;
    }
    
    
    /**
     * ファイルサイズが指定されたサイズ以下かどうか？
     *
     * @param   array   $size_list  基準となるファイルサイズの配列
     * @return  array[boolean]    ファイルサイズが指定されたサイズ以下かどうか？の配列
     * @access  public
     * @since   3.1.0
     */
    function checkFilesize($size_list)
    {
        $filesize_check = array();
        $filesize = $this->getFilesize();
        if (count($filesize) > 0) {
            foreach ($filesize as $key => $val) {
                if (isset($size_list[$key])) {
                    $size = $size_list[$key];
                } else if (isset($size_list["default"])) {
                    $size = $size_list["default"];
                } else {
                    $size = "";
                }
                if ($size == "" || $val <= $size) {
                    $filesize_check[$key] = true;
                }else {
                    $filesize_check[$key] = false;
                }
            }
        }
        return $filesize_check;
    }

    /**
     * 指定されたパスへファイルを移動(one file)
     *
     * @param   strint  $name   移動元のファイルの索引番号
     * @param   strint  $dest   移動先のファイル名
     * @return  boolean 移動に成功したかどうか
     * @access  public
     * @since   3.1.0
     */
    function move($id,$dest)
    {
        $tmp_name = $this->getTmpName();
        if (isset($tmp_name[$id])) {
        	if (move_uploaded_file($tmp_name[$id], $dest)) {
                chmod($dest, $this->getFilemode());
                return true;
            } else {
            	return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * リサイズ処理
     *
     * @param   array   $width_list  		基準となるファイルwidthの配列
     * @param   array   $height_list  		基準となるファイルheightの配列
     * @param   boolean $thumbnail_path		サムネイルを新規に作成する場合、指定（ファイル名を含むフルパスで指定）
     * @access  public
     */
    function resizeFile($width_list, $height_list, $thumbnail_path = null)
    {
    	if(!function_exists("gd_info")) {
    		return;	
    	}
        $filewidth_check = array();
        $filetmp = $this->getTmpNameRef();
        $name = $this->getName();
        
        if (count($filetmp) > 0) {
        	foreach ($filetmp as $key => $val) {
            	if (isset($width_list[$key])) {
                    $max_width = $width_list[$key];
                } else if (isset($width_list["default"])) {
                    $max_width = $width_list["default"];
                } else {
                    $max_width = "";
                }
                if (isset($height_list[$key])) {
                    $max_height = $height_list[$key];
                } else if (isset($height_list["default"])) {
                    $max_height = $height_list["default"];
                } else {
                    $max_height = "";
                }
                
                $tmp_file =& $val;
        		$files = $this->_file->getParameterRef($name);
        		if(count($files["size"]) > 1) {
            		$size =& $files["size"][$key];
        		} else {
        			$size =& $files["size"];
        		}
        		
        		$result = $this->resize($tmp_file, $max_width, $max_height);
                if($result !== false) {
                	$size = $result;
                }
            }
        }
    }
    /*function resizeFile($width_list, $height_list, $thumbnail_path = null)
    {
    	if(!function_exists("gd_info")) {
    		return;	
    	}
        $filewidth_check = array();
        $filetmp = $this->getTmpNameRef();
        $name = $this->getName();
        
        if (count($filetmp) > 0) {
        	foreach ($filetmp as $key => $val) {
            	if (isset($width_list[$key])) {
                    $max_width = $width_list[$key];
                } else if (isset($width_list["default"])) {
                    $max_width = $width_list["default"];
                } else {
                    $max_width = "";
                }
                if (isset($height_list[$key])) {
                    $max_height = $height_list[$key];
                } else if (isset($height_list["default"])) {
                    $max_height = $height_list["default"];
                } else {
                    $max_height = "";
                }
                $dimension = @getimagesize($val);
                if (false !== $dimension) {
                	$thumb = null;
                	if($max_width != "" && $max_height != "") {
                		// 幅・高さ両方とも指定されている場合
                		if ($dimension[0] > $max_width || $dimension[1] > $max_height) {
							// 比率を計算する
							$base_rate = (float)($max_height / $max_width);			// 枠比率
			                $file_rate = (float)($dimension[1] / $dimension[0]);	// ファイル比率
							// 新規サイズを取得する
							if ($base_rate < $file_rate) {
								$newwidth = intval((float)((float)(1 / $file_rate) * $max_height));
								$newheight = $max_height;
							} else {
								$newwidth = $max_width;
								$newheight = intval((float)($file_rate * $max_width));
							}
							// 新規イメージ
							if (($dimension[2] == 2 || $dimension[2] == 3) && function_exists("imagecreatetruecolor") == true) {
								$thumb = imagecreatetruecolor($newwidth, $newheight);
							} else {
								$thumb = imagecreate($newwidth, $newheight);
							}
			            }
                	} else if($max_width != "") {
                		// 幅のみ指定されている場合
                		if ($dimension[0] > $max_width) {
							// 比率を計算する
							$file_rate = (float)($max_width / $dimension[0]);	// ファイル比率
							// 新規サイズを取得する
							$newwidth = $max_width;
							$newheight = intval((float)($file_rate * $dimension[1]));
							// 新規イメージ
							if (($dimension[2] == 2 || $dimension[2] == 3) && function_exists("imagecreatetruecolor") == true) {
								$thumb = imagecreatetruecolor($newwidth, $newheight);
							} else {
								$thumb = imagecreate($newwidth, $newheight);
							}
						}
                	} else if($max_height != "") {
                		// 高さのみ指定されている場合
                		if ($dimension[1] > $max_height) {
							// 比率を計算する
							$file_rate = (float)($max_height / $dimension[1]);	// ファイル比率
							// 新規サイズを取得する
							$newwidth = intval((float)($file_rate * $dimension[0]));
							$newheight = $max_height;
							// 新規イメージ
							if (($dimension[2] == 2 || $dimension[2] == 3) && function_exists("imagecreatetruecolor") == true) {
								$thumb = imagecreatetruecolor($newwidth, $newheight);
							} else {
								$thumb = imagecreate($newwidth, $newheight);
							}
						}
                	}
                	
                	if (isset($thumb)) {
                		$tmp_file =& $val;
                		$files = $this->_file->getParameterRef($name);
                		if(count($files["size"]) > 1) {
	                		$size =& $files["size"][$key];
                		} else {
                			$size =& $files["size"];
                		}
                		
						switch ($dimension[2]) {
							case 1: // gif
								if (function_exists("imagecreatefromgif")) {
									// 読み込み
									$source = imagecreatefromgif($tmp_file);
									// リサイズ
									imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
									// 出力
									if($thumbnail_path == null) {
										
									}
									imagegif($thumb, $tmp_file);
								}
								// サイズ
								$size = filesize($tmp_file);						
								break;
							case 2: // jpg
								if (function_exists("imagecreatefromjpeg")) {
									// 読み込み
									$source = imagecreatefromjpeg($tmp_file);
									// リサイズ
									imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
									// 出力
									imagejpeg($thumb, $tmp_file);
									// サイズ
									$size = filesize($tmp_file);
								}
								break;
							case 3: // png
								if (function_exists("imagecreatefrompng")) {
									// 読み込み
									$source = imagecreatefrompng($tmp_file);
									// リサイズ
									imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
									// 出力
									imagepng($thumb, $tmp_file);
								}
								// サイズ
								$size = filesize($tmp_file);						
								break;
							default:
						}
						
		        	}
        		}
            }
        }
    }*/
    
    /**
     * リサイズ処理
     * @param   string		$tmp_file_path				リサイズ元ファイル（ファイル名を含むフルパスで指定）
     * @param   int   		$max_width  				基準となるファイルwidth
     * @param   int   		$max_height  				基準となるファイルheight
     * @param   string	 	$thumbnail_file_path		サムネイルを新規に作成する場合、指定（ファイル名を含むフルパスで指定）
     * @return  size or boolean
     * @access   public
     */
    function resize($tmp_file_path, $max_width = 0, $max_height = 0, $thumbnail_file_path = null)
    {
    	$max_width = intval($max_width);
    	$max_height = intval($max_height);
    	if($max_width <= 0 && $max_height <= 0) {
    		return false;
    	}
    	$dimension = @getimagesize($tmp_file_path);
    	if ($dimension === false) {
    		return false;
    	}
    	$imagick_flag = false;
    	$gd_flag = false;
    	
    	if(function_exists("gd_info")) {
    		$gd_flag = true;
    	} else if (class_exists('Imagick') && function_exists("Imagick::thumbnailImage")) {
    		$image = new Imagick();
    		$imagick_flag = true;	
    	} else {
    		return false;
    	}
    	
    	/*
    	if (class_exists('Imagick') && function_exists("Imagick::thumbnailImage")) {
    		$image = new Imagick();
    		$imagick_flag = true;	
    	} else if(function_exists("gd_info")) {
    		$gd_flag = true;
    	} else {
    		return false;
    	}
    	*/
    	
    	$thumb = null;
    	if($max_width > 0 && $max_height > 0) {
    		// 幅・高さ両方とも指定されている場合
    		if ($dimension[0] > $max_width || $dimension[1] > $max_height) {
				// 比率を計算する
				$base_rate = (float)($max_height / $max_width);			// 枠比率
                $file_rate = (float)($dimension[1] / $dimension[0]);	// ファイル比率
				// 新規サイズを取得する
				if ($base_rate < $file_rate) {
					$newwidth = intval((float)((float)(1 / $file_rate) * $max_height));
					$newheight = $max_height;
				} else {
					$newwidth = $max_width;
					$newheight = intval((float)($file_rate * $max_width));
				}
				// 新規イメージ
				if($gd_flag == true) {
					if (($dimension[2] == 2 || $dimension[2] == 3) && function_exists("imagecreatetruecolor") == true) {
						$thumb = imagecreatetruecolor($newwidth, $newheight);
					} else {
						$thumb = imagecreate($newwidth, $newheight);
					}
				}
            }
    	} else if($max_width > 0) {
    		// 幅のみ指定されている場合
    		if ($dimension[0] > $max_width) {
				// 比率を計算する
				$file_rate = (float)($max_width / $dimension[0]);	// ファイル比率
				// 新規サイズを取得する
				$newwidth = $max_width;
				$newheight = intval((float)($file_rate * $dimension[1]));
				// 新規イメージ
				if($gd_flag == true) {
					if (($dimension[2] == 2 || $dimension[2] == 3) && function_exists("imagecreatetruecolor") == true) {
						$thumb = imagecreatetruecolor($newwidth, $newheight);
					} else {
						$thumb = imagecreate($newwidth, $newheight);
					}
				}
			}
    	} else if($max_height > 0) {
    		// 高さのみ指定されている場合
    		if ($dimension[1] > $max_height) {
				// 比率を計算する
				$file_rate = (float)($max_height / $dimension[1]);	// ファイル比率
				// 新規サイズを取得する
				$newwidth = intval((float)($file_rate * $dimension[0]));
				$newheight = $max_height;
				// 新規イメージ
				if($gd_flag == true) {
					if (($dimension[2] == 2 || $dimension[2] == 3) && function_exists("imagecreatetruecolor") == true) {
						$thumb = imagecreatetruecolor($newwidth, $newheight);
					} else {
						$thumb = imagecreate($newwidth, $newheight);
					}
				}
			}
    	}
    	
    	if (!isset($thumb)) {
    		return false;
    	}
    	
		switch ($dimension[2]) {
			case 1: // gif
				if ($gd_flag == true && function_exists("imagecreatefromgif")) {
					// 読み込み
					$source = imagecreatefromgif($tmp_file_path);
					// 透明色チェック
					$transparent_idx = imagecolortransparent($source);
					// 透明色指示があった場合
					if($transparent_idx >= 0){
						$transparent_color = @imagecolorsforindex($source, $transparent_idx);
						$thumb_transparent_idx = imagecolorallocate($thumb, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue'] );
						imagefill($thumb, 0, 0, $thumb_transparent_idx);
						imagecolortransparent($thumb, $thumb_transparent_idx );
					}
					// リサイズ
					imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
					//imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
					// 出力
					if($thumbnail_file_path != null) {
						imagegif($thumb, $thumbnail_file_path);
					} else {
						imagegif($thumb, $tmp_file_path);
					}
				} else if($imagick_flag == true) {
					$image->readImage($tmp_file_path);
					if($thumbnail_file_path != null) {
						$image->setImageFileName($thumbnail_file_path);
					}
					$image->thumbnailImage($max_width, $max_height, true);
				} else {
					return false;
				}
				// サイズ
				$size = filesize($tmp_file_path);						
				break;
			case 2: // jpg
				if ($gd_flag == true && function_exists("imagecreatefromjpeg")) {
					// 読み込み
					$source = imagecreatefromjpeg($tmp_file_path);
					// リサイズ
					imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
					//imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
					// 出力
					if($thumbnail_file_path != null) {
						imagejpeg($thumb, $thumbnail_file_path,75);
					} else {
						imagejpeg($thumb, $tmp_file_path, 100);
					}
					// サイズ
					$size = filesize($tmp_file_path);
				} else if($imagick_flag == true) {
//$image->spreadImage(6);
//$image->medianFilterImage(3);
//$image->enhanceImage();
//$image->setComression(Imagick::COMPRESSION_JPEG);
//$image->setCompressionQuality(1);
//$image->despeckleImage();
					$image->readImage($tmp_file_path);
					if($thumbnail_file_path != null) {
						$image->setImageFileName($thumbnail_file_path);
					}
					$image->thumbnailImage($max_width, $max_height, true);
				} else {
					return false;
				}
				break;
			case 3: // png
				if ($gd_flag == true && function_exists("imagecreatefrompng")) {
					// 読み込み
					$source = imagecreatefrompng($tmp_file_path);

                    // 透明色チェック
                    $transparent_idx = imagecolortransparent( $source );

                    if( $transparent_idx >= 0 ) {
                        $transparent_color = imagecolorsforindex( $source, $transparent_idx );
                        $transparent_idx = imagecolorallocate( $thumb, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue'] );
                        imagefill( $thumb, 0, 0, $transparent_idx );
                        imagecolortransparent( $thumb, $transparent_idx );
                    }
                    else {
                        $png_info= unpack( 'Nwidth/Nheight/Cbit/Ccolor/Ccompress/Cfilter/Cinterlace', substr(file_get_contents( $tmp_file_path ), 16, 13 ) );
                        if( $png_info['color'] >= 4 ) {
					        //透過の場合の透明色設定
					        imagealphablending( $thumb, false );//AllCreator
					        imagesavealpha( $thumb, true );
					        $fillcolor = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
					        imagefill($thumb, 0, 0, $fillcolor);
                        }
                    }
					// リサイズ
					imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
					//imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $dimension[0], $dimension[1]);
					// 出力
					if($thumbnail_file_path != null) {
						imagepng($thumb, $thumbnail_file_path, 9);
					} else {
						imagepng($thumb, $tmp_file_path, 0);
					}
				} else if($imagick_flag == true) {
					$image->readImage($tmp_file_path);
					if($thumbnail_file_path != null) {
						$image->setImageFileName($thumbnail_file_path);
					}
					$image->thumbnailImage($max_width, $max_height, true);
				} else {
					return false;
				}
				// サイズ
				$size = filesize($tmp_file_path);						
				break;
			default:
		}

    	return $size;
    }
    
    /**
     * ファイルwidthが指定されたwidth以下かどうか？
     *
     * @param   array   $width_list  基準となるファイルwidthの配列
     * @return  array[boolean]    ファイルサイズが指定されたサイズ以下かどうか？の配列
     * @access  public
     * @since   3.1.0
     */
    function checkFilewidth($width_list)
    {
        $filewidth_check = array();
        $filewidth = $this->getTmpName();
        if (count($filewidth) > 0) {
            foreach ($filewidth as $key => $val) {
                if (isset($width_list[$key])) {
                    $width = $width_list[$key];
                } else if (isset($width_list["default"])) {
                    $width = $width_list["default"];
                } else {
                    $width = "";
                }
                $dimension = @getimagesize($val);
                if (false !== $dimension) {
	                if ($width == "" || $dimension[0] <= $width) {
	                    $filewidth_check[$key] = true;
	                }else {
	                    $filewidth_check[$key] = false;
	                }
        		} else {
        			$filewidth_check[$key] = true;
        		}
            }
        }
        return $filewidth_check;
    }
    
    /**
     * ファイルheightが指定されたheight以下かどうか？
     *
     * @param   array   $height_list  基準となるファイルheightの配列
     * @return  array[boolean]    ファイルサイズが指定されたサイズ以下かどうか？の配列
     * @access  public
     * @since   3.1.0
     */
    function checkFileheight($height_list)
    {
        $fileheight_check = array();
        $fileheight = $this->getTmpName();
        if (count($fileheight) > 0) {
            foreach ($fileheight as $key => $val) {
                if (isset($height_list[$key])) {
                    $height = $height_list[$key];
                } else if (isset($height_list["default"])) {
                    $height = $height_list["default"];
                } else {
                    $height = "";
                }
                $dimension = @getimagesize($val);
        		if (false !== $dimension) {
	                if ($height == "" || $dimension[1] <= $height) {
	                    $fileheight_check[$key] = true;
	                }else {
	                    $fileheight_check[$key] = false;
	                }
        		} else {
        			$fileheight_check[$key] = true;
        		}
            }
        }
        return $fileheight_check;
    }
}
?>
