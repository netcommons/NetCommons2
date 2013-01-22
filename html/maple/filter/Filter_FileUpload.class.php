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
 * 	エラー文字列をdefineから読み取るように修正（多言語化のため）
 * 	$value は errStrDef,....(Params)のパターン->errStr = sprintf(constant(errStrDef),Params1,Params2....)となるように修正
 * 	エラー発生時、アクションを実行しない（fileUploadクラスへ追加+エラーリストへ追加）
 *  tokenのallow_mimetypeがtrueかどうか
 *  tokenのallow_attachmentがtrueかどうか
 *  拡張のチェックを追加
 *  定義してある画像の広さ、高さがオーバーしていれば、リサイズして登録するように修正
 *  解凍してアップロードできるように修正
 * ------------------------------------------------------------------------
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      KeyPoint
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_FileUpload.class.php,v 1.36 2008/08/07 06:36:41 Ryuji.M Exp $
 */
/**
 * アップロードテーブル登録用クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
include_once MAPLE_DIR .'/core/FileUpload.class.php';
//require_once MAPLE_DIR .'/core/FileUpload.class.php';
//include_once MAPLE_DIR.'/core/Token.class.php';

if (!defined('UPLOAD_ERR_OK')) {
	define('UPLOAD_ERR_OK',        0);
	define('UPLOAD_ERR_INI_SIZE',  1);
	define('UPLOAD_ERR_FORM_SIZE', 2);
	define('UPLOAD_ERR_PARTIAL',   3);
	define('UPLOAD_ERR_NO_FILE',   4);
}

/**
 * FileUpload処理を行うFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      KeyPoint
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class Filter_FileUpload extends Filter
{
	/**
	 * コンストラクター
	 *
	 * @access  public
	 * @since   3.1.0
	 */
	function Filter_FileUpload()
	{
		parent::Filter();
	}

	/**
	 * ファイルアップロード処理を行う
	 *
	 * @access  public
	 * @since   3.1.0
	 */
	function execute()
	{
		$log =& LogFactory::getLog();
		$log->trace("Filter_FileUploadの前処理が実行されました", "Filter_FileUpload#execute");

		$container =& DIContainerFactory::getContainer();

		$fileUpload =& new FileUpload;
		$container->register($fileUpload, 'FileUpload');

		$actionChain =& $container->getComponent("ActionChain");
		$errorList =& $actionChain->getCurErrorList();
		if ($errorList->isExists()) {
			$filterChain =& $container->getComponent("FilterChain");
			$filterChain->execute();
			return;
		}

		$session =& $container->getComponent("Session");
		$request =& $container->getComponent("Request");
		$configView =& $container->getComponent("configView");
		$commonMain =& $container->getComponent("commonMain");
		$fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");


		//パラメータをセット
		$upload_params = $request->getParameter("upload_params");
		$fileUpload->setPageid(intval($request->getParameter("page_id")));
		$fileUpload->setModuleid(intval($request->getParameter("module_id")));
		$unique_id = $request->getParameter("unique_id");
		if(!isset($unique_id)) {
			$unique_id = "0";
		}
		$fileUpload->setUniqueid($unique_id);
		$fileUpload->setDownLoadactionName($request->getParameter("download_action_name"));

		$attributes = $this->getAttributes();

		if (isset($attributes["name"])) {
			$fileUpload->setName($attributes["name"]);

			if (isset($attributes["filemode"])) {
				if (defined($attributes["filemode"])) $attributes["filemode"] = $this->constantDef($attributes["filemode"]);
				$fileUpload->setFilemode($attributes["filemode"]);
			} else {
				//default
				$fileUpload->setFilemode(_UPLOAD_FILE_MODE);	
			}

			//maple.iniを分析
			$maxsize_ini = array();
			$maxwidth_ini = array();
			$maxheight_ini = array();
			$type_ini = array();
			$extension_ini = array();
			$sizeError_ini = array();
			$widthError_ini = array();
			$heightError_ini = array();
			$typeError_ini = array();
			$extensionError_ini = array();
			$noFileError_ini = array();
			$resize = true; // default:画像の枠が範囲を超えていればリサイズ
			if(isset($attributes["stopper"]) && ($attributes["stopper"] === 0 || $attributes["stopper"] === "0" || $attributes["stopper"] === false || $attributes["stopper"] === "false")) {
				$stopper = false;
			} else {
				//stopper default:true
				$stopper = true;
			}
			if (isset($attributes["allow_attachment"])) {
				//configの権限でアップロード可能かどうかをセット
				$allow_attachment_flag = $session->getParameter("_allow_attachment_flag");
				$attributes_allow_attachment_flag = $this->constantDef($attributes["allow_attachment"]);
			} else {
				$allow_attachment_flag = _ALLOW_ATTACHMENT_ALL;
				$attributes_allow_attachment_flag = _ALLOW_ATTACHMENT_ALL;
			}

			//エラーメッセージdefault値指定
			$sizeError_ini["default"] = _FILE_UPLOAD_ERR_SIZE;					//vsprintf(_FILE_UPLOAD_ERR_SIZE,_UPLOAD_MAX_SIZE_IMAGE);
			$widthError_ini["default"] = _FILE_UPLOAD_ERR_UPLOAD_WIDTHLARGE;	//vsprintf(_FILE_UPLOAD_ERR_UPLOAD_WIDTHLARGE,_UPLOAD_MAX_WIDTH_IMAGE);
			$heightError_ini["default"] = _FILE_UPLOAD_ERR_UPLOAD_HEIGHTLARGE;	//vsprintf(_FILE_UPLOAD_ERR_UPLOAD_HEIGHTLARGE,_UPLOAD_MAX_HEIGHT_IMAGE);
			$typeError_ini["default"] = _FILE_UPLOAD_ERR_MIMETYPE;

			$noFileError_ini["whether"] = _FILE_UPLOAD_ERR_UPLOAD_NOFILE;
			$noFileError_whether = 0;
			$maxsize_ini["default"] = _UPLOAD_MAX_SIZE_ATTACHMENT;

			foreach($attributes as $key => $value) {
				//start_add code

				if ((!((strlen($key) == 4 && substr($key,0,4) == "type") || (substr($key,0,4) == "type" && is_numeric(substr($key,5,strlen($key)-6))))) &&
					(!((strlen($key) == 9 && substr($key,0,9) == "extension") || (substr($key,0,9) == "extension" && is_numeric(substr($key,10,strlen($key)-11)))))) {
					$valueArray = explode(",", $value);
					if (count($valueArray) > 1) {
						$printParams = array_slice($valueArray, 1);
						$count = 0;
						foreach ($printParams as $subValue) {
							if (defined($subValue)) {
								$printParams[$count] = $this->constantDef($subValue);
							}
							$count++;
						}
						if (defined($valueArray[0])) $valueArray[0] = $this->constantDef($valueArray[0]);
						$value = vsprintf($valueArray[0],$printParams);
					} else {
						if (defined($value)) $value = $this->constantDef($value);
					}
				} else {
					$valueArray = explode(",", $value);
					if (count($valueArray) == 1) {
						if (defined($value)) $value = $this->constantDef($value);
					}
				}
				if (substr($key,0,11) == "action_name") {
					$fileUpload->setDownLoadactionName($value);
				}
				if (substr($key,0,8) == "maxwidth") {
					if (strlen($key) == 8) {
						$maxwidth_ini["default"] = $value;
					} else if (is_numeric(substr($key,9,strlen($key)-10))) {
						$maxwidth_ini[substr($key,9,strlen($key)-10)] = $value;
					}
				}
				if (substr($key,0,9) == "maxheight") {
					if (strlen($key) == 9) {
						$maxheight_ini["default"] = $value;
					} else if (is_numeric(substr($key,10,strlen($key)-11))) {
						$maxheight_ini[substr($key,10,strlen($key)-11)] = $value;
					}
				}
				if ($key == "resize") {
					if(($value === 1 || $value === "1" || $value === "true" || $value === true)) {
						$resize = true;
					} else {
						$resize = false;
					}
				}
				//
				// 解凍してアップロード
				//
				if ($key == "decompression") {
					if(($value === 1 || $value === "1" || $value === "true" || $value === true)) {
						$this->_decompression($attributes["name"], $fileUpload);
					}
				}

				if (substr($key,0,10) == "summaxsize") {
					$summaxsize_ini = $value;
					//if (strlen($key) == 10) {
					//	$summaxsize_ini["default"] = $value;
					//} else if (is_numeric(substr($key,11,strlen($key)-12))) {
					//	$summaxsize_ini[substr($key,11,strlen($key)-12)] = $value;
					//}
				}

				//end_add code 

				if (substr($key,0,7) == "maxsize") {
					if (strlen($key) == 7) {
						$maxsize_ini["default"] = $value;
					} else if (is_numeric(substr($key,8,strlen($key)-9))) {
						$maxsize_ini[substr($key,8,strlen($key)-9)] = $value;
					}
				}

				if (substr($key,0,4) == "type") {
					$typeArray = array();
					if (strlen($key) == 4) {
						$typeArray = explode(",", $value);
						$type_ini["default"] = $typeArray;
					} else if (is_numeric(substr($key,5,strlen($key)-6))) {
						$typeArray = explode(",", $value);
						$type_ini[substr($key,5,strlen($key)-6)] = $typeArray;
					}
				}
				//start_add code
				if (substr($key,0,9) == "extension") {
					$extensionArray = array();
					if (strlen($key) == 9) {
						$extensionArray = explode(",", strtolower($value));
						$extension_ini["default"] = $extensionArray;
					} else if (is_numeric(substr($key,10,strlen($key)-11))) {
						$extensionArray = explode(",", strtolower($value));
						$extension_ini[substr($key,10,strlen($key)-11)] = $extensionArray;
					}
				}
				//if ($allow_attachment_flag==_ALLOW_ATTACHMENT_ALL && substr($key,0,14) == "allow_mimetype" && ($value === _ON || $value === "true")) {
				if (($key == "allow_extension" || $key == "allow_mimetype") && ($value === 1 || $value === "1" || $value === "true" || $value === true)) {
					if(!isset($config)) {
						$config = $configView->getConfigByConfname(_SYS_CONF_MODID, "allow_extension");
					}
					if(isset($config["conf_value"])) {
						$allow_extension = $config["conf_value"];
						if($allow_extension != "" && $allow_extension != null) {
							$typeSubArray = array();
							$extensionSubArray = explode(",", strtolower($allow_extension));
							if ($key == "allow_mimetype") {
								$count = 0;
								//$uploadsView =& $this->_container->getComponent("uploadsView");
								if(!class_exists("Uploads_View")) {
									include_once WEBAPP_DIR .'/components/uploads/View.class.php';
								}
								$uploadsView =& new Uploads_View;
								foreach($extensionSubArray as $extensionValue) {
									$typeSubArray[$count] = $uploadsView->mimeinfo("type", "." . $extensionValue);
									$count++;
								}
								//if (strlen($key) == 14) {
								if(isset($type_ini["default"]) && is_array($type_ini["default"]) && count($type_ini["default"]) > 0) {
									$type_ini["default"] = array_merge($type_ini["default"], $typeSubArray);
								} else {
									$type_ini["default"] = $typeSubArray;
								}
								//} else if (is_numeric(substr($key,15,strlen($key)-16))) {
								//	if(count($typeArray) > 0) {
								//		$type_ini[substr($key,15,strlen($key)-16)] = array_merge($typeArray, $typeSubArray);
								//	} else {
								//		$type_ini[substr($key,15,strlen($key)-16)] = $typeSubArray;
								//	}
								//}
							} else {
								// 拡張子
								if(isset($extension_ini["default"]) && is_array($extension_ini["default"]) && count($extension_ini["default"]) > 0) {
									$extension_ini["default"] = array_merge($extension_ini["default"], $extensionSubArray);
								} else {
									$extension_ini["default"] = $extensionSubArray;
								}
							}
						}
					}
				}
				if(defined($value)) {
					$value = constant($value);
				}
				if (substr($key,0,10) == "widthError") {
					if (strlen($key) == 10) {
						$widthError_ini["default"] = $value;
					} else if (is_numeric(substr($key,11,strlen($key)-12))) {
						$widthError_ini[substr($key,11,strlen($key)-12)] = $value;
					}
				}
				if (substr($key,0,11) == "heightError") {
					if (strlen($key) == 11) {
						$heightError_ini["default"] = $value;
					} else if (is_numeric(substr($key,12,strlen($key)-13))) {
						$heightError_ini[substr($key,12,strlen($key)-13)] = $value;
					}
				}
				if (substr($key,0,12) == "sumsizeError") {
					$sumsizeError_ini = $value;
					//if (strlen($key) == 12) {
					//	$sumsizeError_ini["default"] = $value;
					//} else if (is_numeric(substr($key,13,strlen($key)-14))) {
					//	$sumsizeError_ini[substr($key,13,strlen($key)-14)] = $value;
					//}
				}
				//end_add code 
				if (substr($key,0,9) == "sizeError") {
					if (strlen($key) == 9) {
						$sizeError_ini["default"] = $value;
					} else if (is_numeric(substr($key,10,strlen($key)-11))) {
						$sizeError_ini[substr($key,10,strlen($key)-11)] = $value;
					}
				}

				if (substr($key,0,9) == "typeError") {
					if (strlen($key) == 9) {
						$typeError_ini["default"] = $value;
					} else if (is_numeric(substr($key,10,strlen($key)-11))) {
						$typeError_ini[substr($key,10,strlen($key)-11)] = $value;
					}
				}

				if (substr($key,0,14) == "extensionError") {
					if (strlen($key) == 14) {
						$extensionError_ini["default"] = $value;
					} else if (is_numeric(substr($key,15,strlen($key)-16))) {
						$extensionError_ini[substr($key,15,strlen($key)-16)] = $value;
					}
				}

				if (substr($key,0,11) == "noFileError") {
					if (strlen($key) == 11) {
						$noFileError_ini["default"] = $value;
					} else if (is_numeric(substr($key,12,strlen($key)-13))) {
						$noFileError_ini[substr($key,12,strlen($key)-13)] = $value;
					} else if (substr($key,12,7) == "whether") {
						$noFileError_ini["whether"] = $value;
						$noFileError_whether = 0;
					}
				}
			}

			//関連配列
			$error = $fileUpload->getError();
			$extension_check = $fileUpload->checkExtension($extension_ini);

			$mime_type_check = $fileUpload->checkMimeType($type_ini);

           if($resize) {
				//指定された大きさ(解像度)を取得
				$resolution = $request->getParameter('resolution');

				$maxwidth = array();
				$maxheight = array();

				//解像度により maxwidthを設定(maxheightは自動設定)
				switch ($resolution) {
					//「実寸大」の場合はリサイズしない
					case 'asis':
						break;

					//「大」の場合
					case 'large':
						// 幅、高さ設定値を取得
						$maxwidth['default'] = _UPLOAD_RESOLUTION_IMAGE_LARGE_WIDTH;
						$maxheight['default'] = _UPLOAD_RESOLUTION_IMAGE_LARGE_HEIGHT;
						//リサイズ処理
						$fileUpload->resizeFile($maxwidth, $maxheight);
						break;

					//「中」の場合
					case 'middle':
						// 幅、高さ設定値を取得
						$maxwidth['default'] = _UPLOAD_RESOLUTION_IMAGE_MIDDLE_WIDTH;
						$maxheight['default'] = _UPLOAD_RESOLUTION_IMAGE_MIDDLE_HEIGHT;
						//リサイズ処理
						$fileUpload->resizeFile($maxwidth, $maxheight);
						break;

					//「小」の場合
					case 'small':
						// 幅、高さ設定値を取得
						$maxwidth['default'] = _UPLOAD_RESOLUTION_IMAGE_SMALL_WIDTH;
						$maxheight['default'] = _UPLOAD_RESOLUTION_IMAGE_SMALL_HEIGHT;
						//リサイズ処理
						$fileUpload->resizeFile($maxwidth, $maxheight);
						break;

					//「標準」、指定なしの場合
					default:
						//各モジュールのmaple.iniに定義されたサイズでリサイズ処理
						$fileUpload->resizeFile($maxwidth_ini, $maxheight_ini);
						break;
				}
			}

			$filesize_check = $fileUpload->checkFilesize($maxsize_ini);

			$filewidth_check = $fileUpload->checkFilewidth($maxwidth_ini);

			$fileheight_check = $fileUpload->checkFileheight($maxheight_ini);

			$message = "";
			//以下はforeachで各ファイルでエラーチェックを行う
			$first_key = null;
			foreach ($error as $key => $val) {
				if($first_key === null) {
					$first_key = $key;
				}
				if ($val != UPLOAD_ERR_OK) {// PHP自体が感知するエラーが発生した場合
					if ($val == UPLOAD_ERR_INI_SIZE) {
						//$errorList->setType(UPLOAD_ERROR_TYPE);
						if (isset($attributes["iniSizeError"])) {
							$message = $attributes["iniSizeError"];
						} else {
							//start_change code
							$message = _FILE_UPLOAD_ERR_MAX_FILESIZE_INI;
							//end_change code
						}
						$fileUpload->setErrorMes($key, $message);
						//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						break;
					} else if ($val == UPLOAD_ERR_FORM_SIZE) {
						//$errorList->setType(UPLOAD_ERROR_TYPE);
						if (isset($attributes["formSizeError"])) {
							$message = $attributes["formSizeError"];
						} else {
							$message = _FILE_UPLOAD_ERR_MAX_FILESIZE;
						}
						$fileUpload->setErrorMes($key, $message);
						//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						break;
					} else if ($val == UPLOAD_ERR_PARTIAL) {
						//$errorList->setType(UPLOAD_ERROR_TYPE);
						if (isset($attributes["partialError"])) {
							$message = $attributes["partialError"];
						} else {
							$message = _FILE_UPLOAD_ERR_PART_OF_FILE;
						}
						//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						$fileUpload->setErrorMes($key, $message);
						break;
					} else if ($val == UPLOAD_ERR_NO_FILE) {
						if (isset($noFileError_ini[$key])) {
							//$errorList->setType(UPLOAD_ERROR_TYPE);
							$message = $noFileError_ini[$key];
							$fileUpload->setErrorMes($key, $message);
							//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						}else if (isset($noFileError_ini["default"])) {
							//$errorList->setType(UPLOAD_ERROR_TYPE);
							$message = $noFileError_ini["default"];
							$fileUpload->setErrorMes($key, $message);
							//$errorList->add($fileUpload->getName()."[".$key."]", $message);
							//break;
						} else if (isset($noFileError_ini["whether"])) {
							$noFileError_whether = $noFileError_whether +1;
						}
					}
				}else {// PHP自体が感知するエラーは発生していない場合
					//
					//アップロード不可
					//
					if($allow_attachment_flag == _ALLOW_ATTACHMENT_NO ||
						 ($allow_attachment_flag == _ALLOW_ATTACHMENT_IMAGE && $attributes_allow_attachment_flag ==_ALLOW_ATTACHMENT_ALL)) {
						//var_dump($allow_attachment_flag);
						//var_dump($attributes_allow_attachment_flag);
						$message = _FILE_UPLOAD_ERR_FAILURE;
						$fileUpload->setErrorMes($key, $message);
						continue;
					}
	
					//
					// maple.iniで設定されたサイズを超えていた場合
					//
					if (count($maxsize_ini) > 0) {
						if (!$filesize_check[$key]) {
							//$errorList->setType(UPLOAD_ERROR_TYPE);
							if (isset($sizeError_ini[$key])) {
								$message = vsprintf($sizeError_ini[$key],$maxsize_ini[$key]);	//$sizeError_ini[$key];
							}else if (isset($sizeError_ini["default"])) {
								$message = vsprintf($sizeError_ini["default"],$maxsize_ini["default"]);
							} else {
								$message = _FILE_UPLOAD_ERR_FAILURE;
							}
							$fileUpload->setErrorMes($key, $message);
							continue;
							//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						}
					}

					//
					// maple.iniで設定されたMIME-Typeではなかった場合
					//
					if (count($type_ini) > 0) {
						if (!$mime_type_check[$key]) {
							//$errorList->setType(UPLOAD_ERROR_TYPE);
							if (isset($typeError_ini[$key])) {
								$message = $typeError_ini[$key];
							}else if (isset($typeError_ini["default"])) {
								$message = $typeError_ini["default"];
							} else {
								$message = _FILE_UPLOAD_ERR_FILENAME_REJECRED;
							}
							$fileUpload->setErrorMes($key, $message);
							continue;
							//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						}
					}


					//
					// maple.iniで設定された拡張子ではなかった場合
					//
					if (count($extension_ini) > 0) {
						if (!$extension_check[$key]) {
							//$errorList->setType(UPLOAD_ERROR_TYPE);
							if (isset($extensionError_ini[$key])) {
								$message = $extensionError_ini[$key];
							}else if (isset($extensionError_ini["default"])) {
								$message = $extensionError_ini["default"];
							} else {
								$message = _FILE_UPLOAD_ERR_FILENAME_REJECRED;
							}
							$fileUpload->setErrorMes($key, $message);
							continue;
							//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						}
					}

					//
					// maple.iniで設定されたWidthを超えていた場合
					//
					if (count($maxwidth_ini) > 0) {
						if (!$filewidth_check[$key]) {
							//$errorList->setType(UPLOAD_ERROR_TYPE);
							if (isset($widthError_ini[$key])) {
								$message = vsprintf($widthError_ini[$key],$maxwidth_ini[$key]);
							}else if (isset($widthError_ini["default"])) {
								$message = vsprintf($widthError_ini["default"],$maxwidth_ini["default"]);
							} else {
								$message = _FILE_UPLOAD_ERR_FAILURE;
							}
							$fileUpload->setErrorMes($key, $message);
							continue;
							//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						}
					}

					//
					// maple.iniで設定されたHeightを超えていた場合
					//
					if (count($maxheight_ini) > 0) {
						if (!$fileheight_check[$key]) {
							//$errorList->setType(UPLOAD_ERROR_TYPE);
							if (isset($heightError_ini[$key])) {
								$message = vsprintf($heightError_ini[$key],$maxheight_ini[$key]);
							}else if (isset($heightError_ini["default"])) {
								$message = vsprintf($heightError_ini["default"],$maxheight_ini["default"]);
							} else {
								$message = _FILE_UPLOAD_ERR_FAILURE;
							}
							$fileUpload->setErrorMes($key, $message);
							continue;
							//$errorList->add($fileUpload->getName()."[".$key."]", $message);
						}
					}
				}
			}
			if (isset($noFileError_whether) && count($error) == $noFileError_whether) {
				//$errorList->setType(UPLOAD_ERROR_TYPE);
				$message = $noFileError_ini["whether"];
				//少なくとも1つファイルを指定しないとならないエラーの場合、先頭のfileのエラーリストに追加する key=0固定
				$fileUpload->setErrorMes($first_key, $message);
				//$errorList->add($fileUpload->getName(), $message);
			}

			//
			// start_add code
			// ルーム毎の最大容量チェック 
			//
			$room_id = intval($request->getParameter("room_id"));
			if($room_id != 0) {
				$getdata =& $container->getComponent("GetData");
				$pages = $getdata->getParameter("pages");
				if(!isset($pages[$room_id])) {
					$pagesView =& $container->getComponent("pagesView");
					$page = $pagesView->getPageById($room_id);
				} else {
					$page =& $pages[$room_id];
				}
				$max_capacity = 0;
				if($page['private_flag'] == _ON) {
					$max_capacity = $session->getParameter("_private_max_size");
				} else if($page['space_type'] == _SPACE_TYPE_GROUP) {
					$upload_max_capacity_group = $configView->getConfigByConfname(_SYS_CONF_MODID, "upload_max_capacity_group");
					if(isset($upload_max_capacity_group['conf_value'])) {
						$max_capacity = intval($upload_max_capacity_group['conf_value']);
					} else {
						$max_capacity = 0;
					}
				} else {
					$upload_max_capacity_public = $configView->getConfigByConfname(_SYS_CONF_MODID, "upload_max_capacity_public");
					if(isset($upload_max_capacity_public['conf_value'])) {
						$max_capacity = intval($upload_max_capacity_public['conf_value']);
					} else {
						$max_capacity = 0;
					}
				}
				if($max_capacity != 0) {
					$filesize = $fileUpload->getFilesize();
					$sum_size = 0;
					foreach($filesize as $size) {
						$sum_size = $size;
					}
					$db =& $container->getComponent("DbObject");
					$db_sum_size = intval($db->sumExecute("uploads", "file_size", array("room_id"=>$room_id)));
					$sum_size += $db_sum_size;
					if ($max_capacity < $sum_size) {
						if($max_capacity-$db_sum_size < 0) {
							$rest_size = 0;
						} else {
							$rest_size = $max_capacity-$db_sum_size;
						}
						// エラー
						$message = sprintf(_FILE_UPLOAD_ERR_MAX_CAPACITY, $page['page_name'], $fileView->formatSize($max_capacity), $fileView->formatSize($rest_size));

						// key=0固定
						$stopper = _ON;
						$fileUpload->setErrorMes($first_key, $message);
					}
				}

			}
			// 合計サイズチェック
			if(isset($summaxsize_ini)) {
				$filesize = $fileUpload->getFilesize();
				$sum_size = 0;
				foreach($filesize as $size) {
					$sum_size = $size;
				}
				if ($summaxsize_ini < $sum_size) {
					// エラー
					if(isset($sumsizeError_ini)) {
						$message = $sumsizeError_ini;
					} else {
						$message = sprintf(_FILE_UPLOAD_ERR_SUMSIZE_SIZE, $fileView->formatSize($summaxsize_ini));
					}
					// key=0固定
					$stopper = _ON;
					$fileUpload->setErrorMes($first_key, $message);
				}
			}

			//end_add code 


			if($message != "" && $stopper == _ON) {
				//stopperがONならば、エラーリストに追加し、アクションを呼ばない(エラーリスト追加)
				//最後に発生したエラーをエラーリストへ追加
				$errorList->setType(UPLOAD_ERROR_TYPE);
				$errorList->add($fileUpload->getName(), $message);
			}
		} else {
			$log->trace("フィールド名が指定されていません", "Filter_FileUpload#execute");
		}

		$filterChain =& $container->getComponent("FilterChain");
		$filterChain->execute();

		$log->trace("Filter_FileUploadの後処理が実行されました", "Filter_FileUpload#execute");
	}

	function constantDef($str) {
		if(defined($str)) {
			return constant($str);	
		} else {
			return $str;
		}
	}

	function _decompression($name, &$fileUpload) {
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$session =& $container->getComponent("Session");
		$file_extra =& $container->getComponent("File");

		$commonMain =& $container->getComponent("commonMain");
		$fileAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/Action.class.php', "File_Action", "fileAction");

		$action_name = $actionChain->getCurActionName();
		$pathList = explode("_", $action_name);

		$cur_sess_id = $session->getID();

		require_once "File/Archive.php";

		//
		// テンポラリーディレクトリ作成
		//
		if(!file_exists(FILEUPLOADS_DIR.$pathList[0])) {
			mkdir(FILEUPLOADS_DIR.$pathList[0], octdec(_UPLOAD_FOLDER_MODE));
		}
		$file_path = $pathList[0]."/".strtolower($cur_sess_id);
		if (file_exists(FILEUPLOADS_DIR.$file_path)) {
			$result = $fileAction->delDir(FILEUPLOADS_DIR.$file_path);
			if ($result === false) {
			return false;
			}
		}

		mkdir(FILEUPLOADS_DIR.$file_path, octdec(_UPLOAD_FOLDER_MODE));

		//
		// 圧縮ファイル取得
		//
		$files = $file_extra->getParameterRef($name);

		$file_name = FILEUPLOADS_DIR.$file_path."/".$files['name'];
		// 
		// TODO:cabinetの場合、圧縮ファイルをFileクラスに登録したものを解凍しないといけないため
		// $fileUpload->moveで移動してしまうとエラーとなる。
		// 
		$fileUpload->move(0, $file_name);

		//
		// 圧縮ファイル解凍
		//
		File_Archive::extract(File_Archive::read($file_name."/"), $dest = FILEUPLOADS_DIR.$file_path);

		//
		// 圧縮ファイル削除
		//
		$fileAction->delDir($file_name);

		//
		// 解凍したファイルをアップロードファイルとしてセット
		//
		$commonMain =& $container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");

		$uploadsAction->setFileByPath(FILEUPLOADS_DIR.$file_path, $name);
	}
}
?>