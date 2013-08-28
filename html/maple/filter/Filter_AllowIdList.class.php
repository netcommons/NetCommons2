<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示可能なID(room_id,page_id,block_id)のリストを取得するFilter
 * TARGET_USER_ID or TARGET_LOGIN_ID or TARGET_MODULE_ID or TARGET_ROOM_ID
 * セットパラメータ名称 = "ALLOW_PAGE_ID" or "ALLOW_ROOM_ID" or "ALLOW_BLOCK_ID"
 * or "ALLOW_BLOCK_ARR" or "ALLOW_PAGE_ARR"($pages[$thread_num][$parent_id][$display_sequence])
 * or "ALLOW_ROOM_ARR"($pages[$thread_num][$parent_id][$display_sequence])
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Filter_AllowIdList extends Filter
{
	var $_classname = "Filter_AllowIdList";

	var $_container;
    var $_log;
    var $_filterChain;
    var $_pagesView;
    var $_blocksView;
    var $_usersView;
	var $_session;
    var $_request;
    var $_db = null;

	var $target_room_id = null;
	var $target_module_id = null;
	var $target_user_id = null;

	var $entry_user_flag = false;
    var $current_room_flag = false;

	 /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_AllowIdList()
    {
        parent::Filter();
    }

    /**
     * 表示可能なID取得処理を実行
     *
     * @access  public
     */
    function execute()
    {
        $this->_container   =& DIContainerFactory::getContainer();
        $this->_log         =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_pagesView   =& $this->_container->getComponent("pagesView");
        $this->_blocksView  =& $this->_container->getComponent("blocksView");
        $this->_usersView   =& $this->_container->getComponent("usersView");
        $this->_session     =& $this->_container->getComponent("Session");
        $this->_request     =& $this->_container->getComponent("Request");
        $this->_db          =& $this->_container->getComponent("DbObject");

        $this->_log->trace("{$this->_classname}の前処理が実行されました", "{$this->_classname}#execute");
        $this->_preFilter();

        $this->_filterChain->execute();

        $this->_log->trace("{$this->_classname}の後処理が実行されました", "{$this->_classname}#execute");
        $this->_postFilter();
    }

	/**
     * プリフィルタ
     *
     * @access  private
     */
    function _preFilter()
    {
    	$set_arr = array();
        $attributes = $this->getAttributes();
        foreach ($attributes as $key => $value) {
        	$$key = array();
        	switch ($value) {
        		case "TARGET_SESSION_ROOM_ID":
        			//ターゲットルームID指定あり
        			$room_id = $this->_session->getParameter($key);
        			if(isset($room_id) && $room_id != "") {
        				$this->target_room_id = $room_id;
        			}
        			break;
        		case "TARGET_ROOM_ID":
        			//ターゲットルームID指定あり
        			$room_id = $this->_request->getParameter($key);
        			if(isset($room_id) && $room_id != "") {
        				$this->target_room_id = $room_id;
        			}
        			break;
        		case "TARGET_MODULE_ID":
        			//ターゲットモジュールID指定あり
        			$module_id = $this->_request->getParameter($key);
        			if(isset($module_id) && $module_id != "") {
        				$this->target_module_id = $module_id;
        			}
        			break;
        		case "TARGET_LOGIN_ID":
        			//ターゲットログインID指定あり
        			$user_id = $this->_session->getParameter("_user_id");
        			if($user_id != "0") {
        				$this->target_user_id = $user_id;
        			}
        			break;
				case "TARGET_USER_ID":
        			//ターゲット会員ID指定あり
        			$user_id = $this->_request->getParameter($key);
        			if($user_id != "0") {
        				$this->target_user_id = $user_id;
        			}
        			break;
        		case "ALLOW_ROOM_ID":
        		case "ALLOW_PAGE_ID":
        		case "ALLOW_PAGE_ARR":
        		case "ALLOW_ROOM_ARR":
        		case "ALLOW_ROOM_ARR_FLAT":
        		case "ALLOW_PAGE_ARR_FLAT":
        		case "ALLOW_BLOCK_ID":
        		case "ALLOW_BLOCK_ARR":
        			$set_arr[$key] = $value;
        			//$this->setShowPagesList();
        			break;
        	}

        }
        if(count($set_arr) == 0) {
        	return;
        }

        $result = $this->setShowPagesList();
        if(is_array($result)) {
        	list($room_id_ret, $page_id_ret, $ret, $ret_room, $ret_flat, $ret_room_flat) = $result;
        	$block_ret = null;
        	foreach($set_arr as $key => $value) {
        		switch ($value) {
        			//
	        		// researchmap用にカスタマイズ
	        		//
	        		case "ENTRY_USER_FLAG":
	        			$this->entry_user_flag = true;
	        			break;
	        		case "ENTRY_USER_OR_CURRENTROOM_FLAG":
	        			$this->entry_user_flag = true;
	        			$this->current_room_flag = true;
	        			break;
	        		// researchmap end
        			case "ALLOW_ROOM_ID":
        				$this->_request->setParameter($key, $room_id_ret);
        				break;
	        		case "ALLOW_PAGE_ID":
	        			$this->_request->setParameter($key, $page_id_ret);
        				break;
	        		case "ALLOW_PAGE_ARR":
	        			$this->_request->setParameter($key, $ret);
        				break;
	        		case "ALLOW_ROOM_ARR":
	        			$this->_request->setParameter($key, $ret_room);
        				break;
	        		case "ALLOW_ROOM_ARR_FLAT":
	        			$this->_request->setParameter($key, $ret_room_flat);
        				break;
	        		case "ALLOW_PAGE_ARR_FLAT":
	        			$this->_request->setParameter($key, $ret_flat);
	        		case "ALLOW_BLOCK_ID":
	        		case "ALLOW_BLOCK_ARR":
	        			if(empty($block_ret)) {
	        				if( $this->target_module_id != null) {
	        					$blocks_where_params = array("module_id" => $this->target_module_id);
	        				} else {
	        					$blocks_where_params = null;
	        				}
	        				$block_ret = $this->_blocksView->getBlockByPageId($page_id_ret, $blocks_where_params, array($this, '_showblocks_fetchcallback'));
	        			}
	        			if($block_ret !== false) {
        					if($value == "ALLOW_BLOCK_ID") {
        						$this->_request->setParameter($key, $block_ret[0]);
        					}
        					if($value == "ALLOW_BLOCK_ARR") {
        						$this->_request->setParameter($key, $block_ret[1]);
        					}
        				}
        				break;
        		}

        	}
        }
    }

    /**
     * ポストフィルタ
     *
     * @access  private
     */
    function _postFilter()
    {
        // 何もしません。
    }


	/**
	 *
	 * 表示可能ページ取得
	 *
	 */
	function setShowPagesList() {
		if($this->target_user_id != null && $this->target_user_id != "0") {
			$user =& $this->_usersView->getUserById($this->target_user_id);
			$_user_id = $user['user_id'];
			$_user_auth_id = $user['user_authority_id'];
			$_role_auth_id = $user['role_authority_id'];
		} else {
			$_user_id = $this->_session->getParameter("_user_id");
			$_user_auth_id = $this->_session->getParameter("_user_auth_id");
			$_role_auth_id = $this->_session->getParameter("_role_auth_id");
		}
		$where_params = array();
		if($this->target_room_id != null) {
			$where_params["{pages}.room_id"] = $this->target_room_id;
		}

		$order_params = array(
			"space_type" => "ASC",
			"private_flag" => "ASC",
			"thread_num" => "ASC",
			"display_sequence" => "ASC",
			"default_entry_flag" => "ASC"
		);

		$func_param = array(
			$_user_id,
			$_user_auth_id
		);

		if($_user_id == "0") {
			//
			//ログイン前
			//
			$sql = "SELECT {pages}.* ,{pages_style}.*,"._ROLE_AUTH_GUEST." as role_authority_id,".
					_OFF." as system_flag,'"._AUTH_GUEST_NAME."' as role_authority_name,"._AUTH_GUEST." as authority_id, ". _HIERARCHY_OTHER ." as hierarchy, 0 as createroom_flag " .
						" FROM {pages} ".
						" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id ".
						" WHERE {pages}.display_flag = ". _ON ." AND {pages}.space_type = ". _SPACE_TYPE_PUBLIC . " ";
		} else {
			//
			//ログイン後
			//
			$sql = "SELECT {pages}.*, {pages_style}.*,{pages_users_link}.role_authority_id,".
						"{authorities}.hierarchy, {authorities}.system_flag,{authorities}.role_authority_name, {authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.createroom_flag " .
						" FROM {pages} ".
						" LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = '".$_user_id."'".
						" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id ".
						" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id " .
						" WHERE 1=1 ".
						" AND {pages}.display_flag != ". _PAGES_DISPLAY_FLAG_DISABLED ." ".
						" AND {pages}.private_flag = ". _OFF ." ".
						" AND ({pages}.space_type = ". _SPACE_TYPE_PUBLIC ." ".
						" OR " .
						$this->_pagesView->getGroupWhereStatement() .
						")";
						////" OR ({pages}.space_type = ". _SPACE_TYPE_GROUP ." AND {pages}.default_entry_flag =". _ON ."))";

			$sql2 = "SELECT {pages}.*, {pages_style}.*,{pages_users_link}.role_authority_id,".
						"{authorities}.hierarchy, {authorities}.system_flag,{authorities}.role_authority_name, {authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.createroom_flag " .
						" FROM {pages} ".
						" INNER JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = '".$_user_id."'".
						" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id ".
						" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id " .
						" WHERE 1=1 ".
						" AND {pages}.display_flag != ". _PAGES_DISPLAY_FLAG_DISABLED ." ".
						" AND {pages}.private_flag = ". _ON ." ".
						" AND {pages}.space_type = ". _SPACE_TYPE_GROUP ." ".
						" AND {pages}.insert_user_id = '". $_user_id ."' ";
		}

		$params = array();
		if($where_params) {
			$where_str = $this->_db->getWhereSQL($params, $where_params, false);
			$sql .= $where_str;
			if(!empty($sql2))
				$sql2 .= $where_str;
			//$sql .= $this->_db->getWhereSQL(&$params, &$where_params, false);
			//if(!empty($sql2))
			//	$sql2 .= $this->_db->getWhereSQL(&$params, &$where_params, false);
		}
		//言語
        $lang = $this->_session->getParameter('_lang');
		if(!empty($lang)) {
			$params['lang_dirname'] = $lang;
			$sql .= " AND (lang_dirname = \"\" OR lang_dirname = ?)";
			if(!empty($sql2))
				$sql2 .= " AND (lang_dirname = \"\" OR lang_dirname = ?)";
		} else {
			$sql .= " AND lang_dirname = \"\" ";
			if(!empty($sql2))
				$sql2 .= " AND lang_dirname = \"\" ";
		}
		if ($order_params) {
			$sql_order = "";
	        foreach ($order_params as $key=>$item) {
	        	$sql_order .= ",".$key." ".(empty($item) ? "ASC" : $item);
	        }
	        $sql .= " ORDER BY ".substr($sql_order,1);
	        if(!empty($sql2))
				$sql2 .= " ORDER BY ".substr($sql_order,1);
        }

		$result = $this->_db->execute($sql,$params, null, null,true, array($this, '_showpages_fetchcallback'), $func_param);
		if(!empty($sql2)) {
			$func_param = array(
				$_user_id,
				$_user_auth_id,
				$result
			);
			$result2 = $this->_db->execute($sql2,$params, null, null,true, array($this, '_showpages_fetchcallback'), $func_param);
			if(count($result2) > 0) {
				$result = $result2;
			}
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function _showpages_fetchcallback($result, $func_param) {
		$getdata =& $this->_container->getComponent("GetData");
		$config =& $getdata->getParameter("config");

		$column_row = array();
		if($config[_PAGESTYLE_CONF_CATID]['column_space_type_use']['conf_value'] == _OFF) {
			// スペースタイプ毎に左右カラム、ヘッダーを分けない
			$headercolumn_page_id = $this->_session->getParameter("_headercolumn_page_id");
	    	$leftcolumn_page_id = $this->_session->getParameter("_leftcolumn_page_id");
	    	$rightcolumn_page_id = $this->_session->getParameter("_rightcolumn_page_id");

	    	$headercolumn_flag[0] = false;
			$leftcolumn_flag[0] = false;
			$rightcolumn_flag[0] = false;

		} else {
			$headercolumn_page_id_str = $config[_PAGESTYLE_CONF_CATID]['headercolumn_page_id']['conf_value'];
			$leftcolumn_page_id_str   = $config[_PAGESTYLE_CONF_CATID]['leftcolumn_page_id']['conf_value'];
			$rightcolumn_page_id_str  = $config[_PAGESTYLE_CONF_CATID]['rightcolumn_page_id']['conf_value'];

			$headercolumn_page_id_arr = explode("|",$headercolumn_page_id_str);
			$leftcolumn_page_id_arr   = explode("|",$leftcolumn_page_id_str);
			$rightcolumn_page_id_arr  = explode("|",$rightcolumn_page_id_str);

			$headercolumn_flag[0] = false;
			$leftcolumn_flag[0] = false;
			$rightcolumn_flag[0] = false;

			$headercolumn_flag[1] = false;
			$leftcolumn_flag[1] = false;
			$rightcolumn_flag[1] = false;

			$headercolumn_flag[2] = false;
			$leftcolumn_flag[2] = false;
			$rightcolumn_flag[2] = false;
		}
		$user_id = $func_param[0];
		$user_auth_id = $func_param[1];

		if(!empty($func_param[2])) {
			list($room_id_ret, $page_id_ret, $ret, $ret_room, $ret_flat, $ret_room_flat) = $func_param[2];
		} else {
			$room_id_ret = array();
			$page_id_ret = array();
			$ret = array();
			$ret_room = array();
			$ret_flat = array();
			$ret_room_flat = array();
		}

		while ($row = $result->fetchRow()) {

			if($row['system_flag'] == _ON && $row['role_authority_name'] != null) {
				if(defined($row['role_authority_name'])) {
					$row['role_authority_name'] = constant($row['role_authority_name']);
				}
			}

			//設定されてなければ、configのdefault値をセット
			$row['leftcolumn_flag'] = isset($row['leftcolumn_flag']) ?
						 $row['leftcolumn_flag'] : $config[_PAGESTYLE_CONF_CATID]['leftcolumn_flag']['conf_value'];
			$row['rightcolumn_flag'] = isset($row['rightcolumn_flag']) ?
							 $row['rightcolumn_flag'] : $config[_PAGESTYLE_CONF_CATID]['rightcolumn_flag']['conf_value'];
			$row['header_flag'] = isset($row['header_flag']) ?
							 $row['header_flag'] : $config[_PAGESTYLE_CONF_CATID]['header_flag']['conf_value'];
			$row['footer_flag'] = isset($row['footer_flag']) ?
							 $row['footer_flag'] : $config[_PAGESTYLE_CONF_CATID]['footer_flag']['conf_value'];

			// researchmap用にカスタマイズ
			if(($row['space_type'] !=_SPACE_TYPE_PUBLIC) &&
				!($row['space_type']==_SPACE_TYPE_GROUP && $row['private_flag']==_OFF && $row['thread_num']==0) &&
					$this->entry_user_flag && $row['authority_id'] === null) {
				if($this->current_room_flag == false || $this->_session->getParameter("_main_room_id") != $row['room_id']) {
					continue;
				}
			}
			// researchmap end

			if($row['thread_num']==0) {
				if($row['space_type'] ==_SPACE_TYPE_PUBLIC) {
					$row['page_name'] = _SPACE_TYPE_NAME_PUBLIC;
				} else if($row['space_type'] ==_SPACE_TYPE_GROUP && $row['private_flag'] ==_ON){
					if($row['default_entry_flag'] ==_ON)
						$row['page_name'] = _SPACE_TYPE_NAME_MYPORTAL;
					else
						$row['page_name'] = _SPACE_TYPE_NAME_PRIVATE;
				} else {
					$row['page_name'] = _SPACE_TYPE_NAME_GROUP;
				}
			}
			if($row['page_id'] == $row['room_id'] && $row['space_type'] ==_SPACE_TYPE_GROUP
				 && $row['private_flag'] ==_OFF) {
				if($this->_session->getParameter("_lang") == "japanese" && !empty($row['snscommunity_name_ja'])) {
					$row['page_name'] = $row['snscommunity_name_ja'];
				} else if($this->_session->getParameter("_lang") == "english" && !empty($row['snscommunity_name_en'])){
					$row['page_name'] = $row['snscommunity_name_en'];
				}
			}

			$page_id_ret[] = $row['page_id'];
			//$page_name_ret[$row['page_id']] = $row['page_name'];
			if($row['page_id'] == $row['room_id']) {
				if($row['default_entry_flag'] == _ON && isset($row['role_authority_id']) && $row['role_authority_id'] == _ROLE_AUTH_OTHER) {
					$row['authority_id'] = _AUTH_OTHER;
					$row['hierarchy'] = _HIERARCHY_OTHER;
					continue;
				} else if($row['default_entry_flag'] == _ON && $row['authority_id'] == null) {
					if($row['private_flag'] == _ON) {
						$_default_entry_auth_private = $this->_session->getParameter("_default_entry_auth_private");
						if(isset($_default_entry_auth_private)) {
							$row['authority_id'] = $_default_entry_auth_private;
							$row['hierarchy'] = $this->_session->getParameter("_default_entry_hierarchy_private");
						}
					} elseif($row['space_type'] == _SPACE_TYPE_PUBLIC) {
						$_default_entry_auth_public = $this->_session->getParameter("_default_entry_auth_public");
						if(isset($_default_entry_auth_public)) {
							$row['authority_id'] = $_default_entry_auth_public;
							$row['hierarchy'] = $this->_session->getParameter("_default_entry_hierarchy_public");
						}
					} else {
						$_default_entry_auth_group = $this->_session->getParameter("_default_entry_auth_group");
						if(isset($_default_entry_auth_group)) {
							$row['authority_id'] = $_default_entry_auth_group;
							$row['hierarchy'] = $this->_session->getParameter("_default_entry_hierarchy_group");
						}
					}

					if($row['authority_id'] == null) {
						$row['authority_id'] = _AUTH_OTHER;
						$row['hierarchy'] = _HIERARCHY_OTHER;
					} else if($user_auth_id == _AUTH_GUEST && $row['authority_id'] == _AUTH_GENERAL) {
						$row['authority_id'] = _AUTH_GUEST;
					}

					// ゲスト　あるいは、一般で固定値
					if($row['authority_id'] == _AUTH_OTHER) {
						$row['role_authority_id'] = _ROLE_AUTH_OTHER;
						$row['role_authority_name'] = "";
					} else if($row['authority_id'] == _AUTH_GENERAL) {
						$row['role_authority_id'] = _ROLE_AUTH_GENERAL;
						$row['role_authority_name'] = _AUTH_GENERAL_NAME;
					} else {
						$row['role_authority_id'] = _ROLE_AUTH_GUEST;
						$row['role_authority_name'] = _AUTH_GUEST_NAME;
					}
				}
			}
			if($row['display_flag'] == _OFF && $row['authority_id'] != _AUTH_CHIEF) {
				continue;
			}
			$ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
			$ret_flat[intval($row['page_id'])] =& $ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])];
			if($row['page_id'] == $row['room_id']) {
				//ルーム
				$room_id_ret[] = $row['page_id'];
				$ret_room[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] =& $ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])];
				$ret_room_flat[intval($row['page_id'])] =& $ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])];
			}
			if($row['display_sequence'] == 0) {
				// 左右カラム or ヘッダー
				$column_row[] = $row;
				continue;
			}
			//
			// 左右カラム、ヘッダーが表示可能ページかどうか
			//
			if($config[_PAGESTYLE_CONF_CATID]['column_space_type_use']['conf_value'] == _OFF) {
				$col_num = 0;
			} else {
				if($row['space_type'] == _SPACE_TYPE_PUBLIC) {
					$col_num = 0;
				} else if($row['private_flag'] == _ON) {
					$col_num = 1;
				} else {
					$col_num = 2;
				}
			}
			if($row['header_flag'] == _ON) {
				$headercolumn_flag[$col_num] = true;
			}
			if($row['leftcolumn_flag'] == _ON) {
				$leftcolumn_flag[$col_num] = true;
			}
			if($row['rightcolumn_flag'] == _ON) {
				$rightcolumn_flag[$col_num] = true;
			}
		}
		for($i = 0; $i< count($column_row); $i++) {
			if($config[_PAGESTYLE_CONF_CATID]['column_space_type_use']['conf_value'] == _OFF) {
				$col_num = 0;
			} else {
				if($column_row[$i]['space_type'] == _SPACE_TYPE_PUBLIC) {
					$col_num = 0;
				} else if($column_row[$i]['private_flag'] == _ON) {
					$col_num = 1;
				} else {
					$col_num = 2;
				}
				$headercolumn_page_id = $headercolumn_page_id_arr[$col_num];
				$leftcolumn_page_id = $leftcolumn_page_id_arr[$col_num];
				$rightcolumn_page_id = $rightcolumn_page_id_arr[$col_num];
			}
			if(($column_row[$i]['page_id'] == $headercolumn_page_id && $headercolumn_flag[$col_num] == true) ||
				($column_row[$i]['page_id'] == $leftcolumn_page_id && $leftcolumn_flag[$col_num] == true) ||
				($column_row[$i]['page_id'] == $rightcolumn_page_id && $rightcolumn_flag[$col_num] == true)
				) {
				// 閲覧可能ページ
				$ret[intval($column_row[$i]['thread_num'])][intval($column_row[$i]['parent_id'])][intval($column_row[$i]['display_sequence'])] = $column_row[$i];
				$ret_flat[intval($column_row[$i]['page_id'])] =& $ret[intval($column_row[$i]['thread_num'])][intval($column_row[$i]['parent_id'])][intval($column_row[$i]['display_sequence'])];
				$page_id_ret[] = $column_row[$i]['page_id'];
				//$page_name_ret[$column_row[$i]['page_id']] = $column_row[$i]['page_name'];
				if($column_row[$i]['page_id'] == $column_row[$i]['room_id']) {
					//ルーム
					$room_id_ret[] = $column_row[$i]['page_id'];
					$ret_room[intval($column_row[$i]['thread_num'])][intval($column_row[$i]['parent_id'])][intval($column_row[$i]['display_sequence'])] =& $ret[intval($column_row[$i]['thread_num'])][intval($column_row[$i]['parent_id'])][intval($column_row[$i]['display_sequence'])];
					$ret_room_flat[intval($column_row[$i]['page_id'])] =& $ret[intval($column_row[$i]['thread_num'])][intval($column_row[$i]['parent_id'])][intval($column_row[$i]['display_sequence'])];
				}
			}

		}
		return array($room_id_ret, $page_id_ret, $ret, $ret_room, $ret_flat, $ret_room_flat);
	}

	/**
	 * fetch時コールバックメソッド(blocks)
	 * @param result adodb object
	 * @access	private
	 */
	function _showblocks_fetchcallback($result) {
		$block_id_ret = array();
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[] = $row;
			$block_id_ret[] = $row['block_id'];
		}
		return array($block_id_ret, $ret);
	}
}
?>