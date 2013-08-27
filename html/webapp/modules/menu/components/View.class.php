<?php
/**
 * メニューテーブル表示用クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Components_View {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	var $_container = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Menu_Components_View() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 表示可能なページデータ取得
	 * ブロックIDを指定する場合、メニューデータの取得
	 * @param int      id(block_id),
	 * @param int      page_id
	 * @param int      parent_id
	 * @param int      room_id
	 * @param int      thread_num
	 * @param function func
	 * @param array    func_param
	 * @return array pages_array
	 * @access	public
	 */
	function &getShowPageById($id=0, $page_id = 0, $root_id = 0, $parent_id = 0, $room_id = 0, $thread_num = null, $func = null, $func_param = null)
	{
		$session =& $this->_container->getComponent("Session");
		$getdata =& $this->_container->getComponent("GetData");
		$_user_id = $session->getParameter("_user_id");
		$_user_auth_id = $session->getParameter("_user_auth_id");
		//$root_id = ($root_id == 0) ? $page_id : $root_id;
		$blocks =& $getdata->getParameter("blocks");
		$mode = isset($func_param[8]) ? $func_param[8] : "";
		$temp_name = $blocks[$id]['temp_name'];
		$func_param[count($func_param)] = $temp_name;

		$_insert_user_id = $this->_getUserIdByOpenPrivateSpace($_user_id);

		if($_user_id == "0") {
			//
			//ログイン前
			//
			if($id == 0) {
				$sql = "SELECT {pages}.* ,"._AUTH_GUEST." as authority_id, 0 as createroom_flag " .
					" FROM {pages} ";
			} else {
				$sql = "SELECT {pages}.* ,"._AUTH_GUEST." as authority_id, 0 as createroom_flag, {menu_detail}.visibility_flag " .
					" FROM {pages} LEFT JOIN {menu_detail} ON ({pages}.page_id = {menu_detail}.page_id OR ({pages}.private_flag = "._ON." AND {menu_detail}.page_id = -1)) AND {menu_detail}.block_id = ? ";
				////$sql .= " AND {menu_detail}.visibility_flag = "._ON;
			}
			if($id == 0) {
				$params = array();
			} else {
				$params = array(
					"block_id" => $id
				);
			}
			$sql .= " WHERE {pages}.space_type = ". _SPACE_TYPE_PUBLIC .
							" AND {pages}.private_flag = " . _OFF .
							" AND {pages}.display_flag = " . _ON .
							" ";
		} else {
			//
			//ログイン後
			//
			if($id == 0) {
				$sql = "SELECT {pages}.* ,{authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.createroom_flag " .
					" FROM {pages} ";
			} else {
				$sql = "SELECT {pages}.* ,{authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.createroom_flag, {menu_detail}.visibility_flag " .
					" FROM {pages} LEFT JOIN {menu_detail} ON ({pages}.page_id = {menu_detail}.page_id OR ({pages}.private_flag = "._ON." AND {menu_detail}.page_id = -1)) AND {menu_detail}.block_id = ? ";
				////$sql .= " AND {menu_detail}.visibility_flag = "._ON;
			}
			if($id == 0) {
				$params = array(
					"user_id" => $_user_id,
					"insert_user_id" => $_insert_user_id
				);
			} else {
				$params = array(
					"block_id" => $id,
					"user_id" => $_user_id,
					"insert_user_id" => $_insert_user_id
				);
			}
			$sql .= " LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = ? ".
					" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id ".
					" WHERE 1=1 ";
			//それ以外
			$pagesView =& $this->_container->getComponent('pagesView');
			$sql .= " AND (".
						"({pages}.space_type ="._SPACE_TYPE_PUBLIC." AND {pages}.private_flag ="._OFF.") OR ".
						"({pages}.space_type ="._SPACE_TYPE_GROUP." AND {pages}.private_flag ="._ON.
						" AND {pages}.insert_user_id = ?) OR ".
						$pagesView->getGroupWhereStatement() .
					")".
					" AND {pages}.display_flag != ". _PAGES_DISPLAY_FLAG_DISABLED ." ";
		}

		if(preg_match("/(flat|header|pldwn)/i", $temp_name) && $mode == "init") {
			$sql .= " AND (({pages}.space_type = "._SPACE_TYPE_GROUP." AND {pages}.thread_num < 2) OR {pages}.space_type = "._SPACE_TYPE_PUBLIC." OR {pages}.parent_id = ". $page_id .  " OR {pages}.parent_id = ". $parent_id . " OR {pages}.room_id = ". $room_id . ") ";
		} else if($thread_num == null) {
			$sql .= " AND ({pages}.thread_num < 2  OR {pages}.parent_id = ". $page_id .  " OR {pages}.parent_id = ". $parent_id . " OR {pages}.room_id = ". $room_id . ") ";
		} else {
			$sql .= " AND ({pages}.thread_num = ".$thread_num." OR {pages}.parent_id = ". $page_id . ") ";
		}
		$sql .= " ORDER BY {pages}.thread_num,{pages}.display_sequence";
		$result = $this->_db->execute($sql,$params,null,null,true,$func,$func_param);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * _open_private_spaceがonならば、他人のプライベートスペースを見ている場合、他人の会員IDを返す（他人のプライベートスペースのリストをメニューに表示するため）
	 * @param  string $current_user_id
	 * @return string $user_id
	 * @access	private
	 */
	function _getUserIdByOpenPrivateSpace($current_user_id) {
		$session =& $this->_container->getComponent("Session");
		$getdata =& $this->_container->getComponent("GetData");
		$pages =& $getdata->getParameter("pages");
		$_open_private_space = $session->getParameter("_open_private_space");

		if($_open_private_space == _OFF ||
			!isset($pages[$session->getParameter("_main_page_id")]) || $pages[$session->getParameter("_main_page_id")]['private_flag'] == _OFF) {
			return $current_user_id;
		}

		if(!isset($pages[$pages[$session->getParameter("_main_page_id")]['room_id']])) {
			$pagesView =& $this->_container->getComponent("pagesView");
			$pages[$pages[$session->getParameter("_main_page_id")]['room_id']] = $pagesView->getPageById($pages[$session->getParameter("_main_page_id")]['room_id']);
		}

		if($pages[$session->getParameter("_main_room_id")]['default_entry_flag'] == _ON) {
			// マイポータル
			return $pages[$pages[$session->getParameter("_main_room_id")]['room_id']]['insert_user_id'];
		} else {
			// マイルーム
			if(($current_user_id == "0" &&
				($_open_private_space == _OPEN_PRIVATE_SPACE_PUBLIC || $_open_private_space == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC)) ||
				($current_user_id != "0" &&
				($_open_private_space == _OPEN_PRIVATE_SPACE_GROUP || $_open_private_space == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP))) {
				return $pages[$pages[$session->getParameter("_main_room_id")]['room_id']]['insert_user_id'];
			}
		}
		return $current_user_id;
	}


	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $menus_obj
	 * @access	private
	 */
	function &fetchcallback($result, $fun_param) {
		$main_page_id = $fun_param[0];
		$main_root_id = $fun_param[1];
		$main_parent_id = $fun_param[2];
		$main_room_id = $fun_param[3];
		$main_space_type = $fun_param[4];
		$main_active_node_arr =& $fun_param[5];
		$top_page_arr =& $fun_param[6];
		$edit_flag =& $fun_param[7];
		$mode =& $fun_param[8];
		$temp_name =& $fun_param[9];
		if(preg_match("/(flat|header)/i",$temp_name)) {
			$flat_flag = true;
		} else {
			$flat_flag = false;
		}
		$container =& DIContainerFactory::getContainer();
    	$pagesView =& $container->getComponent("pagesView");
    	$session =& $container->getComponent("Session");


		$main_root_id = ($main_root_id == 0) ? $main_page_id : $main_root_id;

		//$grouproom_flag = false;
		//if($main_space_type == _SPACE_TYPE_GROUP) {
		//	//グループルーム
		//	$grouproom_flag = true;
		//}

		//$bug_row_flag = true;
		$visibility_hide_row = array();
		$buf_row = array();
		$ret = array();
		$count = 0;
		while ($row = $result->fetchRow()) {
			//if($bug_row_flag == true) {
			//	$buf_row[$count][0] = $row['page_id'];
			//	$buf_row[$count][1] = $row['parent_id'];
			//	$count++;
			//}

			//言語の切替
			if(!empty($row['lang_dirname']) && $row['lang_dirname'] != $session->getParameter('_lang')) {
				continue;
			}
			if($row['visibility_flag'] == null) {
				if(($flat_flag == false && in_array($row['parent_id'], $visibility_hide_row)) ||
				    ($flat_flag == true && $row['space_type'] != _SPACE_TYPE_PUBLIC && in_array($row['parent_id'], $visibility_hide_row)) ||
					($flat_flag == true && $row['root_id'] && in_array($row['root_id'], $visibility_hide_row))) {
					$row['visibility_flag'] = _OFF;
					array_push($visibility_hide_row,$row['page_id']);
				} else {
					$row['visibility_flag'] = _ON;
				}
			} else if($row['visibility_flag'] == _OFF) {
				array_push($visibility_hide_row,$row['page_id']);
			}
			if($row["authority_id"] >= _AUTH_CHIEF && $main_room_id == $row["page_id"]) {
				$edit_flag = true;
			}
			$row['visible_flag'] = true;
			$row['edit_flag'] = true;
			$row['chgseq_flag'] = true;
			if($row['thread_num'] == 0 && $row['private_flag'] == _ON) {
				// プライベートスペースならば、主担以上ならばOK
				if($row['authority_id'] < _AUTH_CHIEF) {
					$row['edit_flag'] = false;
				}
				// 管理者のみ変更を許す
				if($session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
					$row['chgseq_flag'] = false;
				}
			} else if($row['thread_num'] == 0) {
				// 深さ０ならば、管理者のみ変更を許す
				$row['default_entry_flag'] = _OFF;
				if($session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
					$row['chgseq_flag'] = false;
					$row['edit_flag'] = false;
				}
			} else if($row['thread_num'] == 1 && $row['space_type'] == _SPACE_TYPE_GROUP &&  $row['private_flag'] == _OFF) {
				// グループルーム
				// 管理者のみ変更を許す
				if($session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
					$row['chgseq_flag'] = false;
				}
				if($row['authority_id'] < _AUTH_CHIEF) {
					$row['edit_flag'] = false;
				}
			} else if($row['page_id'] == $row['room_id']) {
				// その他のルーム
				// 親のルームの権限が主担ならば許す
				if(isset($pre_buf_row[$row['parent_id']])) {
					$parent_page =& $pre_buf_row[$row['parent_id']];
				} else {
					$parent_page = $pagesView->getPageById($row['parent_id']);
				}

				if($parent_page === false || !isset($parent_page['authority_id']) || $parent_page['authority_id'] < _AUTH_CHIEF) {
					$row['chgseq_flag'] = false;
				}
				if($row['authority_id'] < _AUTH_CHIEF) {
					$row['edit_flag'] = false;
				}
			} else {
				// ページ-カテゴリ
				if(isset($pre_buf_row[$row['room_id']])) {
					$parent_page =& $pre_buf_row[$row['room_id']];
				} else {
					$parent_page = $pagesView->getPageById($row['room_id']);
				}

				if($parent_page === false || !isset($parent_page['authority_id']) || $parent_page['authority_id'] < _AUTH_CHIEF) {
					$row['chgseq_flag'] = false;
					$row['edit_flag'] = false;
				}
			}
			if($session->getParameter("_auth_id") < _AUTH_CHIEF && ($row['private_flag'] == _ON || $row['authority_id'] < _AUTH_CHIEF)) {
				// プライベートスペースで、本ブロックの権限が主担でないなら変更不可。
				$row['visible_flag'] = false;
			}
			$pre_buf_row[$row['page_id']] = $row;

			$ret[$row['thread_num']][$row['parent_id']][$row['display_sequence']] = $row;
			if(($row['private_flag'] == _OFF && ($main_parent_id == $row['page_id'] OR $row['thread_num'] == 0))
				) {
				$main_active_node_arr[$row['page_id']] = _ON;
			} else if($row['space_type'] == _SPACE_TYPE_PUBLIC && $row['thread_num'] == 0) {
				$main_active_node_arr[$row['page_id']] = _ON;
			} else {
				$main_active_node_arr[$row['page_id']] = _OFF;
			}
			if($main_root_id == $row['root_id'] || $main_root_id == $row['page_id']) {
				$buf_row[$count][0] = $row['page_id'];
				$buf_row[$count][1] = $row['parent_id'];
				$count++;
			}
			if(count($fun_param) >=6 && $row['thread_num'] == 1 && $row['display_sequence'] == 1 && $row['space_type'] == _SPACE_TYPE_PUBLIC) {
				//トップページ
				$top_page_arr = $row;
			}
		}
		//activeNodeにいたるツリーノードをすべて表示状態へ
		for($i = count($buf_row) - 1; $i >= 0; $i--) {
			if($main_page_id == $buf_row[$i][1]) {
				$main_active_node_arr[$buf_row[$i][1]] = _ON;
			} else if($main_parent_id == $buf_row[$i][0]) {
				$main_parent_id = $buf_row[$i][1];
				$main_active_node_arr[$buf_row[$i][0]] = _ON;
			}
		}

		return $ret;
	}

	/**
	 * menu_detailリストを取得する
	 *
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @param   array   $func          関数
	 * @param   array   $func_params   Funcパラメータ引数
	 * @return array
	 * @access	public
	 */
	function &getMenuDetail($where_params=null, $order_params=null, $func=null, $func_param=null)
	{
		//if (!isset($order_params)) {
        //	$order_params = array("{menu_detail}.page_id"=>"ASC");
        //}

		$db_params = array();
		$sql = $this->_db->getSelectSQL("menu_detail", $db_params, $where_params, $order_params);
		$result = $this->_db->execute($sql, $db_params, null, null, true, $func, $func_param);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
}
?>
