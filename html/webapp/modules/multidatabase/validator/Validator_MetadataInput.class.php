<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック
 * 必須チェック
 *  リクエストパラメータ
 *  var $registration_id = null;
 *  var $items = null;
 *
 * @package	 NetCommons.validator
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Multidatabase_Validator_MetadataInput extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値
	 *
	 * @param   string  $errStr	 エラー文字列(未使用：エラーメッセージ固定)
	 * @param   array   $params	 オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		if(!isset($attributes['multidatabase_id'])) {
			return $errStr;
		}

		// container取得
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		$fileUpload =& $container->getComponent("FileUpload");
		$uploadsAction =& $container->getComponent("uploadsAction");
		$uploadsView =& $container->getComponent("uploadsView");

		$files = $uploadsAction->uploads();
		$errormes = $fileUpload->getErrorMes();

		foreach(array_keys($files) as $metadata_id) {
			if(isset($attributes['datas'][$metadata_id]) && $attributes['datas'][$metadata_id] == _ON) {
				continue;
			}
			$attributes['datas'][$metadata_id] = $files[$metadata_id];
		}
		foreach(array_keys($errormes) as $metadata_id) {
			if(isset($attributes['datas'][$metadata_id]) && $attributes['datas'][$metadata_id] == _ON) {
				continue;
			}
			$attributes['datas'][$metadata_id]['error_mes'] = $errormes[$metadata_id];
		}

		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		$mdbView =& $container->getComponent("mdbView");

		$order_params = array(
				"display_pos" => "ASC",
				"display_sequence" => "ASC"
		);
		$show_metadatas =& $mdbView->getMetadatas(array("multidatabase_id"=>$attributes['multidatabase_id']), $order_params);

		if($show_metadatas === false) {
			$errStr = $smartyAssign->getLang("_invalid_input");
			return $errStr;
		}

		$datas = $attributes['datas'];
		$errors = array();
		$input_datas = array();

		$session =& $container->getComponent("Session");
		$escapeText =& $container->getComponent("escapeText");

		$mobile = $session->getParameter("_mobile_flag");	   //携帯対応のため追加。Add by AllCreator

		//許可するプロトコルを読み込み
		$db =& $container->getComponent("DbObject");
		$sql = "SELECT protocol FROM {textarea_protocol}";
		$protocolArr = $db->execute($sql);
		if ($protocolArr === false) {
			return _INVALID_INPUT;
		}

		foreach($show_metadatas as $metadata) {
			$metadata_id = $metadata['metadata_id'];

			$input_datas[$metadata_id] = "";
			if(isset($datas[$metadata_id])) {
				$input_datas[$metadata_id] = $datas[$metadata_id];
			}

			if($metadata['type'] == MULTIDATABASE_META_TYPE_TEXTAREA) {
				$temp_text = preg_replace("/\r\n/", "\n", $datas[$metadata_id]);
				$input_datas[$metadata_id] = $escapeText->escapeText($temp_text);
			}

			if($metadata['type'] == MULTIDATABASE_META_TYPE_TEXT) {
				$input_datas[$metadata_id] = $escapeText->escapeText($datas[$metadata_id]);
			}

			if($metadata['type'] == MULTIDATABASE_META_TYPE_WYSIWYG) {
				$input_datas[$metadata_id] = $escapeText->escapeWysiwyg($datas[$metadata_id]);
			}

			if ($metadata['type'] == MULTIDATABASE_META_TYPE_MULTIPLE && $input_datas[$metadata_id] != "") {
				$input_datas[$metadata_id] = implode("|", $input_datas[$metadata_id]);
			}

			if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE
					|| $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) {

				$objectName = $smartyAssign->getLang("mdb_file_password_display");
				$errorMsg = $smartyAssign->getLang("_required");
				//ファイルと画像が削除される場合
				if(isset($datas[$metadata_id]) && $datas[$metadata_id] == _ON) {
					continue;
				}

				if(empty($datas[$metadata_id]['error_mes'])) {
					if($metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) {
						if( empty($datas[$metadata_id]['physical_file_name'])){
							//
							//携帯がファイルアップロードに対応していない場合
							//(携帯がファイルアップロードに対応していない場合、physical_file_nameが空になる)
							//
							if($metadata['require_flag'] == _ON ){
								//ファイルアップロード未対応携帯なのに、このファイルは必須扱いになっている...
								//
								//つまり、この携帯からは登録できないので、PCから登録していただくか、
								//管理者にお願いして、必須から任意にかえていただくことを薦めます。
								//
								$errors['mdb_metadatas_'. $metadata_id] = MULTIDATABASE_MOBILE_FILE_UPLOAD_ERR_NOABILITY;
							}
							else{
								//ファイルは任意なので影響なし
							}
							continue;
						}
						else{
							//ファイルアップロードに対応している携帯
							//なので引き続き、画像ファイルの拡張子を調べます
						}

						$pathinfo = $uploadsView->checkExtension($datas[$metadata_id]['physical_file_name'], _UPLOAD_IMAGEFILE_EXTENSION);
						if($pathinfo === false) {
							$errors['mdb_metadatas_'. $metadata_id] = _FILE_UPLOAD_ERR_IMAGETYPE;
						}
						$size = $mdbView->getImageBlockSize($datas[$metadata_id]['physical_file_name']);
						$input_datas[$metadata_id]['block_img_width'] = $size[0];
						$input_datas[$metadata_id]['block_img_height'] = $size[1];
					}

					if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE ) {
						if( empty($datas[$metadata_id]['physical_file_name'])){
							//
							//携帯がファイルアップロードに対応していない場合
							//(携帯がファイルアップロードに対応していない場合、physical_file_nameが空になる)
							//
							if($metadata['require_flag'] == _ON ){
								//ファイルアップロード未対応携帯なのに、このファイルは必須扱いになっている...
								//
								//つまり、この携帯からは登録できないので、PCから登録していただくか、
								//管理者にお願いして、必須から任意にかえていただくことを薦めます。
								//
								$errors['mdb_metadatas_'. $metadata_id] = MULTIDATABASE_MOBILE_FILE_UPLOAD_ERR_NOABILITY;
							}
							else{
								//ファイルは任意なので影響なし
							}
							continue;
						}
						else{
							//ファイルアップロードに対応している携帯
							//
							//なので引き続き、_CheckDownloadPassword()を実行する。
						}

						$this->_CheckDownloadPassword($metadata_id, $attributes, $objectName, $errorMsg, $errors);
					}
					continue;
				}

				if($datas[$metadata_id]['error_mes'] != _FILE_UPLOAD_ERR_UPLOAD_NOFILE
						|| ($metadata['require_flag'] == _ON
							&& $datas[$metadata_id]['error_mes'] == _FILE_UPLOAD_ERR_UPLOAD_NOFILE)) {
					if(!empty($attributes['content_id'])) {
						$input_datas[$metadata_id] = $session->getParameter(array("multidatabase_content", $attributes['block_id'], $metadata_id));
						if(!empty($input_datas[$metadata_id])) {
							if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE ) {
								$this->_CheckDownloadPassword($metadata_id, $attributes, $objectName, $errorMsg, $errors);
							}
							continue;
						}
					}

					$errors['mdb_metadatas_'. $metadata_id] = $metadata['name'].$datas[$metadata_id]['error_mes'];
					continue;

				}else {
					$input_datas[$metadata_id] = $session->getParameter(array("multidatabase_content", $attributes['block_id'], $metadata_id));
					if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE && !empty($input_datas[$metadata_id])) {
						$this->_CheckDownloadPassword($metadata_id, $attributes, $objectName, $errorMsg, $errors);
					}
				}

				continue;
			}
			if($metadata['require_flag'] == _ON) {
				if(is_array($input_datas[$metadata_id])) {
					$chkdata = implode('', $input_datas[$metadata_id]);
				} else {
					$chkdata = $input_datas[$metadata_id];
				}
				if($chkdata == '') {
					$errors['mdb_metadatas_'. $metadata_id] = sprintf($smartyAssign->getLang('_required'), $metadata['name']);
					continue;
				}
			}
			if($metadata['type'] == MULTIDATABASE_META_TYPE_LINK && !empty($datas[$metadata_id]) &&
					!preg_match("/^\.\//", $datas[$metadata_id]) && !preg_match("/^\.\.\//", $datas[$metadata_id])) {
				$error = true;
				foreach ($protocolArr as $i=>$protocol) {
					if (preg_match("/^" . $protocol["protocol"] . "/", $datas[$metadata_id])) {
						$error = false;
						continue;
					}
				}
				if ($error) {
					return _INVALID_INPUT;
				}
			}

			if ($metadata['type'] == MULTIDATABASE_META_TYPE_MAIL) {
				$input_datas[$metadata_id] = $escapeText->convertSingleByte($datas[$metadata_id]);

				$regex = '/^' . MULTIDATABASE_INPUT_EMAIL_ADRR . MULTIDATABASE_INPUT_EMAIL_HOST . '$/i';
				if ($input_datas[$metadata_id] != "" && !preg_match($regex, $input_datas[$metadata_id])) {
					$errors['mdb_metadatas_'. $metadata_id] = sprintf(_FORMAT_WRONG_ERROR, $metadata['name']);
				}
			}

			if ($metadata['type'] == MULTIDATABASE_META_TYPE_DATE && !empty($datas[$metadata_id])) {
				if ($mobile == _ON && empty($datas[$metadata_id]["year"]) && empty($datas[$metadata_id]["month"]) && empty($datas[$metadata_id]["day"])) {
					$input_datas[$metadata_id] = "";
				} else {
					$ret = $mdbView->checkDate($datas[$metadata_id]);
					if ($ret == "") {
						$errors['mdb_metadatas_'. $metadata_id] = sprintf(_INVALID_DATE, $metadata['name']);
					} else {
						$input_datas[$metadata_id] = timezone_date($ret."000000", true);
					}
				}
			}
		}

		if (!empty($errors)) {
			$errStr = "";
			foreach($errors as $key => $val) {
				$errStr .= $val."<br />";
			}

			return $errStr;
		}

		$session->setParameter(array("multidatabase_content", $attributes['block_id']), $input_datas);

		return;
	}

	function _CheckDownloadPassword($metadata_id, $attributes, $objectName, $errorMsg, &$errors) {
		//ファイルパスワードのチェック
		if(isset($attributes['password_checkbox'][$metadata_id]) && $attributes['password_checkbox'][$metadata_id] == _ON) {
			if(empty($attributes['passwords'][$metadata_id])) {
				$errors['mdb_metadata_file_password_input_'. $metadata_id] = sprintf($errorMsg, $objectName);
			}else if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $attributes['passwords'][$metadata_id])) {
				// 半角英数または、記号
				$errors['mdb_metadata_file_password_input_'. $metadata_id] = sprintf(_HALFSIZESYMBOL_ERROR, $objectName);
			}
		}
		else{
			if( isset($attributes['passwords'][$metadata_id]) && $attributes['passwords'][$metadata_id] != "" ){
				$errors['mdb_metadata_file_password_input_'. $metadata_id] = sprintf(MULTIDATABASE_MOBILE_FILE_PASSWORD_INVALID_COMBINATION, $objectName);
			}
		}
	}
}
?>