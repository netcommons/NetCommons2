<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
//include_once MAPLE_DIR.'/includes/pear/File/Archive.php';
//include_once MAPLE_DIR.'/includes/pear/XML/Unserializer.php';

/**
* バックアップファイル-リストア処理(実行)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Action_Main_Restoreresult extends Action
{
	//
	// リクエストパラメータを受け取るため
	//
	var $upload_id = null;
	var $backup_page_id = null;
	var $module_id = null;

	// 状態　(restore_type != "top")
	// バブリックのTop or　プライベートのTopならば、必ず公開
	var $display_flag = null;

	// バックアップされた会員を参加させる（restore_type != "top" && restore_type != "subgroup"）
	// サブグループならば、必ず初期化
	// パブリックスペース、プライベートスペースならば、参加者は変更しない
	//			パブリックスペース内のルームについては、初期化する
	var $entry_user = null;

	// サブグループならばリストア位置を変更できる(restore_type == "subgroup")
	var $regist_location = null;

	// リストアするモジュール
	var $entry_modules = null;

	// 使用コンポーネントを受け取るため
	var $backupRestore = null;
	var $fileAction = null;
	var $pagesView = null;
	var $configView = null;
	var $db = null;
	var $usersView = null;
	var $actionChain = null;
	var $session = null;
	var $pagesAction = null;

	// フィルタによりセット
	//var $room_arr_flat = null;


	// 値をセットするため
	var $transfer_id_arr = array();
	var $del_physical_file_name_arr = array();
	var $temporary_file_path = "";

	var $_self_flag = false;

	// 手動ロールバックは、メモリが大量に使う可能性があるため、別ファイルに吐き出すほうがよい
	var $deleteParams = array();	// 手動ロールバック用
	var $insertParams = array();	// 手動ロールバック用
	var $uploadParams = array();	// 手動ロールバック用
	var $uploadWhereParams = array();	// 手動ロールバック用
	var $selectParams = array();	// 手動ロールバック用

	/**
	 * バックアップファイル-リストア処理(実行)
	 *
	 * @access  public
	 */
	function execute()
	{
		$temporary_file_path = FILEUPLOADS_DIR."backup/".BACKUP_TEMPORARY_DIR_NAME."/".BACKUP_RESTORE_DIR_NAME."/" . $this->backup_page_id. "/";
		$this->backupRestore->mkdirTemporary(BACKUP_RESTORE_DIR_NAME);
		$ret = $this->backupRestore->getRestoreArray($this->upload_id, $this->backup_page_id, $this->module_id, $temporary_file_path);

		if($ret === false) {
			return 'error';
		}
		$backup_uploads = $this->db->selectExecute("backup_uploads", array("upload_id" => $this->upload_id));
		if($backup_uploads === false && !isset($backup_uploads[0])) {
			return 'error';
		}
		$this->temporary_file_path = $temporary_file_path;

		list($room_inf, $restore_modules, $version_arr, $modules) = $ret;

		$restore_data = $this->backupRestore->getRoomArray();

		$restore_type = $restore_modules["system"]['restore_type'];
		if(!isset($restore_data['system']['room'])) {
			// 現状、未処理
			$this->fileAction->delDir($this->temporary_file_path);
			return 'error';
		}
		$this->_self_flag = $restore_modules["system"]['self_flag'];


		//--------------------------------------------------
		// システム関連テーブルリストア
		// - PKを振りなおす -
		// XMLは、改竄されていないとして処理（電子認証）
		//--------------------------------------------------
		$pages_buf = array();
		$page_id_arr = array();
		$pages_users_link_room_id = array();
		$normal_block_col_arr = array();
		$err_block_row_arr = array();
		$upload_id_arr = array();
		$physical_file_name_arr = array();
		$this->del_physical_file_name_arr = array();
		//$insert_blocks = array();
		$login_user_id = $this->session->getParameter("_user_id");

		// room_idで削除できる項目は、はじめに削除
		if($restore_type == "top") {
			// Topのものは置き換える対象なので
			// 現在のものを削除する
			// RollBackのため、SelectしてDelete
			$pre_top_page_id = $room_inf['page_id'];
			$pre_top_room_id = $room_inf['room_id'];
			if(!$this->_self_flag) {
				//topのpage_idを取得する
				if($room_inf['private_flag']) {
					// プライベートルーム
					$top_page = $this->pagesView->getPrivateSpaceByUserId($login_user_id, 0, 0, false);
					if($top_page === false) return 'error';
					if($room_inf['default_entry_flag'] == _ON && isset($top_page[1])) {
						$index_count = 1;
					} else {
						$index_count = 0;
					}
				} else {
					// パブリックスペース
					$where_params = array(
										"thread_num" => 0,
										"space_type" => _SPACE_TYPE_PUBLIC,
										"private_flag" => _OFF
									);
					$top_page = $this->pagesView->getPages($where_params);
					$index_count = 0;
				}
				if($top_page === false || !isset($top_page[$index_count])) {
					return 'error';
				}
				$room_inf['page_id'] = $top_page[$index_count]['page_id'];
				$room_inf['room_id'] = $top_page[$index_count]['room_id'];
				$this->transfer_id_arr['page_id'][$pre_top_page_id] = $top_page[$index_count]['page_id'];
			}
			if(!$this->_delRoomDb($room_inf['page_id'])) {
				$this->fileAction->delDir($this->temporary_file_path);
				return 'error';
			}
		}

		//$adodb = $this->db->getAdoDbObject();
		foreach($restore_data['system']['room'] as $room_id => $restore_table_arr) {
			//
			// room_id毎にリストアしていく
			//
			foreach($restore_table_arr as $table_name => $rec_sets) {
				foreach($rec_sets as $rec_set) {
					if($restore_type == "top" && !$this->_self_flag) {
						if(isset($rec_set['page_id']) && $rec_set['page_id'] == $pre_top_page_id) {
							$rec_set['page_id'] = $room_inf['page_id'];
						}
						if(isset($rec_set['room_id']) && $rec_set['room_id'] == $pre_top_room_id) {
							$rec_set['room_id'] = $room_inf['room_id'];
						}
					}
					if ($restore_type == "top" && $room_inf['private_flag'] == _ON) {
						if (isset($rec_set['insert_site_id'])) {
							$rec_set['insert_site_id'] = $this->session->getParameter("_site_id");
						}
						if (isset($rec_set['insert_user_id'])) {
							$rec_set['insert_user_id'] = $this->session->getParameter("_user_id");
						}
						if (isset($rec_set['update_site_id'])) {
							$rec_set['update_site_id'] = $this->session->getParameter("_site_id");
						}
						if (isset($rec_set['update_user_id'])) {
							$rec_set['update_user_id'] = $this->session->getParameter("_user_id");
						}
					}

					if($table_name == "pages") {
						//-------------------------------------------------------------------------
						// ページテーブル
						//-------------------------------------------------------------------------
						$prev_page_id = $rec_set['page_id'];
						$prev_thread_num = $rec_set['thread_num'];
						if($restore_type == "top" && $rec_set['thread_num'] == 0) {
							// パブリックスペース、プライベートスペースならば、ID変更なし
							// Insertも行わない
							$this->_transferId($table_name, $rec_set, "page_id", null, $rec_set['page_id']);
						} else if($rec_set['display_position'] != _DISPLAY_POSITION_CENTER){
							// 左右カラム、ヘッダー、フッターならばID変更なし
							// Insertも行わない
							$this->_transferId($table_name, $rec_set, "page_id", null, $rec_set['page_id']);
						} else {
							// サブグループならばリストア位置を変更
							if($this->regist_location != null && $restore_type  == "subgroup") {
								$restore_page = $this->pagesView->getPageById(intval($this->regist_location));
								if($restore_page === false || !isset($restore_page['page_id'])) {
									$this->_rollBack();
									return 'error';
								}
								if((($this->backup_page_id == 0 && $backup_uploads[0]['thread_num'] == $prev_thread_num) || $this->backup_page_id == $prev_page_id) &&
									$restore_page['authority_id'] >= _AUTH_CHIEF && $restore_page['createroom_flag'] == _ON) {
									// リストアOKなルームID
									$rec_set['parent_id'] = intval($this->regist_location);
								}
							}

							// ページ名称
							if($restore_type != "top" && ($this->backup_page_id == $rec_set['page_id'] || ($this->backup_page_id == 0 && $backup_uploads[0]['thread_num'] == $prev_thread_num))) {
								$rec_set['page_name'] = $room_inf['page_name'];
								$display_sequence = $this->pagesView->getMaxChildPage($rec_set['parent_id'], $rec_set['lang_dirname']);
								$display_sequence = intval($display_sequence) + 1;
								$rec_set['display_sequence'] = $display_sequence;
							}

							// page_id振替
							$this->_transferId($table_name, $rec_set, "page_id");

							//if($restore_type != "top") {
								// room_id振替
								$this->_transferId($table_name, $rec_set, "page_id", "room_id");
							//}
							if(isset($this->transfer_id_arr['page_id'][$rec_set['root_id']])) {
								// root_id振替
								$this->_transferId($table_name, $rec_set, "page_id", "root_id");
							}
							if(isset($this->transfer_id_arr['page_id'][$rec_set['parent_id']])) {
								// parent_id振替
								//if($this->regist_location != null && $restore_type  == "subgroup") {
								//	$this->_transferId($table_name, $rec_set, "page_id", "parent_id", $rec_set['parent_id']);
								//} else {
									$this->_transferId($table_name, $rec_set, "page_id", "parent_id");
								//}
							}
							// display_flagを_PAGES_DISPLAY_FLAG_DISABLEDにしておく
							// TODO:準備中のルームも最終的に公開中にされてしまうが、仕様とする
							if($rec_set['page_id'] == $rec_set['room_id']) {
								$rec_set['display_flag'] = _PAGES_DISPLAY_FLAG_DISABLED;
							} else {
								$rec_set['display_flag'] = _ON;
							}
							$rec_set['site_id'] = $this->session->getParameter("_site_id");
							$result = $this->db->insertExecute($table_name, $rec_set, false);
							if ($result === false) {
								$this->_rollBack();
								return 'error';
							}

							$this->deleteParams[$table_name][] = $rec_set;
						}
						// permalink
						if(($this->backup_page_id == 0 && $backup_uploads[0]['thread_num'] == $prev_thread_num) || $this->backup_page_id == $prev_page_id) {
							$pages_permalink = $rec_set;
						}
						// Bufferセット
						$page_id_arr[] = $rec_set['page_id'];
						$pages_buf[$rec_set['page_id']] = $rec_set;
					} else if($table_name == "pages_modules_link") {
						//-------------------------------------------------------------------------
						// ページモジュールリンクテーブル
						// module_idからリストア先のmodule_idに変換
						// エラーがあったモジュールはリストアしない
						//-------------------------------------------------------------------------
						if($restore_type == "top" && $room_inf['page_id'] == $rec_set['room_id']) {
							// パブリックスペース、プライベートスペースならば、ID変更なし
							// Insertも行わない
							$this->_transferId($table_name, $rec_set, "page_id", "room_id", $rec_set['room_id']);
						} else {
							// dirname取得
							if(isset($version_arr['__'.$rec_set['module_id']])) {
								$dirname = $version_arr['__'.$rec_set['module_id']];
								if(!isset($restore_modules[$dirname]['error_mes'])) {
									// エラーなし
									// module_id振替
									$this->_transferId($table_name, $rec_set, "module_id", null, $modules[$dirname]['module_id']);

									// room_id振替
									$this->_transferId($table_name, $rec_set, "page_id", "room_id");

									$result = $this->db->insertExecute($table_name, $rec_set, false);
									if ($result === false) {
										$this->_rollBack();
										return 'error';
									}
									$this->deleteParams[$table_name][] = $rec_set;
								}

							}
						}
					} else if($table_name == "pages_users_link") {
						//-------------------------------------------------------------------------
						// ページユーザーリンクテーブル
						//
						// バックアップされた会員を参加させる（restore_type != "top" && restore_type != "subgroup"）
						// サブグループならば、必ず初期化
						// パブリックスペース、プライベートスペースならば、参加者は変更しない
						//			パブリックスペース内のルームについては、初期化する
						//-------------------------------------------------------------------------
						if($restore_type == "top" && $room_inf['page_id'] == $rec_set['room_id']) {
							// 参加者情報はリストアしない（現状のまま）
						} else{
							// room_id振替
							$this->_transferId($table_name, $rec_set, "page_id", "room_id");

							if ($restore_type != "top" && $restore_type != "subgroup" &&  $this->entry_user == _ON &&
								(!isset($admin_users) || !isset($admin_users[$rec_set['user_id']]))) {
								// バックアップされた会員を参加させる
								// 登録されていない会員がいれば、INSERTしない
								$pages_users_link_user = $this->usersView->getUserById($rec_set['user_id']);
								if(isset($pages_users_link_user['user_id'])) {

									$result = $this->db->insertExecute($table_name, $rec_set, false);
									if ($result === false) {
										$this->_rollBack();
										return 'error';
									}
									$this->deleteParams[$table_name][] = $rec_set;
								}
							}

							//
							// リストアを行った会員、管理者は少なくとも参加
							//
							// 管理者一覧取得
							if(!isset($admin_users)) {
								if($pages_buf[$rec_set['room_id']]['private_flag'] == _OFF) {
									// プライベートルームは自分自身のみ
									$admin_users = $this->usersView->getUsers(array("user_authority_id" => _AUTH_ADMIN), null,  array($this, "_usersFetchcallback"));
									if($admin_users === false) {
										$this->_rollBack();
										return 'error';
									}
								} else {
									$admin_users = array();
								}
							}
							// 自分自身は主担として参加させる
							if(!isset($admin_users[$login_user_id])) {
								$admin_users[$login_user_id] = $this->usersView->getUserById($login_user_id);
								if($admin_users[$login_user_id] === false) {
									$this->_rollBack();
									return 'error';
								}
							}

							// room_id振替
							//$this->_transferId($table_name, $rec_set, "page_id", "room_id");
							if(!isset($pages_users_link_room_id[$rec_set['room_id']])) {
								// 未だ初期化がすんでいないroom_id
								if($pages_buf[$rec_set['room_id']]['space_type'] == _SPACE_TYPE_PUBLIC) {
									$rec_set['createroom_flag'] = _OFF;
								} else if($pages_buf[$rec_set['room_id']]['private_flag'] == _OFF &&
										$pages_buf[$rec_set['room_id']]['thread_num'] == 1) {
									$rec_set['createroom_flag'] = _ON;
								} else {
									$rec_set['createroom_flag'] = _OFF;
								}
								$rec_set['role_authority_id'] = _ROLE_AUTH_CHIEF;

								//$pages_users_link_room_id[$rec_set['room_id']] = true;
								foreach($admin_users as $user_id => $admin_user) {
									$rec_set['user_id'] = $user_id;
									$sel_pages_users_link = $this->db->selectExecute("pages_users_link", array("room_id" => $rec_set['room_id'],"user_id" => $user_id));
									if($sel_pages_users_link === false || isset($sel_pages_users_link[0])) {
										// 既にINSERT済み
										continue;
									}
									$result = $this->db->insertExecute($table_name, $rec_set, false);
									if ($result === false) {
										$this->_rollBack();
										return 'error';
									}
									$this->deleteParams[$table_name][] = $rec_set;
								}
							}
						}
					} else if($table_name == "pages_meta_inf") {
						//-------------------------------------------------------------------------
						// pages_meta_infテーブル
						//-------------------------------------------------------------------------
						// page_id振替
						$this->_transferId($table_name, $rec_set, "page_id");

						$result = $this->db->insertExecute($table_name, $rec_set, false);
						if ($result === false) {
							$this->_rollBack();
							return 'error';
						}
						$this->deleteParams[$table_name][] = $rec_set;
					} else if($table_name == "pages_style") {
						//-------------------------------------------------------------------------
						// ページスタイルテーブル
						// 自サイトでないならば、本テーブルは、デフォルトに強制的に戻す
						// header_flag,leftcolumn_flag等が、権限によっては変更できなくなるおそれがあるため
						// 権限毎で、レイアウトを変更できるかどうかをもっているので
						// レイアウト変更できる権限の場合のみリストアすることも可能だが、現状、しない
						//-------------------------------------------------------------------------
						if($restore_modules["system"]['self_flag']) {
							// set_page_id振替
							$this->_transferId($table_name, $rec_set, "page_id", "set_page_id");

							$result = $this->db->insertExecute($table_name, $rec_set, false);
							if ($result === false) {
								$this->_rollBack();
								return 'error';
							}
							$this->deleteParams[$table_name][] = $rec_set;
						}
					} else if($table_name == "monthly_number") {
						//-------------------------------------------------------------------------
						// 月別一覧テーブル
						//-------------------------------------------------------------------------
						$insert_flag = false;

						if($rec_set['module_id'] == 0) {
							// アクセス数等
							$insert_flag = true;
						} else if(isset($version_arr['__'.$rec_set['module_id']])) {
							$dirname = $version_arr['__'.$rec_set['module_id']];
							if(!isset($restore_modules[$dirname]['error_mes'])) {
								// エラーなし
								// module_id振替
								$this->_transferId($table_name, $rec_set, "module_id", null, $modules[$dirname]['module_id']);
								if(isset($this->entry_modules[$dirname]) &&
									$this->entry_modules[$dirname] == _ON) {
									// リストアするモジュールに含まれている
									$insert_flag = true;
								}

							}
						}
						/* ループ前に行うので、ここはコメント　後に削除
						if($restore_type == "top" && $room_inf['page_id'] == $rec_set['room_id']) {
							// Topのものは置き換える対象なので
							// 現在のものを削除する
							// RollBackのため、SelectしてDelete
							$sel_where_params = array(
								"room_id" => $rec_set['room_id']
							);
							//$sel_where_params = array(
							//	"user_id" => $rec_set['user_id'],
							//	"room_id" => $rec_set['room_id'],
							//	"module_id" => $rec_set['module_id']
							//);
							if(!$this->_selectDelete($table_name, $sel_where_params)) {
								return 'error';
							}
						}
						*/
						if($insert_flag == true) {
							// room_id振替
							$this->_transferId($table_name, $rec_set, "page_id", "room_id");

							$result = $this->db->insertExecute($table_name, $rec_set, false);
							if ($result === false) {
								$this->_rollBack();
								return 'error';
							}
							$this->deleteParams[$table_name][] = $rec_set;
						}
					} else if($table_name == "blocks") {
						//-------------------------------------------------------------------------
						// ブロックテーブル
						//-------------------------------------------------------------------------
						if($rec_set['module_id'] == 0) {
							// グループ化されたブロック
							// 内部にモジュールがあるかどうかチェック
							// なければリストア対象としない
							if(!isset($normal_block_col_arr[$rec_set['page_id']][$rec_set['block_id']])) {
								$dirname = "";
							} else {
								$dirname = "_grouping";
							}
						} else if(isset($version_arr['__'.$rec_set['module_id']])) {
							$dirname = $version_arr['__'.$rec_set['module_id']];
						} else {
							$dirname = "";
						}
						if(($dirname != "_grouping" &&
							(!isset($this->entry_modules[$dirname]) ||
							$this->entry_modules[$dirname] == _OFF ||
							$dirname == "" ||
							isset($restore_modules[$dirname]['error_mes'])
							))) {
							// エラー、または、
							// 「リストアしない」がチェックされている
							// ブロックテーブルを詰める

							$err_block_row_arr[$rec_set['page_id']][$rec_set['parent_id']][$rec_set['col_num']][$rec_set['row_num']] = true;
						} else {
							// エラーなし
							$normal_block_col_arr[$rec_set['page_id']][$rec_set['parent_id']][$rec_set['col_num']] = true;
							// 詰めるかどうかチェック
							if($rec_set['row_num'] > 1 && isset($err_block_row_arr[$rec_set['page_id']][$rec_set['parent_id']])) {
								$set_row_num = $rec_set['row_num'];
								$chk_row_num = intval($set_row_num) - 1;
								while(1) {
									if($chk_row_num == 0) break;
									if(isset($err_block_row_arr[$rec_set['page_id']][$rec_set['parent_id']][$rec_set['col_num']][$chk_row_num])) {
										$set_row_num--;
									}
									$chk_row_num--;
								}
								$rec_set['row_num'] = $set_row_num;
							}
							if($rec_set['col_num'] > 1) {
								$set_col_num = $rec_set['col_num'];
								$chk_col_num = intval($set_col_num) - 1;
								while(1) {
									if($chk_col_num == 0) break;
									if(!isset($normal_block_col_arr[$rec_set['page_id']][$rec_set['parent_id']][$chk_col_num])) {
										$set_col_num--;
									}
									$chk_col_num--;
								}
								$rec_set['col_num'] = $set_col_num;
							}

							// block_id振替
							$this->_transferId($table_name, $rec_set, "block_id");

							// page_id振替
							$this->_transferId($table_name, $rec_set, "page_id");

							// root_id振替
							if($rec_set['root_id'] != 0) {
								$this->_transferId($table_name, $rec_set, "block_id", "root_id");
							}
							if($rec_set['parent_id'] != 0) {
								$this->_transferId($table_name, $rec_set, "block_id", "parent_id");
							}

							// module_id振替
							if($dirname != '' && $dirname != "_grouping") {
								$this->_transferId($table_name, $rec_set, "module_id", null, $modules[$dirname]['module_id']);
							}
							$result = $this->db->insertExecute($table_name, $rec_set, false);
							if ($result === false) {
								$this->_rollBack();
								return 'error';
							}
							$this->deleteParams[$table_name][] = $rec_set;
						}
					} else if($table_name == "uploads") {
						//-------------------------------------------------------------------------
						// アップロードテーブル
						// アップロードする実ファイルは、リストアが完全に終わった後
						// コピー or 削除を行う
						// 現状、アップロードできる拡張子以外のものがあってもリストアしている
						//-------------------------------------------------------------------------
						$insert_flag = false;

						if(isset($version_arr['__'.$rec_set['module_id']])) {
							$dirname = $version_arr['__'.$rec_set['module_id']];
							if(!isset($restore_modules[$dirname]['error_mes'])) {
								// エラーなし
								// module_id振替
								$this->_transferId($table_name, $rec_set, "module_id", null, $modules[$dirname]['module_id']);

								if(isset($this->entry_modules[$dirname]) &&
									$this->entry_modules[$dirname] == _ON) {
									// リストアするモジュールに含まれている
									$insert_flag = true;
								}
							}
						}
						/* ループ前に行うので、ここはコメント　後に削除
						if($restore_type == "top" && $room_inf['page_id'] == $rec_set['room_id']) {
							// Topのものは置き換える対象なので
							// 現在のものを削除する
							// RollBackのため、SelectしてDelete
							$sel_where_params = array(
								"room_id" => $rec_set['room_id']
							);
							if(!$this->_selectDelete($table_name, $sel_where_params)) {
								return 'error';
							}
						}
						*/
						if($insert_flag == true) {
							//$old_upload_id = $rec_set['upload_id'];
							$old_physical_file_name = $rec_set['file_path'] . $rec_set['physical_file_name'];
							// upload_id振替
							$this->_transferId($table_name, $rec_set, "upload_id");

							// room_id振替
							$this->_transferId($table_name, $rec_set, "page_id", "room_id");

							if($rec_set['extension'] != "") {
								$rec_set['physical_file_name'] = $rec_set['upload_id'].".".$rec_set['extension'];
							} else {
								$rec_set['physical_file_name'] = $rec_set['upload_id'];
							}
							//unique_idの振替が必要な場合、モジュール毎のリストア時に行う

							// insert
							$result = $this->db->insertExecute($table_name, $rec_set, false);
							if ($result === false) {
								$this->_rollBack();
								return 'error';
							}
							$key = $rec_set['upload_id'];
							$this->deleteParams[$table_name][$key] = $rec_set;

							// upload_id保存
							$upload_id_arr[$dirname][$key] = $rec_set['unique_id'];
							$physical_file_name_arr[$old_physical_file_name] = $rec_set['file_path'] . $rec_set['physical_file_name'];
						}
					}
				}
			}
		}

		// permalink
		if(isset($pages_permalink)) {
			$permalink = $pages_permalink['permalink'];
			$buf_permalink = $permalink;
			$permalink_arr = explode('/', $permalink);
			if($restore_type != "top") {
				$replace_page_name = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $pages_permalink['page_name']);
				$permalink_arr[count($permalink_arr)-1] = $replace_page_name;
			}
			$current_permalink = $permalink_arr[count($permalink_arr)-1];
			$permalink = implode('/', $permalink_arr);

			if(isset($restore_page)) {
				$new_parmalink = $this->pagesAction->updPermaLink($pages_permalink, $current_permalink, $restore_page);
			} else {
				$new_parmalink = $this->pagesAction->updPermaLink($pages_permalink, $current_permalink);
			}
			if($buf_permalink != $new_parmalink) {
				$deleteParamsCnt = count($this->deleteParams['pages']);
				for($i = 0; $i < $deleteParamsCnt; $i++) {
					if($this->deleteParams['pages'][$i]['page_id'] == $pages_permalink['page_id']) {
						$this->deleteParams['pages'][$i]['permalink'] = $new_parmalink;
					}
				}
			}
		}

		//--------------------------------------------------
		// 一般系テーブルリストア
		//--------------------------------------------------
		$all_transfer_id_arr = array();
		$transfer_user_id_arr = array();
		foreach($restore_data as $dirname => $modules_restore_arr) {
			if($dirname == "system") {
				continue;
			}
			//
			// リストア対象かどうかチェック
			//
			if(isset($restore_modules[$dirname]['error_mes'])) {
				// エラーあり
				continue;
			}
			if(!isset($this->entry_modules[$dirname]) ||
					$this->entry_modules[$dirname] == _OFF) {
				// 「リストアしない」がチェックされている
				continue;
			}
			if(!isset($restore_modules[$dirname]['transfer_list'])) {
				continue;
			}
			if(isset($all_transfer_id_arr[$dirname])) {
				$transfer_id_arr = $all_transfer_id_arr[$dirname];
			} else {
				$transfer_id_arr = array();
			}
			$transfer_module_id_arr = array();
			$transfer_core_id_arr = array();
			$transfer_other_module_id_arr = array();
			$transfer_separator_id_arr = array();
			//$buf_transfer_id_arr = explode(",", $restore_modules[$dirname]['transfer_list']['_transfer_id']);
			foreach($restore_modules[$dirname]['transfer_list'] as $transfer_id => $transfer_value) {
				//
				//(table_name).column_name=(core or wysiwyg or dirname).(column_name)
				//
				//
				// key
				//
				$transfer_key_id_arr = explode(".", $transfer_id);
				if(count($transfer_key_id_arr) > 1) {
					$key_table_name = $transfer_key_id_arr[0];	// table_name
					$key_subject = $transfer_key_id_arr[1];
				} else {
					$key_table_name = "all";	//"";
					$key_subject = $transfer_key_id_arr[0];
				}
				//
				// value
				//
				$separator = "";
				if($transfer_value != "") {
					//
					// separator指定
					//
					$transfer_value_arr = explode(":", $transfer_value);
					if(count($transfer_value_arr) > 1) {
						$transfer_value = $transfer_value_arr[0];
						$transfer_value_arr = explode("=", $transfer_value_arr[1]);
						if(count($transfer_value_arr) > 1 && $transfer_value_arr[0] == "separator") {
							$separator = trim($transfer_value_arr[1]);
						}
					}

					$transfer_value_arr = explode(".", $transfer_value);
					if(count($transfer_value_arr) > 1) {
						// table_name or core or wysiwyg or dirname
						$value_table_name = $transfer_value_arr[0];
						$value_subject = $transfer_value_arr[1];
					} else {
						$value_table_name = "";
						$value_subject = $transfer_value_arr[0];
					}
				} else {
					$value_table_name = $key_table_name;
					$value_subject = $key_subject;
				}
				if($key_table_name == "") $key_table_name = "all";

				$transfer_module_id_arr[$key_table_name][$key_subject] = $value_subject;
				if($value_table_name == "core" || $value_table_name == "wysiwyg" || $value_table_name == "text" || $value_table_name == "physical_file_name") {
					$transfer_core_id_arr[$key_table_name][$key_subject] = $value_table_name;
				} else if($value_table_name != "all") {
					$transfer_other_module_id_arr[$key_table_name][$key_subject] = $value_table_name;
				}
				if($separator != "") {
					$transfer_separator_id_arr[$key_table_name][$key_subject] = $separator;
				}
			}

			//
			// room_id毎にリストアしていく
			//
			foreach($modules_restore_arr['room'] as $room_id => $restore_table_arr) {
				foreach($restore_table_arr as $table_name => $rec_sets) {
					if(isset($transfer_module_id_arr[$table_name]["*"]) &&
						$transfer_module_id_arr[$table_name]["*"] == "none_transfer") {
						// 本テーブルは振替対象外
						continue;
					}
					// ハンドル名が重複する可能性があるため、
					// ハンドル名称を振り替える
					$this->_transferIdHandle($transfer_user_id_arr, $rec_sets);

					foreach($rec_sets as $rec_set) {
						if($restore_type == "top" && !$this->_self_flag) {
							if(isset($rec_set['page_id']) && $rec_set['page_id'] == $pre_top_page_id) {
								$rec_set['page_id'] = $room_inf['page_id'];
							}
							if(isset($rec_set['room_id']) && $rec_set['room_id'] == $pre_top_room_id) {
								$rec_set['room_id'] = $room_inf['room_id'];
							}
						}

						if($rec_set["room_id"] && $rec_set["room_id"] == BACKUP_NULL_COLUMN) {
						//if($this->_self_flag && $rec_set["room_id"] && $rec_set["room_id"] == BACKUP_NULL_COLUMN) {
							//
							// room_idがnullのカラムがあった場合
							//
							continue;
						}

						foreach($rec_set as $column_name => $value) {
							// null表記
							if($value == BACKUP_NULL_COLUMN) {
								$rec_set[$column_name] = null;
								$value = null;
							}
							if($value != "0") {
								$transfer_column_name = "";
								if(isset($transfer_module_id_arr["all"][$column_name])) {
									$transfer_column_name = $transfer_module_id_arr["all"][$column_name];
									$set_key = "all";
								} else if(isset($transfer_module_id_arr[$table_name][$column_name])) {
									$transfer_column_name = $transfer_module_id_arr[$table_name][$column_name];
									$set_key = $table_name;
								}
								$mode = "";
								if(isset($set_key) && isset($transfer_core_id_arr[$set_key][$column_name])) {
									$mode = $transfer_core_id_arr[$set_key][$column_name];
								}
								$other_module = "";
								if(isset($set_key) && isset($transfer_other_module_id_arr[$set_key][$column_name])) {
									$other_module = $transfer_other_module_id_arr[$set_key][$column_name];
								}
								$separator = "";
								if(isset($set_key) && isset($transfer_separator_id_arr[$set_key][$column_name])) {
									$separator = $transfer_separator_id_arr[$set_key][$column_name];
								}

								if($transfer_column_name == "none_transfer") {
									// 振替ない
								} else if($transfer_column_name != "") {
									if($mode != "" && $mode != "wysiwyg" && $mode != "text" && $mode != "physical_file_name" && !isset($this->transfer_id_arr[$transfer_column_name][$rec_set[$column_name]])) {
										// Coreから振替
										continue;
									}
									if($separator == "") {
										if($mode != "") {
											// Coreから振替
											if($transfer_column_name == "module_id") {
												if(isset($version_arr['__'.$rec_set[$column_name]])) {
													// module_idに対応したモジュールあり
													$this->_transferId($table_name, $rec_set, $transfer_column_name, $column_name, $modules[$version_arr['__'.$rec_set[$column_name]]]['module_id'], $mode);
												}
											} else {
												$this->_transferId($table_name, $rec_set, $transfer_column_name, $column_name, null, $mode);
											}
										} else if($other_module != "") {
											$this->_transferIdModules($dirname, $all_transfer_id_arr[$other_module], $table_name, $rec_set, $transfer_column_name, $column_name);
										} else {
											$this->_transferIdModules($dirname, $transfer_id_arr, $table_name, $rec_set, $transfer_column_name, $column_name);
										}
									} else {
										$rec_set_column_arr = explode($separator, $rec_set[$column_name]);
										$rec_set_column_str = "";
										foreach($rec_set_column_arr as $rec_set_column) {
											if($rec_set_column_str != "") {
												$rec_set_column_str .= ",";
											}
											if($rec_set_column != "") {
												$rec_set[$column_name] = $rec_set_column;
												if($mode != "") {
													// Coreから振替
													if($transfer_column_name == "module_id") {
														if(isset($version_arr['__'.$rec_set[$column_name]])) {
															// module_idに対応したモジュールあり
															$this->_transferId($table_name, $rec_set, $transfer_column_name, $column_name, $modules[$version_arr['__'.$rec_set[$column_name]]]['module_id'], $mode);
														}
													} else {
														$this->_transferId($table_name, $rec_set, $transfer_column_name, $column_name, null, $mode);
													}
												} else if($other_module != "") {
													$this->_transferIdModules($dirname, $all_transfer_id_arr[$other_module], $table_name, $rec_set, $transfer_column_name, $column_name);
												} else {
													$this->_transferIdModules($dirname, $transfer_id_arr, $table_name, $rec_set, $transfer_column_name, $column_name);
												}
												$rec_set_column_str .= $rec_set[$column_name];
											}
										}
										$rec_set[$column_name] = $rec_set_column_str;
									}
								}

							}
						}

						$result = $this->db->insertExecute($table_name, $rec_set, false);
						if ($result === false) {
							$this->_rollBack();
							return 'error';
						}
						$this->deleteParams[$table_name][] = $rec_set;
					}
				}
			}
			//
			// uploadsテーブルのunique_idの振替
			//
			if(isset($transfer_module_id_arr['uploads']['unique_id']) && isset($upload_id_arr[$dirname]) && $upload_id_arr[$dirname]) {
				$other_module = "";
				if(isset($transfer_other_module_id_arr['uploads']['unique_id'])) {
					$other_module = $transfer_other_module_id_arr['uploads']['unique_id'];
				}
				$transfer_column_name = $transfer_module_id_arr['uploads']['unique_id'];
				foreach($upload_id_arr[$dirname] as $new_upload_id => $unique_id) {
					if($unique_id != "0") {
						$buf_rec_set['unique_id'] = $unique_id;
						// 振替対象
						// Coreからの振替はないものとして処理
						if($other_module != "") {
							$this->_transferIdModules($dirname, $all_transfer_id_arr[$other_module], 'uploads', $buf_rec_set, $transfer_column_name, 'unique_id');
						} else {
							$this->_transferIdModules($dirname, $transfer_id_arr, 'uploads', $buf_rec_set, $transfer_column_name, 'unique_id');
						}
						$upd_params = array("unique_id" => $buf_rec_set['unique_id']);
						$upd_where_params = array("upload_id" => $new_upload_id);
						$result = $this->db->updateExecute('uploads', $upd_params, $upd_where_params, false);
						if ($result === false) {
							$this->_rollBack();
							return false;
						}

						$this->deleteParams['uploads'][$new_upload_id]['unique_id'] = $buf_rec_set['unique_id'];
					}
				}
			}

			//
			// 振替たIDをセット
			// モジュール間の振替が必要なモジュールがあるため（施設予約、TODOのカレンダーID等）
			//
			$all_transfer_id_arr[$dirname] = $transfer_id_arr;
		}
		// test
		//$this->_rollBack();

		//---------------------------------------------------------
		// 画像コピー・削除
		//---------------------------------------------------------
		//$this->del_physical_file_name_arr
		foreach($this->del_physical_file_name_arr as $del_name) {
			if(file_exists(FILEUPLOADS_DIR.$del_name)) {
				// エラー処理は行わない
				$this->fileAction->delDir(FILEUPLOADS_DIR.$del_name);
			}
		}
		//$physical_file_name_arr
		foreach($physical_file_name_arr as $old_name => $new_name) {
			if(file_exists($temporary_file_path. "uploads/".$old_name)) {
				$this->fileAction->copyFile($temporary_file_path. "uploads/".$old_name, FILEUPLOADS_DIR . $new_name);
			}
		}
		$this->fileAction->delDir($this->temporary_file_path);
		//---------------------------------------------------------
		// 正常終了
		//---------------------------------------------------------
		$this->display_flag = ($this->display_flag == _OFF && $this->display_flag != null) ? _OFF : _ON;
		// display_flag=_PAGES_DISPLAY_FLAG_DISABLEDを元に戻す
		$params = array(
			"display_flag" => $this->display_flag
		);
		$where_params = array(
			"page_id IN (". implode(",", $page_id_arr). ") " => null,
			"{pages}.page_id = {pages}.room_id" => null
		);
		//page_id IN (". implode(",", $page_id_arr). ") ";
		$result = $this->db->updateExecute("pages", $params, $where_params, false);

		// 正常終了
		$errorList =& $this->actionChain->getCurErrorList();
		$errorList->add("backup", BACKUP_RESTORE_SUCCESS_MES);

		return 'success';
	}

	// 手動ロールバック
	function _rollBack()
	{
		foreach($this->deleteParams as $table_name => $params) {
			if(is_array($params)) {
				foreach($params as $param) {
					//nullの値は除去
					foreach($param as $key => $column) {
						if(is_null($column)) {
							unset($param[$key]);
						}
					}
					$result = $this->db->deleteExecute($table_name, $param);
					// エラー処理を行わない
				}
			}
		}
		foreach($this->uploadParams as $table_name => $params) {
			if(is_array($params)) {
				$count = 0;
				$uploadWhereParams =& $this->uploadWhereParams[$table_name];
				foreach($params as $param) {
					$result = $this->db->updateExecute($table_name, $param, $uploadWhereParams[$count], false);
					// エラー処理を行わない
					$count++;
				}
			}
		}
		foreach($this->insertParams as $table_name => $params) {
			if(is_array($params)) {
				foreach($params as $param) {
					$result = $this->db->insertExecute($table_name, $param);
					// エラー処理を行わない
				}
			}
		}
		if($this->temporary_file_path != "") {
			$this->fileAction->delDir($this->temporary_file_path);
		}
	}

	/**
	 * 配列を文字列として連結
	 * selectParams用
	 * @access	private
	 */
	function _toString($where_params = array())
	{
		$str = "";
		foreach($where_params as $key => $param) {
			$str .= "|" . $key . "='" . $param . "'";
		}
		return $str;
	}

	/**
	 * RollBackのため、SelectしてDeleteするメソッド
	 * @access	private
	 */
	function _selectDelete($table_name, $sel_where_params = array())
	{
		$whereStr = $this->_toString($sel_where_params);
		if(!isset($this->selectParams[$table_name][$whereStr])) {
			if(isset($sel_where_params['room_id'])) {
				$adodb = $this->db->getAdoDbObject();
				$metaColumns = $adodb->MetaColumns($this->db->getPrefix().$table_name);
				if(!isset($metaColumns["ROOM_ID"])) {
					return true;
				}
			}

			$sel_result = $this->db->selectExecute($table_name, $sel_where_params);
			if ($sel_result === false) {
				$this->_rollBack();
				return false;
			}
			$this->selectParams[$table_name][$whereStr] = $sel_where_params;

			if(is_array($sel_result) && count($sel_result) > 0) {
				$result = $this->db->deleteExecute($table_name, $sel_where_params);
				if ($result === false) {
					$this->_rollBack();
					return false;
				}
				foreach($sel_result as $row) {
					if($table_name == "uploads") {
						// 削除対象のファイルを保存
						$this->del_physical_file_name_arr[$row['file_path'] . $row['physical_file_name']] = $row['file_path'] . $row['physical_file_name'];
					}
					// insertロールバック用に保存
					$this->insertParams[$table_name][] = $row;
				}
			}
		}
		return true;
	}

	/**
	 * ID変換-変換ID保存処理
	 * @param string table_name
	 * @param array  column_records
	 * @param string 変換配列保存キー
	 * @param string 変換カラム名称
	 * @param int 変換ID 指定がなければ振りなおす（nextSeq）
	 * @param string core or wysiwyg
	 * @access	private
	 */
	function _transferId($table_name, &$rec_set, $transfer_column_name, $column_name = null, $transfer_id = null, $mode=null) {
		$column_name = ($column_name == null) ? $transfer_column_name : $column_name;
		if($transfer_column_name == "upload_id" && $mode == "wysiwyg") {
			//
			// WYSIWYGとして判断
			// upload_idが含まれており、変換対象のupload_idならば振替
			// 現在、imgタグ、aタグしか考慮していないが、Flashを置けるようにする等した場合、
			// objectタグも考慮しなければならない
			//
			$pattern = _REGEXP_UPLOAD_ID;
			$matches = null;

			if(preg_match_all ($pattern, $rec_set[$column_name], $matches)) {
				if(isset($matches[1])) {
					foreach($matches[1] as $upload_id) {
						if(isset($this->transfer_id_arr[$transfer_column_name][$upload_id])) {
							// 振替対象
							$pattern = _REGEXP_PRE_TRANSFER_UPLOAD_ID.$upload_id._REGEXP_POST_TRANSFER_UPLOAD_ID;
							$replacement = '${1}'.$this->transfer_id_arr[$transfer_column_name][$upload_id].'${2}';
							$rec_set[$column_name] =preg_replace($pattern, $replacement, $rec_set[$column_name]);
						}
					}
				}
			}

		} else if($transfer_column_name == "upload_id" && $mode == "text") {
			//
			// textとして判断
			// upload_idが含まれており、変換対象のupload_idならば振替
			// upload_idつきのパスが保存されているようなモジュールで使用
			//
			$pattern = "/upload_id=([0-9]+)/i";
			$matches = null;
			if(preg_match_all ($pattern, $rec_set[$column_name], $matches)) {
				if(isset($matches[1])) {
					foreach($matches[1] as $upload_id) {
						if(isset($this->transfer_id_arr[$transfer_column_name][$upload_id])) {
							// 振替対象
							$pattern = '/(upload_id=)'.$upload_id.'((?:.|\s)*?)/iUu';
							$replacement = '${1}'.$this->transfer_id_arr[$transfer_column_name][$upload_id].'${2}';
							$rec_set[$column_name] =preg_replace($pattern, $replacement, $rec_set[$column_name]);
						}
					}
				}
			}
		} else if($transfer_column_name == "upload_id" && $mode == "physical_file_name") {
			//
			// physical_file_name として判断(upload_id+extension)
			// upload_idが含まれており、変換対象のupload_idならば振替
			// physical_file_nameとしてモジュールで保存されているカラムで使用
			//
			$buf_physical_file_name_arr = explode("." ,$rec_set[$column_name]);
			if(isset($this->transfer_id_arr[$transfer_column_name][intval($buf_physical_file_name_arr[0])])) {
				$buf_old_upload_id = $this->transfer_id_arr[$transfer_column_name][intval($buf_physical_file_name_arr[0])];
			} else {
				$buf_old_upload_id = null;
			}
			if(count($buf_physical_file_name_arr) > 0 && isset($buf_old_upload_id)) {
				unset($buf_physical_file_name_arr[0]);
				$rec_set[$column_name] = $buf_old_upload_id.".".implode(".", $buf_physical_file_name_arr);
			}
		} else if(isset($this->transfer_id_arr[$transfer_column_name][$rec_set[$column_name]])) {
			$rec_set[$column_name] = $this->transfer_id_arr[$transfer_column_name][$rec_set[$column_name]];
		} else {
			$transfer_id = ($transfer_id == null) ? $this->_nextSeq($table_name, $column_name, $rec_set[$column_name]) : $transfer_id;
			// $transfer_id = ($transfer_id == null) ? $this->db->nextSeq($table_name) : $transfer_id;
			$this->transfer_id_arr[$transfer_column_name][$rec_set[$column_name]] = $transfer_id;
			$rec_set[$column_name] = $transfer_id;
		}
	}

	/**
	 * ID変換-変換ID保存処理(一般モジュール)
	 * @param string dirname
	 * @param string table_name
	 * @param array  column_records
	 * @param string 変換配列保存キー
	 * @param string 変換カラム名称
	 * @param int 変換ID 指定がなければ振りなおす（nextSeq）
	 * @access	private
	 */
	function _transferIdModules($dirname, &$transfer_id_arr, $table_name, &$rec_set, $transfer_column_name, $column_name = null, $transfer_id = null) {
		$column_name = ($column_name == null) ? $transfer_column_name : $column_name;
		if(isset($transfer_id_arr[$transfer_column_name][$rec_set[$column_name]])) {
			$rec_set[$column_name] = $transfer_id_arr[$transfer_column_name][$rec_set[$column_name]];
		} else {
			// $table_name以外でnextSeqを作成しているモジュールがあると、現在、未対応
			$transfer_id = ($transfer_id == null) ? $this->_nextSeq($table_name, $column_name, $rec_set[$column_name], $dirname, $transfer_column_name) : $transfer_id;
			//$transfer_id = ($transfer_id == null) ? $this->db->nextSeq($table_name) : $transfer_id;
			$transfer_id_arr[$transfer_column_name][$rec_set[$column_name]] = $transfer_id;
			$rec_set[$column_name] = $transfer_id;
		}
	}


	/**
	 * ハンドル名称変換-変換ハンドル名称保存処理(ハンドル名)
	 * @param array  振替配列
	 * @param array  column_records
	 * @access	private
	 */
	function _transferIdHandle(&$transfer_user_id_arr, &$rec_set) {

		if(isset($rec_set['insert_user_id']) && isset($rec_set['insert_user_name'])) {
			if(isset($transfer_user_id_arr[$rec_set['insert_user_id']])) {
				$rec_set['insert_user_name'] = $transfer_user_id_arr[$rec_set['insert_user_id']];
			} else {
				$user = $this->usersView->getUserById($rec_set['insert_user_id']);
				if(isset($user['user_id'])) {
					if($user['handle'] != $rec_set['insert_user_name']) {
						$rec_set['insert_user_name'] = $user['handle'];
					}
				} else {
					$rename_count = 1;
					$first_handle = $rec_set['insert_user_name'];
					while(1) {
						$handle = $rec_set['insert_user_name'];
						$where_params = array("handle" => $handle);
			 			$users = $this->usersView->getUsers($where_params);
			 			$count = count($users);
			 			if($count >= 1) {
			 				$rec_set['insert_user_name'] = $first_handle.sprintf("%03d",$rename_count);
			 			} else {
			 				break;
			 			}
			 			$rename_count++;
					}
				}
				$transfer_user_id_arr[$rec_set['insert_user_id']] = $rec_set['insert_user_name'];
			}
		}
	}

	/**
	 * IDの採番処理
	 *
	 * IDがDB上になければ、そのまま
	 * あれば、nextSeqにより採番
	 * @access	private
	 */
	function _nextSeq($table_name, $column_name, $column_value, $dirname="", $transfer_column_name="") {
		// 自サイトならば
		if($this->_self_flag) {
			$where_params = array($column_name => $column_value);
			$result = $this->db->selectExecute($table_name, $where_params);
			if($result !== false && !isset($result[0])) {
				// 同じキーのデータなし（振替えなし）
				return $column_value;
			}
		}
		if($dirname !== "") {
			// coreのテーブルではなければ、振替を行うキーが主キーにあたるかどうかをチェック
			// 主キーならば振替対象テーブルとする。そうでなければ振替キーを他テーブルから取得
			$pk_tableList = $this->backupRestore->getTablePrimaryList();
			if(isset($pk_tableList[$dirname][$table_name]) && is_array($pk_tableList[$dirname][$table_name]) && in_array($column_name, $pk_tableList[$dirname][$table_name])) {
				// 主キーである：未処理
			} else if(isset($pk_tableList[$dirname])) {
				foreach($pk_tableList[$dirname] as $sub_table_name => $pk_arr) {
					if(is_array($pk_tableList[$dirname][$sub_table_name]) && in_array($transfer_column_name, $pk_tableList[$dirname][$sub_table_name])) {
						$table_name = $sub_table_name;
						break;
					}
				}
			}
		}

		//$transfer_id = $this->db->nextSeq($table_name);
		return $this->db->nextSeq($table_name);
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_usersFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['user_id']] = $row;
		}
		return $ret;
	}

	/**
	 * テーブル削除処理
	 *
	 * @param  int $top_room_id
	 * @return boolean
	 * @access	private
	 */
	function _delRoomDb($top_room_id) {
		// pages,pages_modules_link,pages_users_link,monthly_number,uploads(room_id)
		// blocks, pages_style(page_id)
		$del_where_params = array(
			"(room_id = ".$top_room_id." OR root_id =".$top_room_id.")" => null
		);
		$del_pages = $this->db->selectExecute("pages", $del_where_params);
		if($del_pages === false || !isset($del_pages[0])) {
			// 現状、未処理
			return false;
		}
		$tableList = $this->backupRestore->getTableList();

		foreach($del_pages as $page) {
			// ルーム　and ページ
			//----------------------------------
			// pages
			//----------------------------------
			$table_name = "pages";
			if($page['thread_num'] == 0) {
				// パブリックスペース、プライベートスペースならば、
				// display_flagを_PAGES_DISPLAY_FLAG_DISABLEDにしておく
				$upd_params = array("display_flag" => _PAGES_DISPLAY_FLAG_DISABLED);
				$upd_where_params = array("page_id" => $page['page_id']);
				$result = $this->db->updateExecute($table_name, $upd_params, $upd_where_params, false);
				if ($result === false) {
					$this->_rollBack();
					return false;
				}
				$upd_params = array("display_flag" => _ON);
				$this->uploadParams[$table_name][] = $upd_params;
				$this->uploadWhereParams[$table_name][] = $upd_where_params;
			} else if($page['display_position'] != _DISPLAY_POSITION_CENTER){
				// 左右カラム、ヘッダー、フッターならば削除しない
				// 厳密にいえば、これらもdisplay_flagを_PAGES_DISPLAY_FLAG_DISABLEDにしておくほうがよいが
				// 行わない
			} else {
				$sel_where_params = array(
					"page_id" => $page['page_id']
				);
				if(!$this->_selectDelete($table_name, $sel_where_params)) {
					$this->_rollBack();
					return false;
				}
			}

			//----------------------------------
			// blocks
			//----------------------------------
			$table_name = "blocks";
			//if($page['thread_num'] != 0 && $page['space_type'] != _SPACE_TYPE_PUBLIC) {
				$sel_where_params = array(
					"page_id" => $page['page_id']
				);
				if(!$this->_selectDelete($table_name, $sel_where_params)) {
					$this->_rollBack();
					return false;
				}
			//}
			//----------------------------------
			// pages_style
			//----------------------------------
			$table_name = "pages_style";
			//if($page['thread_num'] != 0 && $page['space_type'] != _SPACE_TYPE_PUBLIC) {
				$sel_where_params = array(
					"set_page_id" => $page['page_id']
				);
				if(!$this->_selectDelete($table_name, $sel_where_params)) {
					$this->_rollBack();
					return false;
				}
			//}
			//----------------------------------
			// pages_meta_inf
			//----------------------------------
			$table_name = "pages_meta_inf";
			//if($page['thread_num'] != 0 && $page['space_type'] != _SPACE_TYPE_PUBLIC) {
				$sel_where_params = array(
					"page_id" => $page['page_id']
				);
				if(!$this->_selectDelete($table_name, $sel_where_params)) {
					$this->_rollBack();
					return false;
				}
			//}
			//----------------------------------
			// smarty_cache
			//----------------------------------
			$table_name = "smarty_cache";
			$sel_where_params = array(
				"page_id" => $page['page_id']
			);
			if(!$this->_selectDelete($table_name, $sel_where_params)) {
				$this->_rollBack();
				return false;
			}

			if($page['room_id'] == $page['page_id']) {
				// ルーム
				//----------------------------------
				// pages_modules_link
				//----------------------------------
				$table_name = "pages_modules_link";
				if($page['thread_num'] == 0) {
					// 削除しない
				} else {
					$sel_where_params = array(
						"room_id" => $page['page_id']
					);
					if(!$this->_selectDelete($table_name, $sel_where_params)) {
						$this->_rollBack();
						return false;
					}
				}
				//----------------------------------
				// pages_users_link
				//----------------------------------
				$table_name = "pages_users_link";
				if($page['thread_num'] == 0) {
					// 削除しない
				} else {
					$sel_where_params = array(
						"room_id" => $page['page_id']
					);
					if(!$this->_selectDelete($table_name, $sel_where_params)) {
						$this->_rollBack();
						return false;
					}
				}
				//----------------------------------
				// monthly_number
				//----------------------------------
				$table_name = "monthly_number";
				$sel_where_params = array(
					"room_id" => $page['page_id']
				);
				if(!$this->_selectDelete($table_name, $sel_where_params)) {
					$this->_rollBack();
					return false;
				}
				//----------------------------------
				// uploads
				//----------------------------------
				$table_name = "uploads";
				$sel_where_params = array(
					"room_id" => $page['page_id'],
					"file_path  != \"backup/\"" => null
				);
				if(!$this->_selectDelete($table_name, $sel_where_params)) {
					$this->_rollBack();
					return false;
				}
				// 一番最後に実ファイルも削除する

				//----------------------------------
				// 一般モジュール
				//----------------------------------
				foreach($tableList as $table_name => $dirname) {
					$sel_where_params = array(
						"room_id" => $page['page_id']
					);
					if(!$this->_selectDelete($table_name, $sel_where_params)) {
						$this->_rollBack();
						return false;
					}
				}
			}
		}
		return true;
	}
}
?>