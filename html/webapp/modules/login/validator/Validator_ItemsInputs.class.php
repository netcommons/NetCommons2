<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 自動登録が使われているかどうかのチェック
 * 項目テーブルの入力チェック(login_id, password,handle, email)
 * 必須チェック
 * 利用規約チェック
 *  リクエストパラメータ
 *  $items,$items_public,$items_reception,$items_password_confirm,$autoregist_disclaimer_ok
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Login_Validator_ItemsInputs extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値(user_id, items, items_public, items_reception)
	 *
	 * @param   string  $errStr エラー文字列(未使用：エラーメッセージ固定)
	 * @param   array   $params オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		// container取得
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$usersView =& $container->getComponent("usersView");
		$configView =& $container->getComponent("configView");
		$fileUpload =& $container->getComponent("FileUpload");
		$commonMain =& $container->getComponent("commonMain");
		$authoritiesView =& $container->getComponent("authoritiesView");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$db =& $container->getComponent("DbObject");

		$config = $configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID);

		if($config['autoregist_use']['conf_value'] != _ON) {
			return $errStr;
		}

		$where_params = array(
							"user_authority_id" => _AUTH_ADMIN		// 管理者固定
						);
		$show_items =& $usersView->getItems($where_params, null, null, null, array($this, "_getItemsFetchcallback"));
		if($show_items === false) {
			return $errStr;
		}
		$configs = explode("|", $config['autoregist_use_items']['conf_value']);
		foreach($configs as $autoregist_use_item) {
			$buf_arr = explode(":", $autoregist_use_item);
			if(isset($buf_arr[0]) && $buf_arr[0] != "") {
				$autoregist_use_items[$buf_arr[0]] = $buf_arr[0];
				$autoregist_use_items_req[$buf_arr[0]] = $buf_arr[1];
			}
		}
		$files = $fileUpload->getOriginalName();
		$files_key = array_keys($files);

		foreach($show_items as $show_items_key => $items) {

			if(!isset($autoregist_use_items[$items['item_id']])) {
				// 自動ログインの項目ではない
				if(isset($attributes['items']) && isset($attributes['items'][$items['item_id']])) {
					return $errStr;
				}
				continue;
			}
			$err_prefix = $items['item_id'].":";
			if($items['define_flag'] == _ON && defined($items['item_name'])) {
				$items['item_name'] = constant($items['item_name']);
				$show_items[$show_items_key]['item_name'] = $items['item_name'];
			}
			if($items['type'] == "file") {
				// File
				// 必須入力チェック
				if($items['require_flag'] == _ON || (isset($autoregist_use_items_req[$items['item_id']]) && $autoregist_use_items_req[$items['item_id']] == _ON)) {

					$error_flag = true;
					foreach($files_key as $file_key) {
						if($items['item_id'] == $file_key && ($files[$file_key] != "" && $files[$file_key] != null)) {
							$error_flag = false;
							break;
						}
					}
					if($error_flag) {
						//ファイルアップロード未対応携帯なのに、このファイルは必須扱いになっている...
						//
						//つまり、この携帯からは登録できないので、PCから登録していただくか、
						//管理者にお願いして、必須から任意にかえていただくことを薦めます。
						//
						if (empty($files)) {
							return $err_prefix.LOGIN_ERR_FILE_UPLOAD_NOABILITY;
						} else {
							return $err_prefix.sprintf(_REQUIRED, $items['item_name']);
						}
					}
				}
				continue;
			}

			if(isset($attributes['items']) && isset($attributes['items'][$items['item_id']])) {
				$content = $attributes['items'][$items['item_id']];
			} else {
				$content = "";
			}

			// 必須入力チェック
			if($items['require_flag'] == _ON || (isset($autoregist_use_items_req[$items['item_id']]) && $autoregist_use_items_req[$items['item_id']] == _ON)) {
				// 必須項目
				if($content == "") {
					return $err_prefix.sprintf(_REQUIRED, $items['item_name']);
				}
			}

			if($items['tag_name'] == "login_id") {
				// 入力文字チェック
				$login_id = $content;
				$login_len = strlen($content);
				if($login_len < USER_LOGIN_ID_MINSIZE || $login_len > USER_LOGIN_ID_MAXSIZE) {
					return $err_prefix.sprintf(_MAXRANGE_ERROR, USER_ITEM_LOGIN, USER_LOGIN_ID_MINSIZE, USER_LOGIN_ID_MAXSIZE);
				}

				// 半角英数または、記号
				if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $login_id)) {
					return $err_prefix.sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_LOGIN);
				}

				// 重複チェック
				$where_params = array("login_id" => $login_id);
				$users =& $usersView->getUsers($where_params);
				$count = count($users);
				if($count >= 1) {
					return $err_prefix.sprintf(LOGIN_MES_ERROR_DUPLICATE, USER_ITEM_LOGIN, USER_ITEM_LOGIN);
				}
			} else if($items['tag_name'] == "password") {
				$new_password = $content;
				// 入力文字チェック
				$pass_len = strlen($new_password);
				if($pass_len < USER_PASSWORD_MINSIZE || $pass_len > USER_PASSWORD_MAXSIZE) {
					return $err_prefix.sprintf(_MAXRANGE_ERROR, USER_ITEM_PASSWORD, USER_PASSWORD_MINSIZE, USER_PASSWORD_MAXSIZE);
				}

				if(!isset($attributes['items_password_confirm'][$items['item_id']]) || $new_password != $attributes['items_password_confirm'][$items['item_id']]) {
					return $err_prefix.LOGIN_ERR_PASS_DISACCORD;
				}
				// 半角英数または、記号
				if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $new_password)) {
					return $err_prefix.sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_PASSWORD);
				}
			} else if($items['tag_name'] == "handle") {
				// 重複チェック
				$handle = $content;
				$where_params = array("handle" => $handle);
				$users =& $usersView->getUsers($where_params);
				$count = count($users);
				if($count >= 1) {
					return $err_prefix.sprintf(LOGIN_MES_ERROR_DUPLICATE, USER_ITEM_HANDLE, USER_ITEM_HANDLE);
				}
			}
			if($items['type'] == "email" || $items['type'] == "mobile_email") {
				$email = $content;

				// 入力文字チェック
				if ( $email != "" && !strpos($email, "@") ) {
					return $err_prefix.sprintf(_FORMAT_WRONG_ERROR, $items['item_name']);
				}

				// 確認用アドレスチェック
				if ($email != $attributes['items_mail_confirm'][$items['item_id']]) {
					$errorMessage = $err_prefix;

					if ($items['type'] == "email") {
						$errorMessage .= LOGIN_ERR_EMAIL_DISACCORD;
					} else if ($items['type'] == "mobile_email") {
						$errorMessage .= LOGIN_ERR_MOBILE_EMAIL_DISACCORD;
					}

					return $errorMessage;
				}

				// 重複チェック
				$userIdByMail = $usersView->getUserIdByMail($email);
				if (!empty($userIdByMail)) {
					$errorMessage = $err_prefix
									. sprintf(LOGIN_MES_ERROR_DUPLICATE, $items['item_name'], $items['item_name']);
					return $errorMessage;
				}

				// メール受信可否
				if(isset($attributes['items_reception']) && isset($attributes['items_reception'][$items['item_id']])) {
					if($items['allow_email_reception_flag'] == _OFF ||
						!($attributes['items_reception'][$items['item_id']] == _ON ||
							$attributes['items_reception'][$items['item_id']] == _OFF)) {
						return  $err_prefix._INVALID_INPUT;
					}
				}

			}

			// 公開設定
			if(isset($attributes['items_public']) && isset($attributes['items_public'][$items['item_id']])) {
				if($items['allow_public_flag'] == _OFF ||
					!($attributes['items_public'][$items['item_id']] == _ON ||
						$attributes['items_public'][$items['item_id']] == _OFF)) {
					return  $err_prefix._INVALID_INPUT;
				}
			}
		}

		// 利用許諾
		if(!isset($attributes['autoregist_disclaimer_ok'])) {
			return LOGIN_ERR_MES_DISCLAIMER;
		}

		// 入力キー
		if ($config['autoregist_use_input_key']['conf_value'] == _ON &&
			$config['autoregist_input_key']['conf_value'] != $attributes['autoregist_input_key']) {

			return LOGIN_ERR_MES_INPUT_KEY;
		}

		// File
		$garbage_flag = _OFF;
		$filelist = $uploadsAction->uploads($garbage_flag, '', array(_UPLOAD_THUMBNAIL_MAX_WIDTH_IMAGE, _UPLOAD_THUMBNAIL_MAX_HEIGHT_IMAGE));

		foreach($filelist as $key => $file) {
			if(isset($file['error_mes']) && $file['error_mes'] != "" && $file['error_mes'] != _FILE_UPLOAD_ERR_UPLOAD_NOFILE) {
				$err_prefix = $key.":";
				return $err_prefix.$file['error_mes'];
			}
		}

		// actionChain取得
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		if(isset($params[0])) {
			BeanUtils::setAttributes($action, array($params[0]=>$show_items));
		} else {
			BeanUtils::setAttributes($action, array("show_items"=>$show_items));
		}
		BeanUtils::setAttributes($action, array("autoregist_approver"=>$config['autoregist_approver']['conf_value']));
		BeanUtils::setAttributes($action, array("autoregist_author"=>$config['autoregist_author']['conf_value']));
		BeanUtils::setAttributes($action, array("autoregist_defroom"=>$config['autoregist_defroom']['conf_value']));
		BeanUtils::setAttributes($action, array("config"=>$config));

		BeanUtils::setAttributes($action, array("filelist"=>$filelist));

		return;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}
}
?>
