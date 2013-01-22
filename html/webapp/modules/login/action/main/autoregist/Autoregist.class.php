<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員受付登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Login_Action_Main_Autoregist extends Action
{
	// リクエストパラメータを受け取るため
	var $items = null;
	var $items_public = null;
	var $items_reception = null;
	//var $files = null;

	// バリデートによりセット
	var $show_items = null;
	var $autoregist_approver = null;
	var $autoregist_author = null;
	var $autoregist_defroom = null;
	var $filelist = null;
	var $config = null;

	// 使用コンポーネントを受け取るため
	var $pagesView = null;
	var $usersView = null;
	var $pagesAction = null;
	var $usersAction = null;
	var $configView = null;
	var $db = null;
	var $uploadsAction = null;
	var $actionChain = null;
	var $monthlynumberAction = null;
	var $fileUpload = null;
	var $timezoneMain = null;
	var $mailMain = null;
	var $session = null;
	var $authoritiesView = null;
	var $blocksAction = null;

	// 値をセットするため
	var $error_flag = true;
	var $post_mail_body = "";
	//var $_attachment_list = null;
	var $use_ssl = 0;

	/**
	 * 会員受付登録
	 *
	 * @access  public
	 */
	function execute()
	{
		// ----------------------------------------------------------------------
		// --- 基本項目(usersテーブル)                                        ---
		// ----------------------------------------------------------------------
		if($this->autoregist_approver == _AUTOREGIST_SELF) {
			$active_flag = _USER_ACTIVE_FLAG_MAILED;
			$activate_key = $this->_getActivateKey(LOGIN_ACTIVATE_KYE_LEN);

			$mail_autoregist_subject = $this->config['mail_approval_subject']['conf_value'];
			$mail_autoregist_body = $this->config['mail_approval_body']['conf_value']."<br />";
		} else if($this->autoregist_approver == _AUTOREGIST_AUTO) {
			$active_flag = _USER_ACTIVE_FLAG_ON;
			$activate_key = "";

			$mail_autoregist_subject = "";
			$mail_autoregist_body = "";
		} else {
			$active_flag = _USER_ACTIVE_FLAG_PENDING;
			$activate_key = $this->_getActivateKey(LOGIN_ACTIVATE_KYE_LEN);

			$mail_autoregist_subject = $this->config['mail_add_announce_subject']['conf_value'];
			$mail_autoregist_body = $this->config['mail_add_announce_body']['conf_value']."<br />";
		}

		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		$this->use_ssl = $config['use_ssl']['conf_value'];

		$time = timezone_date();
		$user = array(
					"role_authority_id" => intval($this->autoregist_author),
					"activate_key" => $activate_key,
					"active_flag" => $active_flag,
					"password_regist_time" => $time,
					"last_login_time" => "",
					"previous_login_time" => "",
					"lang_dirname" => $config['language']['conf_value'],
					"timezone_offset" => $config['default_TZ']['conf_value']
				);

		$users_items_link = array();
		$users_items_link_flag_arr = array();
		$files_key = array_keys($this->fileUpload->getOriginalName());

		foreach($this->show_items as $item_id => $item) {
			if(!isset($this->items[$item_id]) && !(isset($this->filelist[$item_id]['upload_id']))) {
				continue;
			}

			$users_items_link[$item_id] = array(
				"user_id" => 0,
				"item_id" => $item_id,
				"public_flag" => _OFF,
				"email_reception_flag" => _OFF
			);

			if( $item['tag_name'] != ""
				&& $this->usersView->isUsersTableField( $item['tag_name'] ) != false ){
				$tag_name = $item['tag_name'];
				switch ($item['tag_name']) {
					//case "role_authority_name":
					//	$tag_name = "role_authority_id";
					//	break;
					//case "active_flag_lang":
					//	$tag_name = "active_flag";
					//	break;
					case "handle":
						$handle = $this->items[$item_id];
						break;
					case "lang_dirname_lang":
						$tag_name = "lang_dirname";
						break;
					case "timezone_offset_lang":
						$tag_name = "timezone_offset";
						if(defined($this->items[$item_id])) {
							$this->items[$item_id] = $this->timezoneMain->getFloatTimeZone(constant($this->items[$item_id]));
						}
						break;
				}
				$users_items_link_flag_arr[$item_id] = false;

				if($item['tag_name'] == "password") {
					$this->items[$item_id] = md5($this->items[$item_id]);
				} else {
					$this->post_mail_body .= $item['item_name']._SEPARATOR2.$this->items[$item_id]."\n";
				}
				$user[$tag_name] = $this->items[$item_id];
			} else {
				// users_items_linkデータ
				if($item['type'] == "radio" || $item['type'] == "checkbox" ||
					$item['type'] == "select") {
					if(is_array($this->items[$item_id])) {
						$users_items_link[$item_id]['content'] = implode("|", $this->items[$item_id]) . '|';
						$post_mail_body = "";
						foreach ($this->items[$item_id] as $i=>$choice) {
							if (defined($choice)) {
								$post_mail_body .= constant($choice) . ",";
							} else {
								$post_mail_body .= $choice . ",";
							}
						}
						$this->post_mail_body .= $item['item_name']._SEPARATOR2.mb_substr($post_mail_body, 0, -1)."\n";
					} else if($this->items[$item_id] == "") {
						$users_items_link[$item_id]['content'] = "";
						$this->post_mail_body .= $item['item_name']._SEPARATOR2.$this->items[$item_id]."\n";
					} elseif (defined($this->items[$item_id])) {
						$users_items_link[$item_id]['content'] = $this->items[$item_id] . "|";
						$this->post_mail_body .= $item['item_name']._SEPARATOR2.constant($this->items[$item_id])."\n";
					} else {
						$users_items_link[$item_id]['content'] = $this->items[$item_id] . "|";
						$this->post_mail_body .= $item['item_name']._SEPARATOR2.$this->items[$item_id]."\n";
					}
				} else {
					if(isset($this->filelist[$item_id]['upload_id'])) {
						$users_items_link[$item_id]['content'] = "?action=common_download_user&upload_id=".$this->filelist[$item_id]['upload_id'];
					} else {
						$users_items_link[$item_id]['content'] = $this->items[$item_id];
						$this->post_mail_body .= $item['item_name']._SEPARATOR2.$this->items[$item_id]."\n";
					}
				}
				$users_items_link_flag_arr[$item_id] = true;
			}

			if($item['allow_public_flag'] ==_ON && isset($this->items_public[$item_id]) &&
				$this->items_public[$item_id] == _ON) {
				$users_items_link[$item_id]['public_flag'] = _ON;
			}
			if($item['allow_email_reception_flag'] ==_ON && isset($this->items_reception[$item_id]) &&
				 $this->items_reception[$item_id] == _ON) {
				$users_items_link[$item_id]['email_reception_flag'] = _ON;
			}
		}
		$new_user_id = $this->usersAction->insUser($user);
		if($new_user_id === false) return 'error';

		// ----------------------------------------------------------------------
		// --- 詳細項目(users_items_linkデータ登録)                           ---
		// ----------------------------------------------------------------------
		//$email = "";
		$email_arr = array();
		foreach($users_items_link as $item_id => $users_item_link) {
			//$users_item_link[]
			// users_items_linkが変更ないか、users_items_linkがなく初期値であれば
			if($users_items_link_flag_arr[$item_id] == true) {
				if(($users_items_link[$item_id]['content'] == '' &&
					   $users_items_link[$item_id]['public_flag'] == _OFF &&
					   $users_items_link[$item_id]['email_reception_flag'] == _OFF
					)) {
						//初期値のまま
						continue;
				}

				// 新規追加
				$users_item_link['user_id'] = $new_user_id;

				$content = $users_item_link['content'];

				$result = $this->usersAction->insUserItemLink($users_item_link);
				if($result === false) return 'error';

				if($this->show_items[$item_id]['type'] == "file") {
					// アバターの画像のunique_id セット
					$upload_id = $this->filelist[$item_id]['upload_id'];
					$upload_params = array(
						"unique_id" => $new_user_id
					);
					$upload_where_params = array(
							"upload_id" => $upload_id,
					);
					$upload_result = $this->uploadsAction->updUploads($upload_params, $upload_where_params);
					if ($upload_result === false) return 'error';
				//} else if($this->show_items[$item_id]['tag_name'] == "email") {
				} else if($this->show_items[$item_id]['type'] == "email" || $this->show_items[$item_id]['type'] == "mobile_email") {
					$email_arr[] = $content;
				}
			}
		}
		$authoritiy = $this->authoritiesView->getAuthorityById(intval($this->autoregist_author));
		if($authoritiy !== false && $authoritiy['myroom_use_flag'] == _ON) {
			$myroom_display_flag = _ON;
		} else {
			$myroom_display_flag = _PAGES_DISPLAY_FLAG_DISABLED;
		}

		if($this->autoregist_defroom == _OFF) {
			// ----------------------------------------------------------------------
			// ---参加ルーム(pages_users_link登録)                                ---
			// ----------------------------------------------------------------------
			// 自動登録時にデフォルトのルームに参加するかどうか
			// 参加させない場合、不参加で登録する

			$where_params = array(
				"default_entry_flag" => _ON,
				"space_type" => _SPACE_TYPE_GROUP,
				"private_flag" => _OFF,
				"page_id = room_id" => null,
				"page_id !="._SELF_TOPGROUP_ID => null
			);
			$pages =& $this->db->selectExecute("pages", $where_params);
			if ($pages === false) return 'error';
			foreach($pages as $page) {
				// 不参加として登録
				$params = array(
					"room_id" => $page['page_id'],
					"user_id" => $new_user_id,
					"role_authority_id" => _ROLE_AUTH_OTHER,
					"createroom_flag" => _OFF
				);
				$result = $this->pagesAction->insPageUsersLink($params);
				if ($result === false) return 'error';
			}
		} else {
			// ----------------------------------------------------------------------
			// ---参加ルーム(pages_users_link登録)                                ---
			// ----------------------------------------------------------------------
			// 自動登録時にデフォルトのルームに参加し、デフォルトの参加が一般で、登録会員のベース権限がゲスト権限の場合、
			// ゲストとして参加させる必要がある。
			if($authoritiy !== false && $authoritiy['user_authority_id'] == _AUTH_GUEST) {
				// ゲストとして登録
				$where_params = null;
				//$this->session->getParameter("_default_entry_auth_private")
				if($this->session->getParameter("_default_entry_auth_public") == _AUTH_GENERAL &&
					$this->session->getParameter("_default_entry_auth_group") == _AUTH_GENERAL) {
					$where_params = array(
						"page_id = room_id" => null,
						"default_entry_flag" => _ON,
						"private_flag" => _OFF,
						"space_type IN ("._SPACE_TYPE_PUBLIC.","._SPACE_TYPE_GROUP.")" => null
					);
				} else if($this->session->getParameter("_default_entry_auth_public") == _AUTH_GENERAL){
					$where_params = array(
						"page_id = room_id" => null,
						"default_entry_flag" => _ON,
						"private_flag" => _OFF,
						"space_type" => _SPACE_TYPE_PUBLIC
					);
				} else if($this->session->getParameter("_default_entry_auth_group") == _AUTH_GENERAL){
					$where_params = array(
						"page_id = room_id" => null,
						"default_entry_flag" => _ON,
						"private_flag" => _OFF,
						"space_type" => _SPACE_TYPE_GROUP
					);
				}

				if($where_params != null) {
					$pages =& $this->db->selectExecute("pages", $where_params);
					if ($pages === false) {
						return 'error';
					}
					foreach($pages as $page) {
						$params = array(
							"room_id" => $page['page_id'],
							"user_id" => $new_user_id,
							"role_authority_id" => _ROLE_AUTH_GUEST,
							"createroom_flag" => _OFF
						);
						$result = $this->pagesAction->insPageUsersLink($params);
						if ($result === false) return 'error';
					}
				}
			}
		}

		// ----------------------------------------------------------------------
		// --- プライベートスペース作成                                       ---
		// ----------------------------------------------------------------------
		//権限テーブルのmyroom_use_flagにかかわらずプライベートスペース作成

		//
		// ページテーブル追加
		//
		$private_where_params = array(
									"space_type" => _SPACE_TYPE_GROUP,
									"thread_num" => 0,
									"private_flag" => _ON,
									"display_sequence!=0" => null
								);
		$buf_page_private =& $this->pagesView->getPages($private_where_params, null, 1);
		if ($buf_page_private === false) return 'error';
		if(!isset($buf_page_private[0])) {
			// エラー
			$this->db->addError(get_class($this), sprintf(_INVALID_SELECTDB, "pages"));
			return 'error';
		}
		$display_sequence = $buf_page_private[0]['display_sequence'];

		// プライベートスペース名称取得
		if(!isset($handle) || $handle == "") $handle = _PRIVATE_SPACE_NAME;


		$private_space_name = str_replace("{X-HANDLE}", $handle, $config['add_private_space_name']['conf_value']);
		//if($config['open_private_space']['conf_value'] == _ON) {
		//	// プライベートスペースが公開していれば、default_entry_flagをonにする
		//	$default_entry_flag = _ON;
		//} else {
			$default_entry_flag = _OFF;
		//}
		$permalink_handle = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $handle);
		if(_PERMALINK_PRIVATE_PREFIX_NAME != '') {
			$permalink = _PERMALINK_PRIVATE_PREFIX_NAME.'/'.$permalink_handle;
		} else {
			$permalink = $permalink_handle;
		}
		$permalink = $this->pagesAction->getRenamePermaLink($permalink);
		$params = array(
			"site_id" => $this->session->getParameter("_site_id"),
			"root_id" => 0,
			"parent_id" => 0,
			"thread_num" => 0,
			"display_sequence" => $display_sequence,
			"action_name" => DEFAULT_ACTION,
			"parameters" => "",
			"page_name" => $private_space_name,
			"permalink" => $permalink,
			"show_count" => 0,
			"private_flag" => _ON,
			"default_entry_flag" => $default_entry_flag,
			"space_type" => _SPACE_TYPE_GROUP,
			"node_flag" => _ON,
			"shortcut_flag" => _OFF,
			"copyprotect_flag" => _OFF,
			"display_scope" => _DISPLAY_SCOPE_NONE,
			"display_position" => _DISPLAY_POSITION_CENTER,
			"display_flag" => $myroom_display_flag,
			"insert_time" =>$time,
			"insert_site_id" => $this->session->getParameter("_site_id"),
			"insert_user_id" => $new_user_id,
			"insert_user_name" => $user["handle"],
			"update_time" =>$time,
			"update_site_id" => $this->session->getParameter("_site_id"),
			"update_user_id" => $new_user_id,
			"update_user_name" => $user["handle"]
		);
		$private_page_id = $this->pagesAction->insPage($params, true, false);
		if ($private_page_id === false) return 'error';
		//
		// ページユーザリンクテーブル追加
		//
		$params = array(
			"room_id" => $private_page_id,
			"user_id" => $new_user_id,
			"role_authority_id" => _ROLE_AUTH_CHIEF,
			"createroom_flag" => _OFF
		);
		$result = $this->pagesAction->insPageUsersLink($params);
		if ($result === false) return 'error';

		// ----------------------------------------------------------------------
		// --- 月別アクセス回数初期値登録                                     ---
		// ----------------------------------------------------------------------
		$name = "_hit_number";
		$time = timezone_date();
		$year = intval(substr($time, 0, 4));
		$month = intval(substr($time, 4, 2));
		$params = array(
					"user_id" =>$new_user_id,
					"room_id" => $private_page_id,
					"module_id" => 0,
					"name" => $name,
					"year" => $year,
					"month" => $month,
					"number" => 0
				);
		$result = $this->monthlynumberAction->insMonthlynumber($params);
		if($result === false)  {
			return 'error';
		}

		// ----------------------------------------------------------------------
		// --- 初期ページ追加                                                 ---
		// ----------------------------------------------------------------------
		$result = $this->blocksAction->defaultPrivateRoomInsert($private_page_id, $new_user_id, $handle);
		if($result === false)  {
			return 'error';
		}

		// ----------------------------------------------------------------------
		// --- マイポータル作成                                               ---
		// ----------------------------------------------------------------------
		if($config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP ||
			$config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC) {
			//
			// display_sequence取得
			//
			$private_where_params = array(
									"space_type" => _SPACE_TYPE_GROUP,
									"thread_num" => 0,
									"private_flag" => _ON,
									"display_sequence!=0" => null,
									"default_entry_flag" => _ON
								);
			$buf_page_private =& $this->pagesView->getPages($private_where_params, null, 1);
			if ($buf_page_private === false) return 'error';
			if(!isset($buf_page_private[0])) {
				// エラー
				$this->db->addError(get_class($this), sprintf(_INVALID_SELECTDB, "pages"));
				return 'error';
			}
			if(isset($authoritiy['myportal_use_flag']) && $authoritiy['myportal_use_flag'] == _ON) {
				$display_flag = _ON;
			} else {
				$display_flag = _PAGES_DISPLAY_FLAG_DISABLED;
			}
			$display_sequence = $buf_page_private[0]['display_sequence'];

			//
			// ページテーブル追加
			//
			$pages_params['page_name'] = $user["handle"];
			if(_PERMALINK_MYPORTAL_PREFIX_NAME != '') {
				$permalink = _PERMALINK_MYPORTAL_PREFIX_NAME.'/'.$permalink_handle;
			} else {
				$permalink = $permalink_handle;
			}
			$permalink = $this->pagesAction->getRenamePermaLink($permalink);
			$pages_params['permalink'] = $permalink;
			$pages_params['default_entry_flag'] = _ON;
			$pages_params['display_flag'] = $display_flag;
			$pages_params['display_sequence'] = $display_sequence;

			$private_page_id = $this->pagesAction->insPage($pages_params, true, false);
			if ($private_page_id === false) return 'error';

			//
			// ページユーザリンクテーブル追加
			//
			$pages_users_link_params['room_id'] = $private_page_id;
			$result = $this->pagesAction->insPageUsersLink($pages_users_link_params);
			if ($result === false) return 'error';

			//
			// 月別アクセス回数初期値登録
			//
			$monthlynumber_params['room_id'] = $private_page_id;
			$result = $this->monthlynumberAction->insMonthlynumber($monthlynumber_params);
			if($result === false)  {
				return 'error';
			}
		}

		// ----------------------------------------------------------------------
		// --- メール送信処理                                                 ---
		// ----------------------------------------------------------------------
		if($mail_autoregist_subject == "" || count($email_arr) == 0) {
			$this->error_flag = false;
			return 'success';
		}

		if($this->autoregist_approver == _AUTOREGIST_SELF) {
			// ユーザ自身の確認が必要
			$mail_autoregist_body .= "<br />".BASE_URL. INDEX_FILE_NAME.
						"?action=login_action_main_approver" .
						"&user_id=" . $new_user_id . "&activate_key=". $activate_key."&_header="._OFF. "<br />";

			foreach($email_arr as $email) {
				$user['email'] = $email;
				$user['type'] = "text";	// Text固定(html or text)
				$this->mailMain->addToUser($user);
			}
		} else {
			// 管理者の承認が必要
			$mail_autoregist_body .= htmlspecialchars($this->post_mail_body)
									. '<br />'
									. BASE_URL. INDEX_FILE_NAME
										. '?action=login_action_main_approver'
										. '&user_id=' . $new_user_id
										. '&activate_key=' . $activate_key
										. '&_header=' . _OFF
									. '<br />';
			// 管理者取得
			$users = $this->usersView->getSendMailUsers(null, _AUTH_ADMIN, "text");
			$this->mailMain->setToUsers($users);
		}

		$this->mailMain->setSubject($mail_autoregist_subject);
		$this->mailMain->setBody($mail_autoregist_body);

		$this->mailMain->send();

		$this->error_flag = false;

		return 'success';
	}


	/**
	 * アクティベーションキーを取得
	 *
	 * @param string $length 生成するアクティベーションキーの桁数
	 * @return string	アクティベーションキー
	 * @access	public
	 */
	function _getActivateKey($length) {
		$duplicationFlag = true;

		$sql = "SELECT activate_key FROM {users} WHERE activate_key = ?";

		while ($duplicationFlag) {
			$activateKey = md5(uniqid(mt_rand(), 1));
			$activateKey = substr($activateKey, 0, $length);
			$params = array(
				$activateKey
			);

			$recordSet = $this->db->execute($sql, $params);
			if ($recordSet === false) {
				$this->db->addError();
				return false;
			}

			if (empty($recordSet)) {
				$duplicationFlag = false;
			}
		}

		return $activateKey;
	}
}
?>
