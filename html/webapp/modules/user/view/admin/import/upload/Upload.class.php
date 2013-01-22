<?php

/**
 * 会員管理>>インポート>>アップロード
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class User_View_Admin_Import_Upload extends Action
{
	// リクエストパラメータを受け取るため
	var $user_import_option_data_set = null;
	var $user_import_option_detail_set = null;

	//使用コンポーネント
	var $session = null;
	var $actionChain = null;
	var $uploadsAction = null;
	var $usersView = null;
	var $authoritiesView = null;

	// 値をセットするため
	var $duplicate_data = _OFF;		// 重複データオプション
	var $detail_res = _OFF;			// 詳細結果オプション
	var $check_items_num = null;
	var $loginid_list = null;		// インポートファイル内の重複確認のためのログインIDリスト
	var $handle_list = null;		// インポートファイル内の重複確認のためのハンドルリスト

	var $email_list_all = null;		//インポートファイル内の重複確認のためのすべてのeメールリスト
	var $email_column_cnt = 0;		//ユーザ情報中のemail又はmobile_emailの個数
	var $email_column_name = array(); //ユーザ情報中にemail又はmobile_emailの見出し

	/**
	 * インポートファイルのアップロード
	 *
	 * @access  public
	 */
	function execute()
	{
		set_time_limit(USER_TIME_LIMIT);
		// メモリ最大サイズ設定
		ini_set('memory_limit', -1);

		$errorList =& $this->actionChain->getCurErrorList();
		$errUser_cnt = 0;

		if ($this->user_import_option_data_set == "1") $this->duplicate_data = _ON;
		if ($this->user_import_option_detail_set == "1") $this->detail_res = _ON;

		// ファイルアップロード
		$garbage_flag = _ON;
		$filelist = $this->uploadsAction->uploads($garbage_flag);
		if(isset($filelist['error_mes']) && $filelist['error_mes'] != "") {
			$errorList->add(get_class($this), sprintf(_FILE_UPLOAD_ERR_FAILURE."(%s)", $filelist['error_mes']));
			return 'error';
		}else if($filelist[0]['extension'] != "csv") {
			$errorList->add(get_class($this), sprintf(_FILE_UPLOAD_ERR_FILENAME_REJECRED."(%s)", $filelist[0]['file_name']));
			$this->_delImportFile(FILEUPLOADS_DIR."user/".$filelist[0]['physical_file_name']);
			return 'error';
		}

		$file = FILEUPLOADS_DIR."user/".$filelist[0]['physical_file_name'];
		//$file = WEBAPP_DIR."/uploads/user/".$filelist[0]['physical_file_name'];
		$handle = fopen($file, 'r');
		if($handle == false) {
			$errorList->add(get_class($this), sprintf(USER_IMPORT_UPLOAD_OPENERR."(%s)", $file));
			$this->_delImportFile($file);
			return 'error';
		}

		$users_admin = $this->usersView->getUsers(array("user_authority_id" => _AUTH_ADMIN));
		if (isset($users_admin) && is_array($users_admin)) {
			$showitems = $this->usersView->getShowItems($users_admin[0]['user_id'], _AUTH_ADMIN, null);
		}
		if (!isset($showitems) || !is_array($showitems)) {
			$errorList->add(get_class($this), sprintf("show items error"));
			$this->_delImportFile($file);
			return 'error';
		}

		// データ取得
		// ヘッダチェック
		$row_data_headers = fgets($handle);
		$row_data_headers = mb_convert_encoding($row_data_headers, "UTF-8", "SJIS");
		if (empty($row_data_headers)) {
			$errorList->add(get_class($this), sprintf(USER_IMPORT_UPLOAD_NODATA."(%s)", $filelist[0]['file_name']));
			$this->_delImportFile($file);
			return 'error';
		}
		$row_data_headers = explode(",", $row_data_headers);
		$item_count = count($row_data_headers);

		$this->session->removeParameter(array("user", "import"));
		$row_data_headers_disp = null;
		$e = preg_quote('"');
		foreach($row_data_headers as $i=>$row_data_header) {
			$item_found = _OFF;
			$public = "0"; $reception = "0";
			$row_data_header = trim($row_data_header);
			$row_data_header = preg_replace('/^'.$e.'?(.*)'.$e.'$/su', '$1', $row_data_header);
			$row_data_header = preg_replace('/'.$e.$e.'/su', $e, $row_data_header);
			$row_data_headers[$i] = $row_data_header;
			foreach($showitems as $item_list) {
				foreach($item_list as $showitem) {
					$strncmp = strncmp($row_data_header, $showitem['item_name'], strlen($showitem['item_name']));
					if ($strncmp != 0 && $showitem['item_name'] == "ID") {
						$strncmp = strncmp($row_data_header, strtolower($showitem['item_name']), strlen($showitem['item_name']));
						if ($strncmp == 0) {
							$row_data_header = $showitem['item_name'];
							$row_data_headers[$i] = $showitem['item_name'];
						}
					}
					if (!$strncmp) {
						if (!strcmp($row_data_header, sprintf(USER_IMPORT_RECEPTION_EMAIL, $showitem['item_name']))) {
							$reception = "1";
						} else if (!strcmp($row_data_header, sprintf(USER_IMPORT_PUBLIC_FLAG, $showitem['item_name']))) {
							$public = "1";
						} else if (strcmp($row_data_header, $showitem['item_name'])) {
							continue;
						}
						$item_poss[] = array("name" => $row_data_header,
											"item_id" => $showitem['item_id'],
											"public_flag" => $public,
											"reception" => $reception,
											"showitem" => $showitem);
						$item_found = _ON;
						$row_data_headers_disp[] = $row_data_header;

						if ( ($reception != "1" ) && 
							($public != "1" ) && 
							(($showitem['type'] == 'email') || ($showitem['type'] == 'mobile_email')) ) {
							$this->email_column_cnt++;
							$this->email_column_name[] = $showitem['item_name'];
						}

						break;
					}
				}
				if ($item_found == _ON) break;
			}
			if ($item_found != _ON) {
				$item_poss[] = array("name" => trim($row_data_header),
									"item_id" => "none",
									"public_flag" => $public,
									"reception" => $reception,
									"showitem" => $showitem);
			}
		}
		$row_data_headers_disp[] = USER_IMPORT_DATACHK_RES;
		$this->session->setParameter(array("user", "import", "dispheader"), $row_data_headers_disp);

		// データチェック
		$idx = 0;
		$chkusers_num = 0;
		while (!feof($handle)) {
			$items = null; $items_public = null; $items_reception = null;
			$row_data_user_str = fgets($handle);
			if (empty($row_data_user_str)) continue;
			$row_data_user_str = mb_convert_encoding($row_data_user_str, "UTF-8", "SJIS");
			$row_data_user_str_len = strlen($row_data_user_str);
			$row_data_user = null;
			$row_data_user_str_idx = 0;
			$row_data_user_idx = 0;
			$sep_true = 1;
			while ($row_data_user_str_idx < $row_data_user_str_len) {
				$row_data_user[$row_data_user_idx] = "";
				while (($sep_true == 0) || ($row_data_user_str[$row_data_user_str_idx] != ",")) {
					$row_data_user[$row_data_user_idx] = $row_data_user[$row_data_user_idx].$row_data_user_str[$row_data_user_str_idx];
					if ($row_data_user_str[$row_data_user_str_idx] == '"') {
						if ($sep_true == 1) $sep_true = 0;
						else $sep_true = 1;
					}
					$row_data_user_str_idx++;

					if ($row_data_user_str_idx >= $row_data_user_str_len) {
						if ($sep_true == 0) {
							$row_data_user_str = "";
							while (!feof($handle) && empty($row_data_user_str)) {
								$row_data_user_str = fgets($handle);
							}
							$row_data_user_str = mb_convert_encoding($row_data_user_str, "UTF-8", "SJIS");
							$row_data_user_str_len = strlen($row_data_user_str);
							$row_data_user_str_idx = 0;
						} else {
							break;
						}
					}
				}
				$row_data_user_str_idx++;
				$row_data_user_idx++;
			}
			while ($row_data_user_idx < count($row_data_headers)) {
				$row_data_user[$row_data_user_idx] = "";
				$row_data_user_idx++;
			}
			$idx++;

			$row_data_user_disp = null;
			for ($item_num=0; $item_num<count($row_data_user); $item_num++) {
				$row_data_item = trim($row_data_user[$item_num]);
				if (strtolower($row_data_item) == USER_IMPORT_SPACE) $row_data_item = "";

				// "\"" の削除
				if (strpos($row_data_item, "\"") !== FALSE) {
					$row_data_item_tmp = "";
					$row_data_item_len = strlen($row_data_item);

					if (($row_data_item[0] == '"') && ($row_data_item[$row_data_item_len-1] == '"')) {
						$row_data_item = substr($row_data_item, 1, $row_data_item_len-2);
						$row_data_item_len = $row_data_item_len - 2;
					}
					for ($item_idx=0; $item_idx<$row_data_item_len; $item_idx++) {
						$row_data_item_tmp = $row_data_item_tmp.$row_data_item[$item_idx];
						if ($row_data_item[$item_idx] == '"') {
							$item_idx++;
							if ($item_idx >= $row_data_item_len) break;
							if ($row_data_item[$item_idx] == '"') {
								continue;
							}
							$row_data_item_tmp = $row_data_item_tmp.$row_data_item[$item_idx];
						}
					}
					$row_data_item = $row_data_item_tmp;
				}

				$item_pos = $item_poss[$item_num];
				if (!strcmp($item_pos["item_id"], "none")) continue;

				if ($item_pos["public_flag"] == "1") {
					if (($row_data_item == "") || (($row_data_item != "0") && ($row_data_item != "1"))) {
						$items_public[$item_pos["item_id"]] = _ON;
					} else {
						$items_public[$item_pos["item_id"]] = intval($row_data_item);
					}

					if ($items_public[$item_pos["item_id"]] == _ON) $row_data_user_disp[] = USER_IMPORT_PUBLIC;
					else $row_data_user_disp[] = USER_IMPORT_NOPUBLIC;
				} else if ($item_pos["reception"] == "1") {
					if (($row_data_item == "") || (($row_data_item != "0") && ($row_data_item != "1"))) {
						$items_reception[$item_pos["item_id"]] = _OFF;
					} else {
						$items_reception[$item_pos["item_id"]] = intval($row_data_item);
					}

					if ($items_reception[$item_pos["item_id"]] == _ON) $row_data_user_disp[] = USER_ITEM_ACTIVE_FLAG_ON;
					else $row_data_user_disp[] = USER_ITEM_ACTIVE_FLAG_OFF;
				} else {
					$showitem = $item_pos['showitem'];
					$items[$item_pos["item_id"]] = $this->checkVal($showitem, $row_data_item);

					$row_data_user_disp[] = $this->getChangeName($showitem, $items[$item_pos["item_id"]]);
				}
			}

			// 会員情報の重複チェック
			$user_id="0";
			$attributes = array("user_id" => $user_id,
								"items" => $items,
								"item_public" => $items_public,
								"item_reception" => $items_reception);
			$res = $this->dataCheck($attributes, "error");
			$user_id = $res["userid"];
			$errlists = $res["errlist"];

			// インポートファイル内の重複チェック
			$dataCheck_infile_res = "success";
			$errlists_infile = $this->dataCheck_infile($row_data_user, $row_data_headers);
			if (isset($errlists_infile)) {
				$dataCheck_infile_res = "error";
				foreach($errlists_infile as $errlist_infile) {
					$errlists[] = $errlist_infile;
				}
			}
			$errlists_infile_mail = $this->dataCheck_infile_mail();
			if (isset($errlists_infile_mail)) {
				$dataCheck_infile_res = "error";
				foreach($errlists_infile_mail as $errlist_infile) {
					$errlists[] = $errlist_infile;
				}
			}
			if (!strcmp($user_id, "error") || !strcmp($dataCheck_infile_res, "error")) {
				// データチェックエラー
				$errUser_cnt++;
				$errUser = _ON;
			} else {
				$errlists[] = USER_IMPORT_CHKDATA_NOERR;
				$errUser = _OFF;
			}
			$row_data_user_disp[] = $errlists;

			$this->session->setParameter(array("user", "import", "items", $idx), $items);
			$this->session->setParameter(array("user", "import", "items_public", $idx), $items_public);
			$this->session->setParameter(array("user", "import", "items_reception", $idx), $items_reception);
			$this->session->setParameter(array("user", "import", "userid", $idx), $user_id);
			$this->session->setParameter(array("user", "import", "dispdata", $idx), $row_data_user_disp);
			$this->session->setParameter(array("user", "import", "dispdata_err", $idx), $errUser);

			$chkusers_num = $idx;
			if ($this->detail_res == _ON) {
				if ($errUser_cnt >= USER_IMPORT_CHKDATA_MAXERR) break;
			}
		}
		fclose($handle);
		if($chkusers_num > USER_IMPORT_ROW_NUM) {
			$errorList->add(get_class($this), sprintf(USER_IMPORT_ROW_OVER_ERROR, USER_IMPORT_ROW_NUM));
			$this->_delImportFile($file);
			return 'error';
		}
		//if($errUser) {
			$this->_delImportFile($file);
		//}
		$this->session->setParameter(array("user", "import", "detail_res"), $this->detail_res);
		$this->session->setParameter(array("user", "import", "chkusers_num"), $chkusers_num);
		$this->session->setParameter(array("user", "import", "errUser_cnt"), $errUser_cnt);
	}

	/**
	 * 設定値が設定可能範囲かチェック
	 * @param item　name
	 * @return res
	 * @access private
	 */
	function checkVal($item, $name)
	{
		$res = $name;
		$out_option_items = "";
		if ($name != "") {
			if (isset($item['set_options']) && is_array($item['set_options'])) {
				$options = $item['set_options'];
				$res = ""; $val = "";
				if (!strcmp($item["type"], USER_TYPE_CHECKBOX)) {
					$in_option_items = explode("|", $name);
					foreach($in_option_items as $in_option_item) {
						foreach ($options as $option) {
							if (!strcmp($option['def_options'], $in_option_item)) {
								$val[] = trim($option['def_options']);
								break;
							}
						}
					}
					if ($val == "") $res = $val;
					else $res = implode("|", $val);
				} else {
					foreach ($options as $option) {
						if (!strcmp($option['def_options'], $name)) {
							$val = $option['def_options'];
							break;
						}
					}
					$res = trim($val);
				}
			}
		}

		// 無指定時のデフォルト値設定
		if ($res == "") {
			$res = $this->setDefault($item);
		}
		return $res;
	}

	/**
	 * 設定値を数値から名称に変更
	 * @param item　name
	 * @return name
	 * @access private
	 */
	function getChangeName($item, $name)
	{
		$res = $name;
		$out_option_items = "";
		if ($name != "") {
			if (isset($item['set_options']) && is_array($item['set_options'])) {
				$options = $item['set_options'];
				$res = ""; $val = "";
				if (!strcmp($item["type"], USER_TYPE_CHECKBOX)) {
					$in_option_items = explode("|", $name);
					foreach($in_option_items as $in_option_item) {
						foreach ($options as $option) {
							if (!strcmp($option['def_options'], $in_option_item)) {
								$val[] = trim($option['options']);
								break;
							}
						}
					}
					if ($val == "") $res = $val;
					else $res = implode("|", $val);
				} else {
					foreach ($options as $option) {
						if (!strcmp($option['def_options'], $name)) {
							$val = $option['options'];
							break;
						}
					}
					$res = trim($val);
				}
			}
		}

		return $res;
	}

	/**
	 * デフォルト値のセット
	 * @param item
	 * @return res
	 * @access private
	 */
	function setDefault($item)
	{
		switch ($item['item_name']) {
		case USER_ITEM_TIMEZONE_OFFSET:
			$res = USER_IMPORT_TIMEZONE_DEFAULT;
			break;
		case USER_ITEM_LANG_DIRNAME:
			$res = USER_IMPORT_LANG_DEFAULT;
			break;
		case USER_ITEM_ROLE_AUTHORITY_ID:
			$res = USER_IMPORT_ROLE_DEFAULT;
			break;
		case USER_ITEM_ACTIVE_FLAG:
			$res = USER_IMPORT_ACTIVE_DEFAULT;
			break;
		default:
			$res = "";
			break;
		}

		return trim($res);
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result
	 * @return ret
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}

	/**
	 * 既存会員情報とインポートデータのチェック
	 * @param attributes errStr
	 * @return user_id
	 * @access private
	 */
	function dataCheck($attributes, $errStr)
	{
		$user_id = null;
		$res = null;
		$errlist = null;

		$_system_user_id = $this->session->getParameter("_system_user_id");
		$_user_auth_id = $this->session->getParameter("_user_auth_id");


		$user_id = $attributes['user_id'];
		$edit_flag = false;

		$where_params = array(
							"user_authority_id" => _AUTH_ADMIN		// 管理者固定
						);
		$show_items =& $this->usersView->getItems($where_params, null, null, null, array($this, "_getItemsFetchcallback"));
		if($show_items === false) return $errStr;

		$current_user = null;
		foreach($show_items as $items) {
			if((isset($attributes['items']) && is_array($attributes['items'])) && isset($attributes['items'][$items['item_id']])) {
				$content = $attributes['items'][$items['item_id']];
			} else {
				$content = "";
			}

			if($items['define_flag'] == _ON && defined($items['item_name'])) $items['item_name'] = constant($items['item_name']);
			// 必須入力チェック
			if($items['require_flag'] == _ON) {
				if($content == "") {
					if ($this->duplicate_data == _OFF || !isset($current_user) || $items['tag_name'] != "password") {
						// パスワードで既存会員ならば、エラーにしない
						$errlist[] = sprintf(_REQUIRED, $items['item_name']).$edit_flag;
						$user_id = $errStr;
						continue;
					}
				}
			}
			if($items['tag_name'] == "login_id") {
				// 文字チェック
				$login_id = $content;
				$login_len = strlen($content);

				if($login_len < USER_LOGIN_ID_MINSIZE || $login_len > USER_LOGIN_ID_MAXSIZE) {
					$errlist[] = sprintf(_MAXRANGE_ERROR, USER_ITEM_LOGIN, USER_LOGIN_ID_MINSIZE, USER_LOGIN_ID_MAXSIZE);
					$user_id = $errStr;
				}

				// 半角英数または、記号
				if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $login_id)) {
					$errlist[] = sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_LOGIN);
					$user_id = $errStr;
				}

				// 重複チェック
				$where_params = array("login_id" => $login_id);
				$users =& $this->usersView->getUsers($where_params);
				$count = count($users);
				if($count >= 1) {
					if ($this->duplicate_data == _OFF) {
						$errlist[] = sprintf(USER_IMPORT_MES_ERROR_DUPLICATE, USER_ITEM_LOGIN);
						$user_id = $errStr;
					} else {
						if ($user_id != $errStr) {
							$user_id = $users[0]['user_id'];
							$attributes['user_id'] = $user_id;
						}
						$edit_flag = _ON;
					}
					$current_user = $users[0];
				} else {
					$current_user = null;
				}

				$this->loginid_list[] = $login_id;
			} else if($items['tag_name'] == "password" && $content != "") {
				$new_password = $content;
				// 文字チェック
				$pass_len = strlen($new_password);
				if($pass_len < USER_PASSWORD_MINSIZE || $pass_len > USER_PASSWORD_MAXSIZE) {
					$errlist[] = sprintf(_MAXRANGE_ERROR, USER_ITEM_PASSWORD, USER_PASSWORD_MINSIZE, USER_PASSWORD_MAXSIZE);
					$user_id = $errStr;
				}
				// 半角英数または、記号
				if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $new_password)) {
					$errlist[] = sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_PASSWORD);
					$user_id = $errStr;
				}
			} else if($items['tag_name'] == "handle") {
				// 重複チェック
				$handle = $content;
				$where_params = array("handle" => $handle);
				$users =& $this->usersView->getUsers($where_params);
				$count = count($users);
				if($count >= 1 && $users[0]['user_id'] != $attributes['user_id']) {
					$errlist[] = sprintf(USER_IMPORT_MES_ERROR_DUPLICATE, USER_ITEM_HANDLE);
					$user_id = $errStr;
				}

				$this->handle_list[] = $handle;
			} else if($items['tag_name'] == "active_flag_lang") {
				//システム管理者の場合、使用不可にはできない
				if($attributes['user_id'] == $_system_user_id && $content == _OFF) {
					$errlist[] = sprintf(USER_IMPORT_SYSTEM_ADMIN_ERR, $items['item_name']);
					$user_id = $errStr;
				}
			} else if($items['tag_name'] == "role_authority_name") {
				//システム管理者の場合、変更不可
				if($attributes['user_id'] == $_system_user_id && $content != _SYSTEM_ROLE_AUTH_ID) {
					$errlist[] = sprintf(USER_IMPORT_SYSTEM_ADMIN_ERR, $items['item_name']);
					$user_id = $errStr;
				}

				$authority = $this->authoritiesView->getAuthorityByID($content);
				if ((($authority !== false) && ($authority != null)) && ($authority["user_authority_id"] >= $_user_auth_id)) {
					$errlist[] = sprintf(USER_IMPORT_SYSTEM_AUTH_ERR, $items['item_name']);
					$user_id = $errStr;
				} else if ($content == "") {
					$errlist[] = sprintf(_REQUIRED, $items['item_name']);
					$user_id = $errStr;
				}
			}
			if($items['type'] == "email" || $items['type'] == "mobile_email") {
				$email = $content;

				// 文字チェック
				if ( $email != "" && !strpos($email, "@") ) {
					$errlist[] = sprintf(_FORMAT_WRONG_ERROR, $items['item_name']);
					$user_id = $errStr;
				}
				// 重複チェック
				$userIdByMail = $this->usersView->getUserIdByMail($email);
				if (!empty($userIdByMail)
						&& $userIdByMail != $attributes['user_id']) {
					$errlist[] = sprintf(USER_IMPORT_MES_ERROR_DUPLICATE, $items['item_name']);
					$user_id = $errStr;
				}
				// メール受信可否
				if((isset($attributes['items_reception']) && is_array($attributes['items_reception'])) && isset($attributes['items_reception'][$items['item_id']])) {
					if($items['allow_email_reception_flag'] == _OFF ||
						!($attributes['items_reception'][$items['item_id']] == _ON ||
							$attributes['items_reception'][$items['item_id']] == _OFF)) {
						$errlist[] = sprintf(USER_IMPORT_INVALID_INPUT, $items['item_name'], USER_IMPORT_EMAIL_USE_SET);
						$user_id = $errStr;
					}
				}

				$this->email_list_all[] = $email;
			}

			// 公開設定
			if((isset($attributes['items_public']) && is_array($attributes['items_public'])) && isset($attributes['items_public'][$items['item_id']])) {
				if($items['allow_public_flag'] == _OFF ||
					!($attributes['items_public'][$items['item_id']] == _ON ||
						$attributes['items_public'][$items['item_id']] == _OFF)) {
						$errlist[] = sprintf(USER_IMPORT_INVALID_INPUT, $items['item_name'], USER_IMPORT_PUBLIC_SET);
						$user_id = $errStr;
				}
			}
		}
		$res = array("errlist" => $errlist, "userid" => $user_id);
		return $res;
	}

	/**
	 * インポートファイル内の重複チェック
	 * @param row_data_user, row_data_headers
	 * @return res
	 * @access	private
	 */
	function dataCheck_infile($row_data_user, $row_data_headers)
	{
		// ユーザID，ハンドルをチェック
		$check_items = array(USER_ITEM_LOGIN, USER_ITEM_HANDLE);
		$datas = array($this->loginid_list, $this->handle_list);
		$res = null;
		$errlist = null;

		// インポートファイル内の「ユーザID，ハンドル」の列番号を検索
		if (!isset($this->check_items_num)) {
			$check_items_num = null;
			foreach ($check_items as $check_item) {
				$found_item = _OFF;
				for ($idx=0; $idx<count($row_data_headers); $idx++) {
					$item_pos = $row_data_headers[$idx];
					if (!strcmp($item_pos, $check_item)) {
						$check_items_num[] = $idx;
						$found_item = _ON;
						break;
					}
				}
				if ($found_item == _OFF) $check_items_num[] = "-1";
			}
			$this->check_items_num = $check_items_num;
		} else {
			$check_items_num = $this->check_items_num;
		}

		// 重複チェック
		for ($idx1=0; $idx1<count($check_items); $idx1++) {
			$check_item_num = $check_items_num[$idx1];
			$data = $datas[$idx1];

			if (isset($data) && is_array($data)) {
				for ($idx2=0; $idx2<(count($data)-1); $idx2++) {
					$data_item = $data[$idx2];
					if (($data_item != "") && ($row_data_user[$check_item_num] != "")) {
						if (!strcmp($data_item, $row_data_user[$check_item_num])) {
							// 重複
							$errlist[] = sprintf(USER_IMPORT_DUPLICATE, $check_items[$idx1]);
							break;
						}
					}
				}
			}
		}

		$res = $errlist;
		return $res;
	}

	/**
	 * インポートファイル内のメールの重複チェック
	 * @param row_data_user, row_data_headers
	 * @return res
	 * @access	private
	 */
	function dataCheck_infile_mail()
	{
		$errlist = null;

		//現在行で設定したメールアドレスを取得
		$current_set_mail = array_slice($this->email_list_all,count($this->email_list_all)-$this->email_column_cnt,$this->email_column_cnt);

		//現在行より前で設定しているメールアドレスと重複していないことをチェック
		for ( $i = 0 ; $i < $this->email_column_cnt ; $i++ ) {
			if (!empty($current_set_mail[$i])) {
				for ( $j = 0 ; $j < count($this->email_list_all)-$this->email_column_cnt ; $j++ ){
					if ($current_set_mail[$i] == $this->email_list_all[$j] ) {
						$errlist[] = sprintf(USER_IMPORT_DUPLICATE."(".$current_set_mail[$i].")",$this->email_column_name[$i]);
						break;
					}
				}
			} 
		}
		return $errlist;
	}

	function _delImportFile($file_path) {
		if(file_exists($file_path)) {
			@chmod($file_path, 0777);
			unlink($file_path);
		}
	}
}
?>