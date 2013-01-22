<?php
/**
 * アップロードテーブル表示用クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Uploads_View {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	var $_container = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Uploads_View() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * アップロードIDからアップロードオブジェクトを取得
	 * @param int upload_id
	 * @return array uploads_object
	 * @access	public
	 */
	function getUploadById($id) {
		$params = array(
			"upload_id" => $id
		);
		$sql = "SELECT * FROM {uploads}" .
					" WHERE {uploads}.upload_id = ?" .
					" ";
		$result = $this->_db->execute($sql,$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$db->addError();
			return false;
		}
		if(isset($result[0]))
			return $result;
		else
			return null;
	}

	/**
	 * モジュールIDからアップロードオブジェクトを取得
	* @param int module_id
	 * @return array uploads_object
	 * @access	public
	 */
	function getUploadByModuleid($module_id) {
		$params = array(
			"module_id" => $module_id
		);
		$sql = "SELECT * FROM {uploads}" .
					" WHERE {uploads}.module_id = ?" .
					" ";
		$result = $this->_db->execute($sql,$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$db->addError();
			return false;
		}
		if(isset($result[0]))
			return $result;
		else
			return null;
	}


	/**
	 * 画像表示できるかどうかのチェック
	 * @param int $upload_id
	 * @param int $show_auth_id		見ることができるルーム権限（主担以上など）
	 * @param int $thumbnail_flag  1 or 0 サムネイル表示するかどうか
	 * @param string $action_name
	 * @return array(string pathname, string file_name)
	 * @access	public
	 */
    function downloadCheck($upload_id, $show_auth_id = null, $thumbnail_flag = 0, $action_name = null, $force_resize=null) {
    	$pathname = null;
    	$file_name = null;
    	$session =& $this->_container->getComponent("Session");
    	$pagesView =& $this->_container->getComponent("pagesView");
    	$actionChain =& $this->_container->getComponent("ActionChain");
    	$action_name = isset($action_name) ? $action_name : $actionChain->getCurActionName();
    	$user_id = $session->getParameter("_user_id");
    	$physical_file_name = null;
    	$cache_flag = false;
    	$space_type = _SPACE_TYPE_GROUP;

		// add by AllCreator 2010.10.13
		$mobile_imgdsp_size = 0;
		$needThumb = false;
		$mobile_flag = $session->getParameter( "_mobile_flag" );
		if( $mobile_flag == _ON ) {
			$mobile_imgdsp_size = $session->getParameter( "_mobile_imgdsp_size" );
		}


		if( $force_resize === null ) {
			if( $mobile_imgdsp_size != 0 ) {
				$resize_spec = array( $mobile_imgdsp_size, 0 );
			}
			else {
				$resize_spec = 0;
			}
		}
		else if( $force_resize === 0 ) {
			$resize_spec = 0;
		}
		else {
			$resize_spec = $force_resize;
		}


    	if($upload_id != null) {
    		//権限チェック
    		$uploads_obj = $this->getUploadById($upload_id);
    		if(is_array($uploads_obj)) {
    			foreach($uploads_obj as $upload_obj) { // Loop, but here is only one data

					if( is_array($resize_spec) ) {
						$needThumb = $this->_needThumb( $upload_obj, $resize_spec );
					}
    				//
    				// ActionNameチェック
    				//
    				if($action_name != $upload_obj['action_name']) {
    					break;
    				}

    				$room_id = $upload_obj['room_id'];
    				$file_name = $upload_obj['file_name'];

					// thumbnail_flagを最優先で見ることにした AllCreator 2010.10.13
    				if($thumbnail_flag) {
    					$physical_file_name = $upload_obj['upload_id']."_thumbnail.".$upload_obj['extension'];
    					//$normal_physical_file_name = $upload_obj['physical_file_name'];
    				}
					else if( $force_resize !== null && $needThumb==true ) {
    					$physical_file_name = $upload_obj['upload_id']."_resize_".$resize_spec[0]."_".$resize_spec[1].".".$upload_obj['extension'];
					}
					// mod by AllCreator 2010.10.13
					else if( $mobile_imgdsp_size!=0 && $needThumb==true ) {
    					$physical_file_name = $upload_obj['upload_id']."_mobile_".$mobile_imgdsp_size.".".$upload_obj['extension'];
					}
					else {
    					$physical_file_name = $upload_obj['physical_file_name'];
    				}
    				if($room_id == 0) {
    					//room_id=0ならば、誰でも閲覧可能
    					$pathname = FILEUPLOADS_DIR.$upload_obj['file_path'];
    					$cache_flag = true;
    					break;
    				} else {
    					$page =& $pagesView->getPageById($room_id);
    					if(isset($page)) {
    						$space_type = $page['space_type'];
    						if($space_type == _SPACE_TYPE_PUBLIC) {
    							//
				    			//パブリックスペース
				    			//
				    			$auth_id = isset($page['authority_id']) ? $page['authority_id'] : $session->getParameter("_default_entry_auth_public");
				    			if($show_auth_id != null && $show_auth_id > $auth_id) {
				    				// 見せれる権限より小さい
				    				break;
				    			}
				    			$pathname = FILEUPLOADS_DIR.$upload_obj['file_path'];
				    			$cache_flag = true;
    							break;
    						} else if($space_type == _SPACE_TYPE_GROUP && $page['private_flag'] == _ON) {
    							//
    							// プライベートスペース
    							//
    							$cache_flag = ($page['default_entry_flag'] == _ON) ? true : false;

    							$err_flag = false;
    							switch($session->getParameter("_open_private_space")) {
    								case _OPEN_PRIVATE_SPACE_GROUP:
    									$err_flag = ($user_id != "0") ? false : true;
    									break;
    								case _OPEN_PRIVATE_SPACE_PUBLIC:
    									break;
    								case _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP:
    									$err_flag = (($user_id != "0" && $page['default_entry_flag'] == _ON) || $user_id == $upload_obj['update_user_id']) ? false : true;
    									break;
    								case _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC:
    									$err_flag = (($page['default_entry_flag'] == _ON) || $user_id == $upload_obj['update_user_id']) ? false : true;
    									break;
    								default:
    									$err_flag = ($user_id == $upload_obj['update_user_id']) ? false : true;
    							}
    							if($err_flag) break;

    							$auth_id = isset($page['authority_id']) ? $page['authority_id'] : _AUTH_OTHER;
    							if($show_auth_id != null && $show_auth_id > $auth_id) {
				    				// 見せれる権限より小さい
				    				break;
				    			}

    							$pathname = FILEUPLOADS_DIR.$upload_obj['file_path'];
    							break;
    						} else if($space_type == _SPACE_TYPE_GROUP && $page['default_entry_flag'] == _ON) {
    							//
    							//グループスペース(すべての会員にデフォルトで参加させる)
    							//
    							$auth_id = isset($page['authority_id']) ? $page['authority_id'] : $session->getParameter("_default_entry_auth_group");
				    			if($show_auth_id != null && $show_auth_id > $auth_id) {
				    				// 見せれる権限より小さい
				    				break;
				    			}
				    			if($user_id != "0" && $auth_id != _AUTH_OTHER) {
				    				$pathname = FILEUPLOADS_DIR.$upload_obj['file_path'];
				    				break;
				    			}
    						} else if($space_type == _SPACE_TYPE_GROUP) {
    							//
    							//グループスペース
    							//
    							$auth_id = isset($page['authority_id']) ? $page['authority_id'] : _AUTH_OTHER;
    							if($show_auth_id != null && $show_auth_id > $auth_id) {
				    				// 見せれる権限より小さい
				    				break;
				    			}
    							if($auth_id == _AUTH_OTHER) {
    								break;
    							}
    							//if($auth_id != 0) {
    								$pathname = FILEUPLOADS_DIR.$upload_obj['file_path'];
    								break;
    							//}
    						}
    					}
    				}
    			}
    		}
    	}
		// Thumbnail 最優先
    	if($thumbnail_flag && !file_exists($pathname.$physical_file_name)) {
    		//
    		// common/avatar_thumbnail.gifを使用
    		//
    		$pathname = MODULE_DIR."/common/files/images/";
	    	$physical_file_name = "thumbnail.gif";
    	}
		// add by AllCreator 2010.10.13
		if( $resize_spec!=0 && $needThumb==true && !file_exists($pathname.$physical_file_name) ) {
			include_once MAPLE_DIR . '/core/FileUpload.class.php';
			$fileUpload =& new FileUpload;
			$result = $fileUpload->resize( $pathname.$upload_obj['physical_file_name'], $resize_spec[0], $resize_spec[1], $pathname.$physical_file_name);
			if( $result == false ) {
				$physical_file_name = $upload_obj['physical_file_name'];
			}
		}
    	return array($pathname, $file_name, $physical_file_name, $cache_flag);
    }

	/**
	 * モバイル用サムネイルを取得すべきアップロード画像か
	 * @param int   mobile_imgdsp_size
	 * @param array uploadsObj
	 * @access	public
	 * @return false:no_need true:need
	 */
	function _needThumb( $uploadsObj, $resize_spec )
	{
		// 無条件にオリジナル画像が欲しいという指示だったら
		if( $resize_spec == 0 ) {
			return false;	// そのままでいいという判断を返す
		}

		//拡張子はgif,png,jpgのみ受け付ける
		switch( strtolower($uploadsObj['extension']) ) {
			case 'jpg':
			case 'png':
			case 'gif':
				break;
			default:
				return false;
		}
		//実サイズが希望サイズを超えているか
		$file_path = FILEUPLOADS_DIR . "/" . $uploadsObj['file_path'] . "/" . $uploadsObj['physical_file_name'];
		$dimension = @getimagesize($file_path);
		if ($dimension === false) {
			return false;
		}
		if( $dimension[0] > $resize_spec[0] || $dimension[1] > $resize_spec[1] ) {
			return true;
		}
		return false;
	}

	/**
	 * ヘッダー出力
	 * @param string pathname
	 * @param string filename
	 * @access	public
	 */
	function headerOutput($pathname, $filename, $physical_file_name = null, $cache_flag = false) {
		if($physical_file_name == null) $physical_file_name = $filename;
		$pathname = $pathname.$physical_file_name;	//urlencode($filename);
		if ($pathname != null && file_exists($pathname)) {
			$mimetype = $this->mimeinfo("type", $filename);
			if($this->_headerOutput($filename, $pathname, filesize($pathname), $mimetype, $cache_flag) == "200") {
				$handle = fopen($pathname, 'rb');
				while (!feof($handle)) {
					echo fread($handle, 1 * (1024 * 1024));
					ob_flush();
					flush();
				}
				fclose($handle);
			}else{
				exit;
			}
		} else {
			header("HTTP/1.0 404 not found");
		}
	}

	function _headerOutput($filename, $pathname, $filesize, $mimetype, $cache_flag = false) {
		$status_code = "200";
		$etag = null;

		if (!isset($_SERVER['HTTP_USER_AGENT'])) {
			//HTTP_USER_AGENTがない場合、
			header("Content-disposition: inline; filename=\"".$filename."\"");
		} elseif (stristr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
			// IEの場合
			header("Content-disposition: inline; filename=\"".mb_convert_encoding($filename, "SJIS", _CHARSET)."\"");
		} elseif (stristr($_SERVER['HTTP_USER_AGENT'], "Opera")) {
			// Operaの場合
			header("Content-disposition: attachment; filename=\"".$filename."\"");
		} elseif (stristr($_SERVER['HTTP_USER_AGENT'], "Firefox")) {
			// FireFoxの場合
			if ($mimetype == "application/x-shockwave-flash") {
				header("Content-disposition: inline; filename=\"".$filename."\"");
			} else {
				header("Content-disposition: attachment; filename=\"".$filename."\"");
			}
		} elseif (stristr($_SERVER['HTTP_USER_AGENT'], "Chrome")) {
			// GoogleChromeの場合
			if (stristr($_SERVER['HTTP_USER_AGENT'], "Windows")) {
				// Windows版
				header("Content-disposition: inline; filename=\"".mb_convert_encoding($filename, "SJIS", _CHARSET)."\"");
			} else {
				// それ以外
				header("Content-disposition: inline; filename=\"".$filename."\"");
			}
		} else {
			// 上記以外(Mozilla, Firefox, NetScape)
			header("Content-disposition: inline; filename=\"".$filename."\"");
		}
		if(!empty($pathname)) {
			$stats = stat( $pathname );
			$etag = sprintf( '"%x-%x-%x"', $stats['ino'], $stats['size'], $stats['mtime'] );
			header('Etag: '.$etag);
		}

		//header("Content-disposition: inline; filename=\"".$filename."\"");
		// パブリックの画像ならばキャッシュを取るように修正
		if($cache_flag == true) {
			// 1Week
			header("Cache-Control: max-age=604800, public");
			header('Pragma: cache'); //no-cache以外の文字列をセット
			$offset = 60 * 60 * 24 * 7; //  1Week
			header('Expires: '.gmdate('D, d M Y H:i:s', time() + $offset).' GMT');
			if (isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) &&
		       stripcslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) == $etag ) {
				header( 'HTTP/1.1 304 Not Modified' );
				$status_code = "304";
		   	}
		//} else if (isset($_SERVER['HTTPS']) && stristr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		//  // IE + サイト全体SSLの場合、ダウンロードが正常に行われない。
		//  // ダウンロードさせるためには、以下コメントをはずす必要があるが、
		//  // アップロードした画像ファイル等をローカルキャッシュにとられてしまう弊害がある。
		//	// 1Week
		//	header("Cache-Control: max-age=604800, public");
		//	header('Pragma: cache'); //no-cache以外の文字列をセット
		//	$offset = 60 * 60 * 24 * 7; //  1Week
		//	header('Expires: '.gmdate('D, d M Y H:i:s', time() + $offset).' GMT');
		} else {
    		header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Pragma: no-cache");
		}
		//header("Cache-Control: public");//キャッシュを有効にする設定(private or public)

		header("Content-length: ".$filesize);
		header("Content-type: ".$mimetype);
		return $status_code;

		//header("Content-type: application/force-download");
		//header("Content-type: ForceType application/octet-stream");
		//header("Content-type: AddType application/octet-stream");
		//header("Content-type: application/octet-stream");
    }

    /**
	 * PHPで作られたファイルダウンロードメソッド
	 * @param string $data
	 * @param string $filename (hogehoge.csv等)
	 * @param string $mime_type (document/unknown等)
	 * @access	public
	 */
    function download($data, $filename, $mimetype = null) {
    	if($mimetype == null) {
    		$mimetype = $this->mimeinfo("type", $filename);
    	}
		$this->_headerOutput($filename, null, strlen($data), $mimetype);

    	echo $data;
    }

    /**
	 * Mimeタイプ取得
	 * @param int key(type or icon)
	 * @return string mime_type
	 * @access	public
	 */
    function mimeinfo($key, $filename) {
	    $mimeinfo = array (
	        "xxx"  => array ("type"=>"document/unknown", "icon"=>"unknown.gif"),
	        "3gp"  => array ("type"=>"video/quicktime", "icon"=>"video.gif"),
	        "ai"   => array ("type"=>"application/postscript", "icon"=>"image.gif"),
	        "aif"  => array ("type"=>"audio/x-aiff", "icon"=>"audio.gif"),
	        "aiff" => array ("type"=>"audio/x-aiff", "icon"=>"audio.gif"),
	        "aifc" => array ("type"=>"audio/x-aiff", "icon"=>"audio.gif"),
	        "applescript"  => array ("type"=>"text/plain", "icon"=>"text.gif"),
	        "asc"  => array ("type"=>"text/plain", "icon"=>"text.gif"),
	        "au"   => array ("type"=>"audio/au", "icon"=>"audio.gif"),
	        "avi"  => array ("type"=>"video/x-ms-wm", "icon"=>"avi.gif"),
	        "bmp"  => array ("type"=>"image/bmp", "icon"=>"image.gif"),
	        "cs"   => array ("type"=>"application/x-csh", "icon"=>"text.gif"),
	        "css"  => array ("type"=>"text/css", "icon"=>"text.gif"),
	        "csv"  => array ("type"=>"text/plain", "icon"=>"csv.gif"),
	        "dv"   => array ("type"=>"video/x-dv", "icon"=>"video.gif"),
	        "doc"  => array ("type"=>"application/msword", "icon"=>"word.gif"),
	        "docx"  => array ("type"=>"application/vnd.openxmlformats-officedocument.wordprocessingml.document", "icon"=>"word.gif"),
	        "dif"  => array ("type"=>"video/x-dv", "icon"=>"video.gif"),
	        "eps"  => array ("type"=>"application/postscript", "icon"=>"pdf.gif"),
	        "gif"  => array ("type"=>"image/gif", "icon"=>"image.gif"),
	        "gtar" => array ("type"=>"application/x-gtar", "icon"=>"zip.gif"),
	        "gz"   => array ("type"=>"application/g-zip", "icon"=>"zip.gif"),
	        "gzip" => array ("type"=>"application/g-zip", "icon"=>"zip.gif"),
	        "h"    => array ("type"=>"text/plain", "icon"=>"text.gif"),
	        "hqx"  => array ("type"=>"application/mac-binhex40", "icon"=>"zip.gif"),
	        "html" => array ("type"=>"text/html", "icon"=>"html.gif"),
	        "htm"  => array ("type"=>"text/html", "icon"=>"html.gif"),
	        "jpe"  => array ("type"=>"image/jpeg", "icon"=>"image.gif"),
	        "jpeg" => array ("type"=>"image/jpeg", "icon"=>"image.gif"),
	        "jpg"  => array ("type"=>"image/jpeg", "icon"=>"image.gif"),
	        "js"   => array ("type"=>"application/x-javascript", "icon"=>"text.gif"),
	        "latex"=> array ("type"=>"application/x-latex", "icon"=>"text.gif"),
	        "m"    => array ("type"=>"text/plain", "icon"=>"text.gif"),
	    	"flv"  => array ("type"=>"video/x-flv", "icon"=>"video.gif"),
	        "mov"  => array ("type"=>"video/quicktime", "icon"=>"video.gif"),
	        "movie"=> array ("type"=>"video/x-sgi-movie", "icon"=>"video.gif"),
	        "m3u"  => array ("type"=>"audio/x-mpegurl", "icon"=>"audio.gif"),
	        "mp3"  => array ("type"=>"audio/mp3", "icon"=>"audio.gif"),
	        "mp4"  => array ("type"=>"video/mp4", "icon"=>"video.gif"),
	        "mpeg" => array ("type"=>"video/mpeg", "icon"=>"video.gif"),
	        "mpe"  => array ("type"=>"video/mpeg", "icon"=>"video.gif"),
	        "mpg"  => array ("type"=>"video/mpeg", "icon"=>"video.gif"),
	        "pct"  => array ("type"=>"image/pict", "icon"=>"image.gif"),
	        "pdf"  => array ("type"=>"application/pdf", "icon"=>"pdf.gif"),
	        "php"  => array ("type"=>"text/plain", "icon"=>"text.gif"),
	        "pic"  => array ("type"=>"image/pict", "icon"=>"image.gif"),
	        "pict" => array ("type"=>"image/pict", "icon"=>"image.gif"),
	        "png"  => array ("type"=>"image/png", "icon"=>"image.gif"),
	        "pps"  => array ("type"=>"application/vnd.ms-powerpoint", "icon"=>"powerpoint.gif"),
	        "ppt"  => array ("type"=>"application/vnd.ms-powerpoint", "icon"=>"powerpoint.gif"),
	        "pptx"  => array ("type"=>"application/vnd.openxmlformats-officedocument.presentationml.presentation", "icon"=>"powerpoint.gif"),
	        "ps"   => array ("type"=>"application/postscript", "icon"=>"pdf.gif"),
	        "qt"   => array ("type"=>"video/quicktime", "icon"=>"video.gif"),
	        "ra"   => array ("type"=>"audio/x-realaudio", "icon"=>"audio.gif"),
	        "ram"  => array ("type"=>"audio/x-pn-realaudio", "icon"=>"audio.gif"),
	        "rm"   => array ("type"=>"audio/x-pn-realaudio", "icon"=>"audio.gif"),
	        "rtf"  => array ("type"=>"text/rtf", "icon"=>"text.gif"),
	        "rtx"  => array ("type"=>"text/richtext", "icon"=>"text.gif"),
	        "sh"   => array ("type"=>"application/x-sh", "icon"=>"text.gif"),
	        "sit"  => array ("type"=>"application/x-stuffit", "icon"=>"zip.gif"),
	        "smi"  => array ("type"=>"application/smil", "icon"=>"text.gif"),
	        "smil" => array ("type"=>"application/smil", "icon"=>"text.gif"),
	        "swf"  => array ("type"=>"application/x-shockwave-flash", "icon"=>"flash.gif"),
	        "tar"  => array ("type"=>"application/x-tar", "icon"=>"zip.gif"),
	        "tgz"  => array ("type"=>"application/x-tar", "icon"=>"zip.gif"),
	        "tif"  => array ("type"=>"image/tiff", "icon"=>"image.gif"),
	        "tiff" => array ("type"=>"image/tiff", "icon"=>"image.gif"),
	        "tex"  => array ("type"=>"application/x-tex", "icon"=>"text.gif"),
	        "texi" => array ("type"=>"application/x-texinfo", "icon"=>"text.gif"),
	        "texinfo"  => array ("type"=>"application/x-texinfo", "icon"=>"text.gif"),
	        "tsv"  => array ("type"=>"text/tab-separated-values", "icon"=>"text.gif"),
	        "txt"  => array ("type"=>"text/plain", "icon"=>"text.gif"),
	        "wav"  => array ("type"=>"audio/wav", "icon"=>"audio.gif"),
	        "wmv"  => array ("type"=>"video/x-ms-wmv", "icon"=>"avi.gif"),
	        "asf"  => array ("type"=>"video/x-ms-asf", "icon"=>"avi.gif"),
	        "xls"  => array ("type"=>"application/vnd.ms-excel", "icon"=>"excel.gif"),
	        "xlsx"  => array ("type"=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "icon"=>"excel.gif"),
	        "xml"  => array ("type"=>"text/xml", "icon"=>"xml.gif"),
	        "xsl"  => array ("type"=>"text/xml", "icon"=>"xml.gif"),
	        "zip"  => array ("type"=>"application/zip", "icon"=>"zip.gif"),
	        "tex"  => array ("type"=>"application/x-tex", "icon"=>"text.gif"),
	        "dvi"  => array ("type"=>"application/x-dvi", "icon"=>"text.gif"),
	        "ps"   => array ("type"=>"application/postscript", "icon"=>"text.gif"),
	        "ics"  => array ("type"=>"application/octet-stream", "icon"=>"outlook.gif"),
	        "jtd"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jbw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jtt"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jfw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jvw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "juw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jaw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jtw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jsw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "jxw"  => array ("type"=>"application/x-js-taro", "icon"=>"unknown.gif"),
	        "odt"  => array ("type"=>"application/vnd.oasis.opendocument.text", "icon"=>"unknown.gif"),
	        "odg"  => array ("type"=>"application/vnd.oasis.opendocument.graphic", "icon"=>"unknown.gif"),
	        "ods"  => array ("type"=>"application/vnd.oasis.opendocument.spreadsheet", "icon"=>"unknown.gif"),
	        "odp"  => array ("type"=>"application/vnd.oasis.opendocument.presentation", "icon"=>"unknown.gif"),
	        "odb"  => array ("type"=>"application/vnd.oasis.opendocument.database", "icon"=>"unknown.gif"),
	        "odf"  => array ("type"=>"application/vnd.oasis.opendocument.formula", "icon"=>"unknown.gif")
	    );

	    if (eregi("\.([a-z0-9]+)$", $filename, $match)) {
	        if(isset($mimeinfo[strtolower($match[1])][$key])) {
	            return $mimeinfo[strtolower($match[1])][$key];
	        } else {
	            return $mimeinfo["xxx"][$key];   // By default
	        }
	    } else {
	        return $mimeinfo["xxx"][$key];   // By default
	    }
	}

	/**
     * 指定された拡張子になっているか？
     *
     * @param   string    $file_name
     * @param   string    $allow_extension  拡張子文字列(「,」区切りで複数指定可)
     * 					　指定しない場合、configのallow_extensionの中にあるかどうかのチェック
     * @return  array pathInfo or false
     * @access  public
     */
    function checkExtension($file_name, $allow_extension = null)
    {
    	$pathinfo = pathinfo($file_name);
    	if(isset($pathinfo['extension'])) {
			$extension_str = strtolower($pathinfo['extension']);
		} else {
			$extension_str = "";
		}

    	if($allow_extension == null) {
	    	$configView =& $this->_container->getComponent("configView");
	        $config = $configView->getConfigByConfname(_SYS_CONF_MODID, "allow_extension");
	        if(!isset($config["conf_value"])) {
	        	return false;
	        }
	        $allow_extension = $config["conf_value"];
        }

        if($allow_extension != "") {
        	$extensionArray = explode(",", strtolower($allow_extension));
        	if(!in_array($extension_str, $extensionArray)) {
        		return false;
        	}
        }
        //
		// tar.gzの対応
		// tar.gzのほかにも同じような拡張子があるかも
		//
		if(preg_match("/.+\.tar\.gz$/i", $file_name)) {
			$pathinfo['extension'] = "tar.gz";
		}
		return $pathinfo;
    }
}
?>
