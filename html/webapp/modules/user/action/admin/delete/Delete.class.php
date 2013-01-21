<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員削除
 * 自分自身の削除-システム管理者の削除は不可（バリデート）
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $user_id = null;

	// 使用コンポーネントを受け取るため
	var $pagesView = null;
	var $usersAction = null;
	var $pagesAction = null;
	var $uploadsAction = null;
	var $db = null;
	var $blocksView = null;
	var $blocksAction = null;
	var $modulesView = null;
	var $usersView = null;
	var $actionChain = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$user_id = $this->user_id;

		// ルームに所属しているページ一覧取得
		$params = array(
			"insert_user_id" => $user_id
		);

		$privatespace_pages = $this->db->execute("SELECT {pages}.* " .
									" FROM {pages} " .
									" WHERE {pages}.thread_num = 0 AND {pages}.private_flag = "._ON.
									" AND {pages}.space_type = "._SPACE_TYPE_GROUP.
									" AND {pages}.insert_user_id = ? ".
									" ORDER BY default_entry_flag DESC",$params);

		if ($privatespace_pages === false || !isset($privatespace_pages[0])) {
			$this->db->addError();
	       	return 'error';
		}
		if(count($privatespace_pages) > 1) {
			$where_page_list = array("(room_id = ".$privatespace_pages[0]['room_id']." OR room_id=".$privatespace_pages[1]['room_id'].")" => null);
		} else {
			$where_page_list = array("room_id" => $privatespace_pages[0]['room_id']);
		}
		$page_list =& $this->db->selectExecute("pages", $where_page_list ,null, null, null, array($this, "_fetchcallback"));
    	if($page_list === false) {
    		return 'error';
    	}
    	$users = $this->usersView->getUsers(array("user_id"=>$user_id));
    	if($users === false || !isset($users[0])) return 'error';

		// ----------------------------------------------------------------------
		// --- usersテーブルから削除                                         ---
		// ----------------------------------------------------------------------
		// ----------------------------------------------------------------------
		// --- users_items_linkテーブルから削除                              ---
		// ----------------------------------------------------------------------
		$result = $this->usersAction->delUserById($user_id);
		if($result === false) return 'error';

		// ----------------------------------------------------------------------
		// --- pages_users_linkテーブルから削除                              ---
		// ----------------------------------------------------------------------
		$where_params = array("user_id" => $user_id);
		$result = $this->pagesAction->delPageUsersLink($where_params);
		if($result === false) return 'error';

		// ----------------------------------------------------------------------
		// --- ブロック削除アクション-削除アクション実行                     ---
		// ----------------------------------------------------------------------
		$blocks =& $this->blocksView->getBlockByPageId($page_list);
		$modules =& $this->modulesView->getAuthoritiesModulesByUsed($users[0]['role_authority_id'],0,0,true);

		if(is_array($blocks) && count($blocks) > 0) {
			foreach($blocks as $block) {
				if(!isset($modules[$block['module_id']])) {
					$get_modules= $this->modulesView->getModules(array("module_id" => $block['module_id']));
					$modules[$block['module_id']] = $get_modules[0];
				}
				$block_delete_action = $modules[$block['module_id']]['block_delete_action'];
				if($block_delete_action != "" && $block_delete_action != null) {
					//ブロック削除アクション
					$result = $this->blocksAction->delFuncExec($block['block_id'], $block, $block_delete_action);
				}
			}
		}
		foreach($privatespace_pages as $privatespace_page) {
			$current_page_id = $privatespace_page['room_id'];

			// ----------------------------------------------------------------------
			// --- 添付ファイル削除処理                                           ---
			// ----------------------------------------------------------------------
			$result = $this->uploadsAction->delUploadsByRoomid($current_page_id);
			if($result === false) return 'error';

			if(is_array($modules) && count($modules) > 0) {
				foreach($modules as $module) {
					$delete_action = $module['delete_action'];
					// delete_action処理
					if($delete_action != "" && $delete_action != null) {
						$result = $this->pagesAction->delFuncExec($current_page_id, $module, $delete_action);
					}
				}
			}
		}

		// ----------------------------------------------------------------------
		// --- プライベートスペースの削除          　　　                    ---
		// ----------------------------------------------------------------------
		foreach($page_list as $page_id) {
			//削除関数を呼び出し
			$blocks =& $this->blocksView->getBlockByPageId($page_id);
			if(isset($blocks[0])) {
				foreach($blocks as $block) {
					$this->blocksAction->delFuncExec($block['block_id']);
				}
			}
			// ----------------------------------------------------------------------
			// --- ブロックテーブル削除                                           ---
			// ----------------------------------------------------------------------
	    	$result = $this->pagesAction->delPageById($page_id, "blocks");
			if(!$result) {
				return 'error';
			}

	    	// ----------------------------------------------------------------------
			// --- ページテーブル削除                                             ---
			// ----------------------------------------------------------------------
	    	$result = $this->pagesAction->delPageById($page_id);
			if(!$result) {
				return 'error';
			}

			// ----------------------------------------------------------------------
			// --- pages_meta_inf テーブルから削除                               ---
			// ----------------------------------------------------------------------
			if(!$this->pagesAction->delPageMetaInfById($page_id)) {
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
							"unique_id " => $user_id,
							"garbage_flag" => _OFF
						);
		$result = $this->uploadsAction->updUploads($params, $where_params);
		if($result === false) return 'error';

		$recursive_action_name = $this->actionChain->getRecursive();
		if($recursive_action_name != "") {
			return 'recursive_success';
		}

		return 'success';
	}


    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[] = $row['page_id'];
		}
		return $ret;
	}
}
?>
