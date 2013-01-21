<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報-詳細の登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Action_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $item_id = null;
	var $user_id = null;
	
	var $content = null;
	var $confirm_content = null;
	var $current_content = null;
	var $email_reception_flag = null;
	var $public_flag = null;
	
	// バリデートによりセット
	var $items = null;
	var $user = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $usersAction = null;
	var $usersView = null;
	var $timezoneMain = null;
	var $languagesView = null;
	var $pagesAction = null;
	var $pagesView = null;
	var $authoritiesView = null;
	var $configView = null;
	
	// 値をセットするため

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if(!isset($this->user_id) || $this->user_id == "0") {
			$user_id = $this->session->getParameter("_user_id");
		} else {
			$user_id = $this->user_id;
		}
		$is_users_tbl_fld = $this->usersView->isUsersTableField( $this->items['tag_name'] );
		if($this->items['tag_name'] != "" && $is_users_tbl_fld) {
			//会員テーブル更新
			if($this->items['tag_name'] =="password") {
				//パスワード
				$this->content = md5($this->content);
			}
			switch ($this->items['tag_name']) {
				case "timezone_offset_lang":
					// タイムゾーン
					$tag_name = "timezone_offset";
					$this->content = substr($this->content, 0, strlen($this->content) - 1);	//語尾の|除去
					$this->content = $this->timezoneMain->getFloatTimeZone($this->content);
					$params = array($tag_name => $this->content);
					$this->session->setParameter("_timezone_offset",$this->content);
					break;
				case "role_authority_name":
					$tag_name = "role_authority_id";
					$this->content = substr($this->content, 0, strlen($this->content) - 1);	//語尾の|除去
					$params = array($tag_name => $this->content);
					break;
				case "lang_dirname_lang":
					$tag_name = "lang_dirname";
					$content_lang = substr($this->content, 0, strlen($this->content) - 1);	//語尾の|除去
					//自動を選択した場合
					if($content_lang == LANG_NAME_AUTO) {
						$params = array($tag_name => '');
						$this->content = '';
						break;
					}
					$languages =& $this->languagesView->getLanguagesList();
					$params = array($tag_name => "japanese");						//初期値-固定値
					$this->content = "japanese";
					foreach($languages as $key => $language) {
						if($content_lang == $language) {
							$params = array($tag_name => $key);
							$this->content = $key;
							break;	
						}
					}
					break;
				case "active_flag_lang":
					$tag_name = "active_flag";
					$this->content = substr($this->content, 0, strlen($this->content) - 1);	//語尾の|除去
					$this->content = ($this->content == _ON) ? _ON : _OFF;
					$params = array($tag_name => $this->content);	
					break;
				case "password":
					$tag_name = $this->items['tag_name'];
					// パスワード変更日時更新
					$params = array(
						$this->items['tag_name'] => $this->content,
						"password_regist_time" => timezone_date()
					);
					break;
				default :
					$tag_name = $this->items['tag_name'];
					$params = array($this->items['tag_name'] => $this->content);
			}
			$private_where_params = array(
				"{pages}.insert_user_id" => $user_id,
				"{pages}.private_flag" => _ON,
				"{pages}.page_id={pages}.room_id" => null
			);
			
			$private_pages = $this->pagesView->getPagesUsers($private_where_params, array("default_entry_flag" => "ASC"), 2);
			if($private_pages === false) return 'error';
						
			if($this->user[$tag_name] != $this->content) {
				$where_params = array("user_id" => $user_id);
				$result = $this->usersAction->updUsers($params, $where_params);
				if ($result === false) return 'error';
				if($this->items['tag_name'] == "handle" && $user_id == $this->session->getParameter("_user_id")) {
					//ハンドル名称変更ならばセッションも変更
					$this->session->setParameter("_handle", $this->content);
				} else if($this->items['tag_name'] == "lang_dirname_lang" && $user_id == $this->session->getParameter("_user_id")) {
					//言語選択変更ならばセッションも変更
					$this->session->setParameter("_lang", $this->content);
				} else if($this->items['tag_name'] == "role_authority_name") {
					// ベース権限が管理者に変更された場合、すべてのルームに主担として参加させる
					$old_authoritiy =& $this->authoritiesView->getAuthorityById(intval($this->user[$tag_name]));
					if ($old_authoritiy === false) return 'error';
					
					$authoritiy =& $this->authoritiesView->getAuthorityById(intval($this->content));
					if ($authoritiy === false) return 'error';
					if($authoritiy['user_authority_id'] == _AUTH_ADMIN) {
						$where_params = array(
											"user_id" => $user_id,
											"user_authority_id" => _AUTH_ADMIN,
											"role_authority_id" => intval($this->content)
										);
						$pages =& $this->pagesView->getShowPagesList($where_params);
						if ($pages === false) return 'error';
						$where_params = array("user_id" => $user_id);
						$result = $this->pagesAction->delPageUsersLink($where_params);
						if ($result === false) return 'error';
						foreach($pages as $page) {
							// グループスペース直下はcontinue
							if($page['thread_num'] == 0 && $page['private_flag'] == _OFF && 
								$page['space_type'] == _SPACE_TYPE_GROUP) {
								continue;
							}
							if($page['thread_num'] == 1) {
								$createroom_flag = _ON;
							} else {
								$createroom_flag = _OFF;
							}
							$where_params = array(
											"room_id" => $page['page_id'],
											"user_id" => $user_id,
											"role_authority_id" => _ROLE_AUTH_CHIEF,
											"createroom_flag" => $createroom_flag
										);
							$result = $this->pagesAction->insPageUsersLink($where_params);
							if ($result === false) return 'error';
						}
					}
					//
					// プライベートスペースが使用可能かどうかが変更されている場合、
					// それに応じてpagesテーブルのdisplay_flagも変更する(_ON or _PAGES_DISPLAY_FLAG_DISABLED)
					//
					if($old_authoritiy['myroom_use_flag'] != $authoritiy['myroom_use_flag']) {
						$private_where_params = array(
							"{pages}.insert_user_id" => $user_id,
							"{pages}.private_flag" => _ON,
							"{pages}.default_entry_flag" => _OFF
						);
						if($authoritiy['myroom_use_flag'] == _ON) {
							$set_params = array(
								"display_flag" => _ON
							);
						} else {
							// プライベートスペース使用不可
							$set_params = array(
								"display_flag" => _PAGES_DISPLAY_FLAG_DISABLED
							);
						}
						$result = $this->pagesAction->updPage($set_params, $private_where_params);
						if($result === false) return 'error';
					}
				}
				//
		    	// 固定リンク
		    	//
		    	if($this->items['tag_name'] == "handle" && isset($private_pages[0])) {
		    		$handle = $this->content;
		    		$result = $this->pagesAction->updPermaLink($private_pages[0], $handle);
			    	if($result === false)  {
			    		return 'error';	
			    	}
			    	$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
					if($config === false) return 'error';
			    	if(isset($private_pages[1]) &&
			    			$config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP ||
							$config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC) {
						$result = $this->pagesAction->updPermaLink($private_pages[1], $handle);
				    	if($result === false)  {
				    		return 'error';	
				    	}
					}
		    	}
			}
		} else {
			$users_item_links =& $this->usersView->getUserItemLinkById($user_id,  $this->items['item_id']);
			switch ($this->items['type']) {
				case "select":
				case "radio":
				case "checkbox":
					if($this->items['define_flag'] == _ON) {
						//
						//定義名称がある場合、そちらで登録
						//英語と日本語の選択されたものが同じであると認識させる必要があるため
						//
						$options_arr = explode("|", $this->items['options']);
						$buf_options_arr = $options_arr;
						$count = 0;
						foreach($options_arr as $options) {
							if(defined($options)) {
								$options_arr[$count] = constant($options);
							}
							$count++;
						}
						
						$content_options_arr = explode("|", $this->content);
						$count = 0;
						foreach($content_options_arr as $content_options) {
							if($content_options != "") {
								$count_sub = 0;
								foreach($options_arr as $options) {
									if($content_options == $options) {
										$content_options_arr[$count] = $buf_options_arr[$count_sub];
									}
									$count_sub++;
								}
							}
							$count++;
						}
						$this->content = implode("|", $content_options_arr);
					}
				case "text":
				case "textarea":
				case "email":
				case "mobile_email":
					$update_flag = false;
					if(isset($users_item_links)) {
						if($this->content != $users_item_links['content'] ||
							$this->email_reception_flag != $users_item_links['email_reception_flag'] ||
							$this->public_flag != $users_item_links['public_flag']
							) {
							$update_flag = true;
							//更新
							$params = array(
											"public_flag" => intval($this->public_flag),
											"email_reception_flag" => intval($this->email_reception_flag),
											"content" => $this->content
										);
							$where_params = array("user_id" => $user_id,"item_id" => $this->items['item_id']);
							$result = $this->usersAction->updUsersItemsLink($params, $where_params);
							if ($result === false) return 'error';
						}
					} else {
						$update_flag = true;
						$params = array(
											"user_id" => $user_id,
											"item_id" => $this->items['item_id'],
											"public_flag" => intval($this->public_flag),
											"email_reception_flag" => intval($this->email_reception_flag),
											"content" => $this->content
										);
						//新規追加
						$result = $this->usersAction->insUserItemLink($params);
						if ($result === false) return 'error';
					}
					//更新日時更新
					if($update_flag) {
						$where_params = array("user_id" => $user_id);
						$result = $this->usersAction->updUsers(array(), $where_params, true);
						if ($result === false) return 'error';
					}
					break;
			}
		}
		return 'success';
	}
}
?>
