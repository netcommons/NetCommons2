<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員削除(選択して削除)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Seldelete extends Action
{
	// リクエストパラメータを受け取るため
	var $select_user = null;
	var $delete_users = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $pagesView = null;
	var $usersAction = null;
	var $pagesAction = null;
	var $uploadsAction = null;
	var $db = null;
	var $blocksView = null;
	var $blocksAction = null;
	var $authoritiesView = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		// 検索結果から削除会員を特定
		$order_str = "";
		$session_params =& $this->session->getParameter(array("user", "selected_params"));
		//$select_str = "SELECT {users}.user_id ";
		$select_str = "SELECT {users}.*, ".
						"{authorities}.role_authority_id,".
						"{authorities}.role_authority_name,".
						"{authorities}.system_flag AS authority_system_flag,".
						"{authorities}.user_authority_id,".
						"{authorities}.public_createroom_flag,".
						"{authorities}.group_createroom_flag,".
						"{authorities}.private_createroom_flag,".
						"{authorities}.myroom_use_flag";

		$sql = $select_str.
				$this->session->getParameter(array("user", "selected_where_str")) . $order_str;
		$users =& $this->db->execute($sql, $session_params, null, null, true, array($this, "_fetchcallback"));
		if($users === false) {
			$this->db->addError();
			return 'error';
		}
		$del_users = array();
		$login_user_id = $this->session->getParameter("_user_id");
		$_system_user_id = $this->session->getParameter("_system_user_id");
		$_user_auth_id = $this->session->getParameter("_user_auth_id");
		$arr_count = 0;
		$count = 0;
		if($_user_auth_id == _AUTH_ADMIN) {
    		//
    		// 管理者ならば、システムコントロールモジュール、サイト運営モジュールの選択の有無で上下を判断
    		//
    		$_role_auth_id = $this->session->getParameter("_role_auth_id");
    		$func = array($this, "_getSysModulesFetchcallback");
    		$system_user_flag = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($_role_auth_id, array("system_flag"=>_ON), null, $func);
		} else {
    		$func = null;
    		$system_user_flag = null;
		}
		foreach($users as $user_id => $user) {
			if(!isset($del_users[$arr_count])) {
				$del_users[$arr_count] = array();
			}
			if($user_id == $login_user_id || $user_id == $_system_user_id) {
	    		// 自分自身 or システム管理者
	    		continue;
	    	}
	    	if($_user_auth_id != _AUTH_ADMIN && $user['user_authority_id'] >= $_user_auth_id) {
	    		// ログイン会員よりベース権限が未満のものしか削除できない
	    		continue;
	    	}

	    	if($system_user_flag === null) {
				$buf_system_user_flag = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($user['role_authority_id'], array("system_flag"=>_ON), null, $func);
				if($buf_system_user_flag === true) {
					continue;
				}
			}

			if(isset($this->delete_users[$user_id])) {
				if($this->delete_users[$user_id] == _ON) {
					// 選択されている
					// 削除対象
					$del_users[$arr_count][] = $user_id;
				} else {
					continue;
				}
			} else if($this->select_user == _ON){
				// 全選択中
				// 削除対象
				$del_users[$arr_count][] = $user_id;
			} else {
				continue;
			}
			// 50件単位でdeleteするため
			$count++;
			if($count == 50) {
				$count = 0;
				$arr_count++;
			}
		}

		$modules = $this->db->execute("SELECT {modules}.*,{authorities_modules_link}.role_authority_id FROM {modules},{authorities_modules_link} " .
									" WHERE {modules}.system_flag = 0 AND {modules}.module_id={authorities_modules_link}.module_id " .
									" ORDER BY {modules}.display_sequence",array(),null,null, true, array($this, "_fetchcallbackModulesByUsed"));
		if ($modules === false) {
	       	$this->db->addError();
	       	return 'error';
		}

		// ---------------------------------------------------------
		// 削除処理
		// ---------------------------------------------------------
		// 50件単位
		foreach($del_users as $del_users_arr) {
			if(count($del_users_arr) == 0) {
				continue;
			}
			$where_user_str = implode("','", $del_users_arr);
			$where_params = array(
				"user_id IN ('".$where_user_str. "') " => null
			);

			$params = array();
			$privatespace_pages = $this->db->execute("SELECT {pages}.page_id " .
										" FROM {pages_users_link}, {pages} " .
										" WHERE {pages}.thread_num = 0 AND {pages}.private_flag = "._ON.
										" AND {pages}.space_type = "._SPACE_TYPE_GROUP.
										" AND {pages}.insert_user_id IN ('".$where_user_str. "') " .
										" ",$params, null, null, true, array($this, "_pageFetchcallback"));
			if ($privatespace_pages === false) {
				$this->db->addError();
		       	return 'error';
			}

			$page_list =& $this->db->selectExecute("pages", array("room_id IN (".implode(",", $privatespace_pages). ") " => null) ,null, null, null, array($this, "_pageFetchcallback"));
	    	if($page_list === false) {
	    		return 'error';
	    	}
			// ----------------------------------------------------------------------
			// --- usersテーブルから削除                                         ---
			// ----------------------------------------------------------------------
			// ----------------------------------------------------------------------
			// --- users_items_linkテーブルから削除                              ---
			// ----------------------------------------------------------------------
			$result = $this->db->deleteExecute("users", $where_params);
			if ($result === false) {
				return 'error';
			}

			$result = $this->db->deleteExecute("users_items_link", $where_params);
			if ($result === false) {
				return 'error';
			}

			// ----------------------------------------------------------------------
			// --- pages_users_linkテーブルから削除                              ---
			// ----------------------------------------------------------------------
			$result = $this->pagesAction->delPageUsersLink($where_params);
			if($result === false) {
				return 'error';
			}

			// ----------------------------------------------------------------------
			// --- プライベートスペースの削除          　　　                    ---
			// ----------------------------------------------------------------------
			if(is_array($page_list)) {
				// ----------------------------------------------------------------------
				// --- ブロック削除アクション-削除アクション実行                     ---
				// ----------------------------------------------------------------------

				$blocks =& $this->blocksView->getBlockByPageId($page_list);
				if(is_array($blocks) && count($blocks) > 0) {
					foreach($blocks as $block) {
						//ブロック削除アクション
						$result = $this->blocksAction->delFuncExec($block['block_id'], $block);
					}
				}
				foreach($page_list as $current_page_id) {

					// ----------------------------------------------------------------------
					// --- pages_meta_inf テーブルから削除                                ---
					// ----------------------------------------------------------------------
					if(!$this->pagesAction->delPageMetaInfById($current_page_id)) {
						return 'error';
					}

					// ----------------------------------------------------------------------
					// --- 添付ファイル削除処理                                           ---
					// ----------------------------------------------------------------------
					$result = $this->uploadsAction->delUploadsByRoomid($current_page_id);
					if(is_array($modules) && count($modules) > 0 &&
						isset($modules[$users[$user_id]['role_authority_id']])) {
						foreach($modules[$users[$user_id]['role_authority_id']] as $module) {
							$delete_action = $module['delete_action'];
							// delete_action処理
							if($delete_action != "" && $delete_action != null) {
								$result = $this->pagesAction->delFuncExec($current_page_id, $module, $delete_action);
							}
						}
					}
				}

				/*
				foreach($page_list as $page_id) {
					//削除関数を呼び出し
					$blocks =& $this->blocksView->getBlockByPageId($page_id);
					if(isset($blocks[0])) {
						foreach($blocks as $block) {
							$this->blocksAction->delFuncExec($block['block_id']);
						}
					}
				}
				*/
				$delpage_params = array(
					"page_id IN (".implode(",", $page_list). ") " => null
				);
				$result = $this->db->deleteExecute("pages", $delpage_params);
				if ($result === false) {
					return 'error';
				}
				$result = $this->db->deleteExecute("blocks", $delpage_params);
				if ($result === false) {
					return 'error';
				}
			}

			// ----------------------------------------------------------------------
			// --- uploads テーブルから削除(アバター等)                          ---
			// ----------------------------------------------------------------------
			// 複数存在する場合もあるため、ガーベージフラグにより対応
			$params = array("garbage_flag" => _ON);
			$where_params = array(
								"action_name" => "common_download_user",
								"unique_id IN ('".$where_user_str. "') " => null,
								"garbage_flag" => _OFF
							);
			$result = $this->uploadsAction->updUploads($params, $where_params);
			if($result === false) {
				return 'error';
			}
		}

		return 'success';
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_fetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['user_id']] = $row;
		}
		return $ret;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_pageFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['page_id']] = $row['page_id'];
		}
		return $ret;
	}

    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return true or null
	 * @access	private
	 */
	function _getSysModulesFetchcallback($result) {
		$site_modules_dir_arr = explode("|", AUTHORITY_SYS_DEFAULT_MODULES_ADMIN);
		while ($obj = $result->fetchRow()) {
			if($obj["authority_id"] === null) continue;
			$module_id = $obj["module_id"];

			$pathList = explode("_", $obj["action_name"]);
			if(!in_array($pathList[0], $site_modules_dir_arr)) {
				return true;
			}
		}
		return null;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return true or null
	 * @access	private
	 */
	function _fetchcallbackModulesByUsed($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['role_authority_id']][$row['module_id']] = $row;
		}
		return $ret;
	}

}
?>
