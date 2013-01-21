<?php
/**
 * ページ登録用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	var $_container = null;

	var $modulesView = null;
	var $databaseSqlutility = null;
	var $preexecuteMain = null;

	// リクエストパラメータを受け取るため
	var $page_id = null;
	//var $update_count = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pages_Action() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * ページInsert
	 * @param array()
	 * @param boolean room_flag:ルームとして作成する場合、true
	 * @param boolean $footer_flag
	 * @return int new page_id
	 * @access	public
	 */
	function insPage($ins_params=array(), $room_flag=false, $footer_flag=true)
	{
		$page_id = $this->_db->nextSeq('pages');
		if($room_flag) {
			$params = array('page_id' => $page_id, 'room_id' => $page_id);
		} else {
			$params = array('page_id' => $page_id);
		}
		$params = array_merge($params, $ins_params);

		if (!isset($params['url'])) {
			$params['url'] = '';
		}
		if (!isset($params['permalink'])) {
			$params['permalink'] = '';
		}

		$result = $this->_db->insertExecute("pages", $params, $footer_flag);
		if ($result === false) {
			return false;
		}
		return $page_id;
	}

	/**
	 * ページユーザリンクInsert
	 * @param array()
	 * @return boolean
	 * @access	public
	 */
	function insPageUsersLink($params=array())
	{
		$result = $this->_db->insertExecute("pages_users_link", $params, true);
        if ($result === false) {
	       	return false;
		}
		return true;
	}

	/**
	 * ページユーザリンクUpdate
	 * @param array()
	 * @return boolean
	 * @access	public
	 */
	function updPageUsersLink($params=array(), $where_params=array())
	{
		$result = $this->_db->updateExecute("pages_users_link", $params, $where_params, true);
        if ($result === false) {
	       	return false;
		}
		return true;
	}

	/**
	 * ページユーザリンクDelete
	 * @param array()
	 * @return boolean
	 * @access	public
	 */
	function delPageUsersLink($where_params=array())
	{
		$result = $this->_db->deleteExecute("pages_users_link", $where_params);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * ページモジュールリンクInsert
	 * @param array()
	 * @return boolean
	 * @access	public
	 */
	function insPagesModulesLink($params=array())
	{
		$result = $this->_db->insertExecute("pages_modules_link", $params, true);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * ページモジュールリンクUpdate
	 * @param array()
	 * @return boolean
	 * @access	public
	 */
	function updPagesModulesLink($params=array(), $where_params=array())
	{
		$result = $this->_db->updateExecute("pages_modules_link", $params, $where_params, true);
        if ($result === false) {
	       	return false;
		}
		return true;
	}

	/**
	 * ページモジュールリンクDelete
	 * @param array()
	 * @return boolean
	 * @access	public
	 */
	function delPagesModulesLink($where_params=array())
	{
		$result = $this->_db->deleteExecute("pages_modules_link", $where_params);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * ページテーブルUpdate
	 *
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updPage($params=array(), $where_params=array())
	{
		$result = $this->_db->updateExecute("pages", $params, $where_params, true);
        if ($result === false) {
	       	return false;
		}
		return true;
	}

	/**
	 * 名称変更処理
	 * @param int page_id ,string block_name
	 * @return boolean true or false
	 * @access	public
	 */
	function updPagename($page_id,$block_name)
	{
		$params = array(
			"page_name " => $block_name,
			"page_id" => $page_id
		);
		$result = $this->_db->execute("UPDATE {pages} SET page_name =?" .
									" WHERE page_id=?", $params);
		if ($result === false) {
	       	 $this->_db->addError();
	       	return false;
		}
		return true;
	}

	/**
	 * ページスタイルテーブルInsert
	 * @param array()
	 * @return boolean
	 * @access	public
	 */
	function insPageStyle($params=array())
	{
		$result = $this->_db->insertExecute("pages_style", $params);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * ページスタイルテーブルUpdate
	 *
	 * @param   array   $params        パラメータ引数
	 * @param   array   $where_params  Whereパラメータ引数
	 * @return boolean true or false
	 * @access	public
	 */
	function updPageStyle($params=array(), $where_params=array())
	{
		$result = $this->_db->updateExecute("pages_style", $params, $where_params);
        if ($result === false) {
	       	return false;
		}
		return true;
	}

	/**
	 * PageIDよりpages_styleテーブル削除
	 * @param int page_id
	 * @return boolean true or false
	 * @access	public
	 */
	function delPageStyleById($page_id)
	{
		$params = array(
			"set_page_id" => $page_id
		);

		$result = $this->_db->deleteExecute("pages_style", $params);
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * PageIDよりpages_meta_infテーブル削除
	 * @param int page_id
	 * @return boolean true or false
	 * @access	public
	 */
	function delPageMetaInfById($page_id)
	{
		$params = array(
			"page_id" => $page_id
		);
		$result = $this->_db->deleteExecute("pages_meta_inf", $params);
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * カラム表示非表示変更処理
	 * @param array (page_id,header_flag,footer_flag,leftcolumn_flag,rightcolumn_flag)
	 * @return boolean true or false
	 * @access	public
	 */
	function updColumnFlag($params=array())
	{
		$footer_flag = false;
        if(!isset($params['update_time'])){
	        $footer_flag = true;
        }
		$result = $this->_db->updateExecute("pages", $params, array("page_id" => $params['page_id']), $footer_flag);
        if ($result === false) {
	       	return false;
		}
		return true;
	}

	/**
	 * UpdateCount++処理
	 * ブロック移動、ブロック追加、ブロック削除、グループ化、グループ化解除、ペースト(カット)、ショートカット作成
	 * 時にカウントアップ。
	 * @param int page_id
	 * @return boolean true or false
	 * @access	public
	 */
	function updShowCount($page_id)
	{
		$params = array(
			"page_id" => $page_id
		);

		$result = $this->_db->execute("UPDATE {pages} SET show_count=show_count + 1" .
									" WHERE page_id=?",$params);
		if ($result === false) {
	       	 $this->_db->addError();
	       	return false;
		}
		return true;
	}


	/**
	 * PageIDより削除
	 * @param int page_id
	 * @param string tableName
	 * @return boolean true or false
	 * @access	public
	 */
	function delPageById($page_id, $tableName="pages")
	{
		$params = array(
			"page_id" => $page_id
		);
		$result = $this->_db->deleteExecute($tableName, $params);
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * 表示順前詰め処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function decrementDisplaySeq($parent_id = null, $current_display_sequence = 1, $lang_dirname="", $display_sequence = 1) {
	 	$display_sequence = -1*$display_sequence;
	 	return $this->_operationDisplaySeq($parent_id, $current_display_sequence, $lang_dirname, $display_sequence);
	}
	function incrementDisplaySeq($parent_id = null, $current_display_sequence = 1, $lang_dirname="", $display_sequence = 1) {
	 	return $this->_operationDisplaySeq($parent_id, $current_display_sequence, $lang_dirname, $display_sequence);
	}
	function _operationDisplaySeq($parent_id = null, $current_display_sequence = 1, $lang_dirname="", $display_sequence = 1) {
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");

		$params = array(
				"update_time" =>$time,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name,
				"parent_id" => $parent_id,
				"lang_dirname" => $lang_dirname,
				"display_sequence" => $current_display_sequence
		);
		$result = $this->_db->execute("UPDATE {pages} SET display_sequence=display_sequence  + (". $display_sequence ."),update_time=?, update_user_id=?,update_user_name=?" .
									" WHERE parent_id=?" .
									" AND lang_dirname=?" .
									" AND display_sequence>=?",$params);
		if ($result === false) {
	       	 $this->_db->addError();
	       	return false;
		}
		return true;
	 }

	/**
	 * 表示順変更処理
	 * @param int page_id
	 * @param int parent_id
     * @param int thread_num
     * @param int display_sequence
	 * @return boolean true or false
	 * @access	public
	 */
	function updDisplaySequence($page_id, $parent_id, $thread_num, $display_sequence)
	{
		$params = array(
			"parent_id " => $parent_id,
			"thread_num " => $thread_num,
			"display_sequence " => $display_sequence,
			"page_id" => $page_id
		);
		$result = $this->_db->execute("UPDATE {pages} SET parent_id = ?, thread_num = ?, display_sequence = ?" .
									" WHERE page_id=?",$params);
		if ($result === false) {
	       	 $this->_db->addError();
	       	return false;
		}
		return true;
	}

	/**
	 * 表示順変更処理(プライベートスペース一斉更新)
	 * @param int display_sequence
	 * @return boolean true or false
	 * @access	public
	 */
	function updPrivateDisplaySeq($display_sequence)
	{
		$params = array(
			"display_sequence " => $display_sequence
		);
		$result = $this->_db->execute("UPDATE {pages} SET display_sequence = ?" .
									" WHERE thread_num=0 AND space_type="._SPACE_TYPE_GROUP." AND private_flag="._ON,$params);
		if ($result === false) {
	       	 $this->_db->addError();
	       	return false;
		}
		return true;
	}

	/**
	 * thread_num更新処理
	 * @param int parent_id
     * @param int thread_num
	 * @return boolean true or false
	 * @access	public
	 */
	function updThreadNum($parent_id, $thread_num)
	{
		$params = array(
			"thread_num " => $thread_num,
			"parent_id" => $parent_id
		);
		$result = $this->_db->execute("UPDATE {pages} SET thread_num = ?" .
									" WHERE parent_id=?",$params);
		if ($result === false) {
	       	 $this->_db->addError();
	       	return false;
		}
		return true;
	}


	/**
	 * 削除関数呼び出し
	 * @param  int     $room_id		削除対象room_id
	 * @param  array   $modules		削除対象モジュール配列
	 * @param  string  delete_action
	 * @return boolean true or false
	 * @access	public
	 */
	function delFuncExec($room_id, &$module, $delete_action = null)
	{
		if($delete_action == "auto") {
			$action_name = $module['action_name'];
			$pathList = explode("_", $action_name);
			$dirname = $pathList[0];
			//テーブルリスト取得
			$table_list = $this->databaseSqlutility->getTableList($dirname);
			if(!is_array($table_list)) {
				// 配列でなければエラーとする
				return false;
			}

			//自動的に削除
			$where_params = array(
				"room_id" => $room_id
			);
			$error = false;

			foreach ($table_list as $table_name) {
				//ルームIDより削除処理。ルームIDがなければ、処理しない
				$result = $this->_db->execute("DESCRIBE ".$table_name." room_id");
				if(is_array($result) && isset($result[0])) {
					$result = $this->_db->execute("DELETE FROM ".$table_name." WHERE room_id=?" .
										" ",$where_params);
					if(!$result) {
						$error = true;
					}
				}
			}

			if($error) return false;
		} elseif($delete_action == "" || $delete_action === null) {
			//なにもしない
		} else {
			//
			//削除関数呼び出し
			//
			$pathList   = explode("_", $delete_action);
	        $ucPathList = array_map('ucfirst', $pathList);

	        $basename = ucfirst($pathList[count($pathList) - 1]);

	        $actionPath = join("/", $pathList);
	        $className  = join("_", $ucPathList);
	        $filename   = MODULE_DIR . "/${actionPath}/${basename}.class.php";
    		if (@file_exists($filename)) {
    			$pagesView =& $this->_container->getComponent("pagesView");
    			$page =& $pagesView->getPageById($room_id);
				if($page === false || !isset($page['page_id'])) {
					return false;
				}

				//ブロック削除アクション
				$params = array(
					'action' => $delete_action,
					'mode'   => "delete",
		    		'page_id'=> $page['page_id'],
		    		'room_id'=> $room_id
		    	);
				$result = $this->preexecuteMain->preExecute($delete_action, $params);
				if($result === false || $result === "false") {
					return false;
				}
    		}
		}
		return true;
	}

	/**
	 * パーマリンク更新処理
	 * @param  array   $page
	 * @param  string  $upd_permalink
	 * @param  array   $parent_page (１つ上の親、親をセットした場合、親の固定リンクより子の固定リンクを設定)
	 * @param  private $ref_flag
	 * @return boolean
	 * @access	public
	 */
	function updPermaLink($page, $upd_permalink, $parent_page=null, $ref_flag = false)
	{
		$permalink = $upd_permalink;
		if(isset($upd_permalink) && ($upd_permalink != "" ||
			($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['thread_num'] == 1 && $page['display_sequence'] == 1))) {
			$replace_permalink = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $upd_permalink);

			if(isset($parent_page)) {
				$permalink = $parent_page['permalink'];
				/*
				if($ref_flag == true) {
					$permalink_arr = explode('/', $permalink);
					$page_permalink_arr = explode('/', $page['permalink']);
					$count = 0;
					foreach($page_permalink_arr as $page_permalink) {
						if(isset($permalink_arr[$count])) {
							$page_permalink_arr[$count] = $permalink_arr[$count];
						}
						$count++;
					}
					unset($page_permalink_arr[count($page_permalink_arr)-1]);
					$permalink = implode('/', $page_permalink_arr);
				}
				*/
				if(($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['thread_num'] == 0) ||
					($page['private_flag'] == _OFF && $page['space_type'] == _SPACE_TYPE_GROUP && $page['thread_num'] == 0)) {
					$replace_permalink = "";
				} else {
					if($permalink == "") {
						$permalink .= $replace_permalink;
					} else {
						$permalink .= '/' . $replace_permalink;
					}
				}
			} else {
				$permalink_arr = explode('/', $page['permalink']);
				$current_permalink = $permalink_arr[count($permalink_arr)-1];
				$permalink_arr[count($permalink_arr)-1] = $replace_permalink;
				$permalink = implode('/', $permalink_arr);
			}
			// 同じ階層に同じ言語と名称の固定リンクがあればリネーム
			if($permalink !== "") {
				$permalink = $this->getRenamePermaLink($permalink, $page['page_id'], $page['lang_dirname']);
			}

			if($permalink == $page['permalink']) {
				// 固定リンク変更なし
				return $permalink;
			}

			$update_params = array(
				"permalink" => $permalink
			);
			$result = $this->_db->updateExecute("pages", $update_params, array("page_id" => $page['page_id']));
			if ($result === false) return false;

	    	//
	    	// 子のページのpermalinkも更新をかける
	    	// サブグループならば、必ず再帰的に実行
	    	//
	    	if($ref_flag == false || $page['page_id'] == $page['room_id']) {
	    		$child_pages = $this->_getChildPages($page);
	    		if($child_pages === false) return false;

	    		if(count($child_pages) > 0) {
	    			$page['permalink'] = $permalink;
	    			foreach($child_pages as $key => $child_page) {
	    				$permalink_arr = explode('/', $child_page['permalink']);
						$upd_permalink = $permalink_arr[count($permalink_arr)-1];
						$set_page = isset($child_pages[$child_page['parent_id']]) ? $child_pages[$child_page['parent_id']] : $page;
	    				$child_pages[$key]['permalink'] = $this->updPermaLink($child_page, $upd_permalink, $set_page, true);
	    			}
	    		}
	    	}
		}
    	return $permalink;
	}

	/**
     * 固定リンクで既に同じものが存在していればリネーム
     * @param string $permalink
     * @param int $page_id : 現在のpage_id 現在見ているページ以外で
     * 						　同じ名称の固定リンクがあるかどうかを検索
     * @param string $lang_dirname
     * @access  private
     */
	function getRenamePermaLink($permalink, $page_id = 0, $lang_dirname = null) {
		$count = 1;
		$old_permalink = $permalink;
		$permalink_arr = explode('/', $permalink);
		if(is_array($permalink_arr) && preg_match(_PERMALINK_PROHIBITION_DIR_PATTERN, $permalink_arr[count($permalink_arr) - 1])) {
			$permalink = $permalink ."-". $count;
			$count++;
		}

		while(1) {
			$where_param = array(
				"page_id!" => $page_id,
				"permalink"=> $permalink
			);
			if(!empty($lang_dirname)) {
				$where_param["lang_dirname"] = $lang_dirname;
			}
			$same_pages = $this->_db->selectExecute("pages", $where_param, null, 1);
			if(isset($same_pages[0])) {
				$permalink = $old_permalink ."-". $count;
			} else {
				$permalink_arr = explode("-", $permalink);
				if(empty($permalink_arr[1]) || !empty($permalink_arr[2]) || !is_numeric($permalink_arr[1]))
					break;
				$where_param = array(
					"short_url"=> $permalink_arr[0]
				);
				$same_pages = $this->_db->selectExecute("abbreviate_url", $where_param, null, 1);
				if(isset($same_pages[0])) {
					$permalink = $permalink ."-". $count;
				} else {
					break;
				}
			}
			$count++;
		}
		return $permalink;
	}

     /**
     * 下の階層のpageのリストを求める
     * @param array $page
     * @access  private
     */
    function _getChildPages($page) {
    	$where_params = array("room_id = ".intval($page['room_id'])." OR parent_id = ".intval($page['room_id']) => null);
		$order_params = array("thread_num" => "ASC");

		$sql = "SELECT {pages}.* FROM {pages} ";
		$params = array();
		$sql .= $this->_db->getWhereSQL($params, $where_params);

		$sql .= $this->_db->getOrderSQL($order_params);

		$parent_pages = $this->_db->execute($sql, $params, null, null, true, array($this,"_fetchcallbackGetChildPages"), $page);

    	return $parent_pages;
    }

    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @param array $page
	 * @return array
	 * @access	private
	 */
	function &_fetchcallbackGetChildPages($result, $page) {
		$ret = array();
		$parent_id_arr[] = $page['page_id'];
		while ($row = $result->fetchRow()) {
			if(in_array($row['parent_id'], $parent_id_arr)) {
				$ret[$row['page_id']] = $row;
				$parent_id_arr[] = $row['page_id'];
			}
		}
		return $ret;
	}
}
?>
