<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページ表示クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	var $_session = null;

	// リクエストパラメータを受け取るため
	var $page_id = null;
	//var $update_count = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pages_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
    	$this->_db =& $this->_container->getComponent("DbObject");
    	$this->_session =& $this->_container->getComponent("Session");
	}

	/**
	 * pagesテーブルからpages_objectの配列を取得する
	 * @param int page_id or array page_id_array
	 * @param user_id(default:login user_id)
	 * @return array
	 * @access	public
	 */
	function &getPageById($params, $user_id = null)
	{
		$session =& $this->_container->getComponent("Session");
		$user_id = ($user_id == null || $user_id == "0") ? $session->getParameter("_user_id") : $user_id;
		if(is_array($params)) {
			$count = count($params);
			$params = array_merge(array("user_id"=>$user_id), $params);

			$sql = "SELECT {pages}.*,{pages_style}.*,{authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.role_authority_id, {pages_users_link}.createroom_flag " .
					" FROM {pages} " .
					" LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = ? " .
					" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id " .
					" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id " .
					" WHERE {pages}.page_id=?";
			for($i = 1; $i < $count; $i++) {
				$sql .= " OR {pages}.page_id=?";
			}
			$sql .= " ORDER BY {pages}.display_position ";
			$result = $this->_db->execute($sql,$params, null,null,true, array($this, 'fetchcallback'));
			if ($result === false) {
		       	$this->_db->addError();
		       	return $result;
			}
			if(!isset($result[0])) {
				$result = false;
			}
		} else {
			$id = $params;
			$params = array(
				"user_id" => $user_id,
				"page_id" => $id
			);
			$result = $this->_db->execute("SELECT {pages}.*,{pages_style}.*,{authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.role_authority_id, {pages_users_link}.createroom_flag " .
										" FROM {pages} " .
										" LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = ? " .
										" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id " .
										" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id " .
										" WHERE {pages}.page_id=? ORDER BY {pages}.display_position ",$params,null,null,true,  array($this, 'fetchcallback'));
			if ($result === false) {
		       	$this->_db->addError();
		       	return $result;
			}
			if(!isset($result[0])) {
				$result = false;
			}
			$result = $result[0];
		}

		return $result;
	}

	/**
	 * meta情報のデフォルト値を取得
	 * @param array $page
	 * @return array
	 * $meta = array(
     * 		'sitename','meta_language','meta_robots','meta_keywords','meta_description',
     *      'meta_rating','meta_copyright','meta_footer','title'
	 * );
	 *
	 * @access	public
	 */
	function getDafaultMeta($page) {
		if($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['thread_num'] == 1 && $page['display_sequence'] == 1) {
			// トップページ
			$default_page_title = "";
		} else if($page['private_flag'] == _ON) {
			// マイポータル
			if($page['thread_num'] == 0) {
				$default_page_title = "{X-ROOM}";
			} else {
				$default_page_title = "{X-ROOM} - {X-PAGE}";
			}
			if($page['thread_num'] == 0) {
				$meta_description = "{X-USER}";
			} else {
				$meta_description = "{X-USER} - {X-PAGE}";
			}
		} else if($page['space_type'] == _SPACE_TYPE_GROUP && $page['page_id'] != $page['room_id']) {
			$default_page_title = "{X-ROOM} - {X-PAGE}";
		} else if($page['space_type'] == _SPACE_TYPE_GROUP) {
			$default_page_title = "{X-ROOM}";
		} else {
			$default_page_title = "{X-PAGE}";
		}

		$meta = $this->_session->getParameter("_meta");
		if(isset($meta_description)) {
			$meta['meta_description'] = $meta_description;
		}
		//if(isset($meta_keywords)) {
		//	$meta['meta_keywords'] = $meta_keywords;
		//}
		$meta['title'] = $default_page_title;

    	$meta['permalink'] = $page['permalink'];
		return $meta;
	}

	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $pages
	 * @access	private
	 */
	function &fetchcallback($result) {
		$getdata =& $this->_container->getComponent("GetData");
		$config =& $getdata->getParameter("config");
		while ($row = $result->fetchRow()) {

			//設定されてなければ、configのdefault値をセット
			$row['leftcolumn_flag'] = isset($row['leftcolumn_flag']) ?
						 $row['leftcolumn_flag'] : $config[_PAGESTYLE_CONF_CATID]['leftcolumn_flag']['conf_value'];
			$row['rightcolumn_flag'] = isset($row['rightcolumn_flag']) ?
							 $row['rightcolumn_flag'] : $config[_PAGESTYLE_CONF_CATID]['rightcolumn_flag']['conf_value'];
			$row['header_flag'] = isset($row['header_flag']) ?
							 $row['header_flag'] : $config[_PAGESTYLE_CONF_CATID]['header_flag']['conf_value'];
			$row['footer_flag'] = isset($row['footer_flag']) ?
							 $row['footer_flag'] : $config[_PAGESTYLE_CONF_CATID]['footer_flag']['conf_value'];
			if(!isset($row['theme_name']) || $row['theme_name'] == "") {
				if($row['space_type'] == _SPACE_TYPE_GROUP && $row['private_flag'] == _ON)	{
					// プライベートスペース
					$row['theme_name'] = $config[_PAGESTYLE_CONF_CATID]['default_theme_private']['conf_value'];
				} else if($row['space_type'] == _SPACE_TYPE_GROUP) {
					// グループスペース
					$row['theme_name'] = $config[_PAGESTYLE_CONF_CATID]['default_theme_group']['conf_value'];
				} else {
					// パブリックスペース
					$row['theme_name'] = $config[_PAGESTYLE_CONF_CATID]['default_theme_public']['conf_value'];
				}
			}
			$row['temp_name'] = (isset($row['temp_name']) && $row['temp_name'] != "") ?
							 $row['temp_name'] : $config[_PAGESTYLE_CONF_CATID]['default_temp']['conf_value'];

			$row['align'] = isset($row['align']) ?
							 $row['align'] : $config[_PAGESTYLE_CONF_CATID]['align']['conf_value'];
			$row['leftmargin'] = isset($row['leftmargin']) ?
							 $row['leftmargin'] : $config[_PAGESTYLE_CONF_CATID]['leftmargin']['conf_value'];
			$row['rightmargin'] = isset($row['rightmargin']) ?
							 $row['rightmargin'] : $config[_PAGESTYLE_CONF_CATID]['rightmargin']['conf_value'];
			$row['topmargin'] = isset($row['topmargin']) ?
							 $row['topmargin'] : $config[_PAGESTYLE_CONF_CATID]['topmargin']['conf_value'];
			$row['bottommargin'] = isset($row['bottommargin']) ?
							 $row['bottommargin'] : $config[_PAGESTYLE_CONF_CATID]['bottommargin']['conf_value'];
			$row['body_style'] = isset($row['body_style']) ?
							 $row['body_style'] : "";
			$row['header_style'] = isset($row['header_style']) ?
							 $row['header_style'] : "";
			$row['centercolumn_style'] = isset($row['centercolumn_style']) ?
							 $row['centercolumn_style'] : "";

			$row['leftcolumn_style'] = isset($row['leftcolumn_style']) ?
							 $row['leftcolumn_style'] : "";
			$row['rightcolumn_style'] = isset($row['rightcolumn_style']) ?
							 $row['rightcolumn_style'] : "";
			$row['footer_style'] = isset($row['footer_style']) ?
							 $row['footer_style'] : "";
			$ret[] = $row;

		}
		return $ret;
	}

	/**
	 * pages_styleテーブルの一覧を取得する
	 * @param array where_params
	 * @param array order_params
	 * @return array pages
	 * @access	public
	 */
	function &getPagesStyle($where_params=null, $order_params=null)
	{
		$db_params = array();
		if (!isset($order_params)) {
        	$order_params = array("{pages_style}.set_page_id"=>"ASC");
        }
		$sql = $this->_db->getSelectSQL("pages_style", $db_params, $where_params, $order_params);
		$result = $this->_db->execute($sql ,$db_params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * pagesテーブルの一覧を取得する
	 * @param array where_params
	 * @param array order_params
	 * @return array pages
	 * @access	public
	 */
	function &getPages($where_params=null, $order_params=null, $limit=0, $start=0, $func = null, $func_param = null)
	{
		if (!isset($order_params)) {
        	$order_params = array("{pages}.page_id"=>"ASC");
        }
		$result = $this->_db->selectExecute("pages", $where_params, $order_params, $limit, $start, $func, $func_param);
		if ($result === false) {
	       	return $result;
		}
		return $result;
	}

	/**
	 * 指定会員の指定権限以上のルームの一覧を返す
	 *
	 * @param array $user_id
	 * @param array $authority_id default:_ROLE_AUTH_CHIEF
	 * @access	public
	 */
	function &getRoomIdByUserId($user_id = null, $more_than_authority_id = _AUTH_CHIEF)
	{
		if($user_id == null) {
			$session =& $this->_container->getComponent("Session");
			$user_id = $session->getParameter("_user_id");
			$_user_auth_id = $session->getParameter("_user_auth_id");
		} else {
			//user_id指定あり
			$usersView =& $this->_container->getComponent("usersView");
			$user =& $usersView->getUserById($user_id);
			$_user_auth_id = $user['user_authority_id'];
		}
		$where_params = array(
			"user_id" => $user_id,
			"{pages}.room_id={pages}.page_id" => null
		);
		$order_params = array(
			"space_type" => "ASC",
			"private_flag" => "ASC",
			"thread_num" => "ASC",
			"display_sequence" => "ASC"
		);
		$result = $this->getShowPagesList($where_params, $order_params, null, null, array($this, '_showpages_fetchcallback'), array($_user_auth_id, $more_than_authority_id));
		if($result === false) {
			return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @param array  $func_param
	 * @access	private
	 */
	function _showpages_fetchcallback($result, $func_param) {
		$user_auth_id = $func_param[0];
		$more_than_authority_id = $func_param[1];
		$session =& $this->_container->getComponent("Session");
		$ret = array();
		while ($row = $result->fetchRow()) {
			if($row['default_entry_flag'] == _ON  && isset($row['role_authority_id']) && $row['role_authority_id'] == _ROLE_AUTH_OTHER) {
				//$row['authority_id'] = _AUTH_OTHER;
				continue;
			} else if($row['default_entry_flag'] == _ON && $row['authority_id'] == null) {
				if($row['private_flag'] == _ON) {
					$_default_entry_auth_private = $session->getParameter("_default_entry_auth_private");
					if(isset($_default_entry_auth_private)) {
						$row['authority_id'] = $_default_entry_auth_private;
						//$row['hierarchy'] = $session->getParameter("_default_entry_hierarchy_private");
					}
				} elseif($row['space_type'] == _SPACE_TYPE_PUBLIC) {
					$_default_entry_auth_public = $session->getParameter("_default_entry_auth_public");
					if(isset($_default_entry_auth_public)) {
						$row['authority_id'] = $_default_entry_auth_public;
						//$row['hierarchy'] = $session->getParameter("_default_entry_hierarchy_public");
					}
				} else {
					$_default_entry_auth_group = $session->getParameter("_default_entry_auth_group");
					if(isset($_default_entry_auth_group)) {
						$row['authority_id'] = $_default_entry_auth_group;
						//$row['hierarchy'] = $session->getParameter("_default_entry_hierarchy_group");
					}
				}

				if($row['authority_id'] == null) {
					$row['authority_id'] = _AUTH_OTHER;
					//$row['hierarchy'] = _HIERARCHY_OTHER;
				} else if($user_auth_id == _AUTH_GUEST && $row['authority_id'] == _AUTH_GENERAL) {
					$row['authority_id'] = _AUTH_GUEST;
				}
			}
			if($row['authority_id'] >= $more_than_authority_id) {
				$ret[] = $row["room_id"];
			}
		}
		return $ret;
	}

	/**
	 * 表示可能なページデータ取得
	 *
	 * @param array where_params
	 * @param array order_params
	 * @param int limit
	 * @param int start
	 * @param function func
	 * @param array    func_param
	 * @return array pages_array
	 * @access	public
	 */
	function &getShowPagesList($where_params=null, $order_params=null, $limit=0, $start=0, $func = null, $func_param = null)
	{
		$session =& $this->_container->getComponent("Session");
		if($where_params == null || !isset($where_params["user_id"])) {
			$_user_id = $session->getParameter("_user_id");
			$_user_auth_id = $session->getParameter("_user_auth_id");
			$_role_auth_id = $session->getParameter("_role_auth_id");
		} else if(isset($where_params["user_id"]) && isset($where_params["user_authority_id"]) &&
			isset($where_params["role_authority_id"])) {
			//user_id,user_authority_id,role_authority_id指定あり
			$_user_id = $where_params['user_id'];
			$_user_auth_id = $where_params['user_authority_id'];
			$_role_auth_id = $where_params['role_authority_id'];
			unset($where_params['user_id']);
			unset($where_params['user_authority_id']);
			unset($where_params['role_authority_id']);
		} else {
			//user_id指定あり
			$usersView =& $this->_container->getComponent("usersView");
			$user =& $usersView->getUserById($where_params["user_id"]);
			$_user_id = $user['user_id'];
			$_user_auth_id = $user['user_authority_id'];
			$_role_auth_id = $user['role_authority_id'];
			unset($where_params['user_id']);
		}
		if($func_param == null) {
			$func_param = array(
				$_user_id,
				$_user_auth_id
			);
		}
		if($_user_id == "0") {
			//
			//ログイン前
			//
			$sql = "SELECT {pages}.* ,{pages_style}.*,"._ROLE_AUTH_GUEST." as role_authority_id,".
					_OFF." as system_flag,'"._AUTH_GUEST_NAME."' as role_authority_name,"._AUTH_GUEST." as authority_id, ". _HIERARCHY_OTHER ." as hierarchy, 0 as createroom_flag " .
						" FROM {pages} ".
						" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id ".
						" WHERE {pages}.display_flag = ". _ON ." AND (({pages}.space_type ="._SPACE_TYPE_PUBLIC." AND {pages}.private_flag ="._OFF.")";
			$main_room_id = $session->getParameter('_main_room_id');
			if(isset($main_room_id) && $session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC) {
				$sql .= " OR ({pages}.space_type ="._SPACE_TYPE_GROUP." AND {pages}.private_flag ="._ON." AND {pages}.room_id = ".$main_room_id.")) ";
			} else {
				$sql .= ")";
			}
		} else {
			//
			//ログイン後
			//
			$sql = "SELECT {pages}.*, {pages_style}.*,{pages_users_link}.role_authority_id,".
						"{authorities}.hierarchy, {authorities}.system_flag,{authorities}.role_authority_name, {authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.createroom_flag " .
						" FROM {pages} ".
						" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id ".
						" LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = '".$_user_id."'".
						" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id " .
						" WHERE 1=1 ";

			if($_user_auth_id == _AUTH_ADMIN) {
				//管理者
				$sql .= " AND (({pages}.private_flag = "._ON." " .
					" AND {pages_users_link}.user_id IS NOT NULL) OR ({pages}.private_flag = "._OFF." AND ({pages}.space_type = "._SPACE_TYPE_GROUP." OR {pages}.space_type ="._SPACE_TYPE_PUBLIC."))) ".
					" AND {pages}.display_flag != ". _PAGES_DISPLAY_FLAG_DISABLED ." ";
			} else {
				$sql .= " AND (".
						"({pages}.space_type ="._SPACE_TYPE_PUBLIC." AND {pages}.private_flag ="._OFF.") OR ".
						"({pages}.space_type ="._SPACE_TYPE_GROUP." AND {pages}.private_flag ="._ON.
						" AND {pages}.insert_user_id = '".$_user_id."') OR ".
						$this->getGroupWhereStatement() .
					")";
					//" AND ({pages}.default_entry_flag =1 OR {pages_users_link}.user_id IS NOT NULL))".

				if($session->getParameter("_user_id") == "0") {
					$sql .= " AND {pages}.display_flag = ". _ON;
				}
			}
		}
		$params = array();
		if($where_params) {
			$sql .= $this->_db->getWhereSQL($params, $where_params, false);
		}
		if ($order_params) {
			$sql_order = "";
	        foreach ($order_params as $key=>$item) {
	        	$sql_order .= ",".$key." ".(empty($item) ? "ASC" : $item);
	        }
	        $sql .= " ORDER BY ".substr($sql_order,1);
        }

		$result = $this->_db->execute($sql,$params, $limit,$start,true,$func,$func_param);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * pages,pages_users_linkの取得
	 * @param array    $where_params
	 * @param array    $order_params
	 * @param int      $limit
	 * @param int      $start
	 * @param function $func
	 * @param array    $func_param
	 * @access	public
	 */
	function &getPagesUsers($where_params=null, $order_params=null, $limit=0, $start=0, $func = null, $func_param = null)
	{
		$params = array();
		$sql = "SELECT {pages}.*, {pages_users_link}.user_id, {authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.role_authority_id, {pages_users_link}.createroom_flag " .
									" FROM {pages} " .
									" LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id ".
									" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id ";

		$sql .= $this->_db->getWhereSQL($params, $where_params);
		$sql .= $this->_db->getOrderSQL($order_params);

		$result = $this->_db->execute( $sql, $params, $limit, $start ,true, $func, $func_param);
		if ($result === false) {
			$this->_db->addError();
	       	return $result;
		}

		return $result;
	}

	/**
	 * 閲覧可能なプライベートスペースのページを取得
	 * @param int user_id
	 * @return array pages_object
	 * @access	public
	 */
	function &getPrivateSpaceByUserId($user_id, $limit=0, $start=0, $myroom_flag = true)
	{
		$params = array(
			"insert_user_id" => $user_id,
			"user_id" => $user_id
		);

		$result = $this->_db->execute("SELECT {pages}.*, {pages_style}.*, {authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {pages_users_link}.role_authority_id, {pages_users_link}.createroom_flag " .
									" FROM {pages_users_link}, {authorities}, {pages} " .
									" LEFT JOIN {pages_style} ON {pages}.page_id = {pages_style}.set_page_id " .
									" WHERE {pages}.thread_num = 0 AND {pages}.display_flag != "._PAGES_DISPLAY_FLAG_DISABLED.
									" AND {pages}.space_type ="._SPACE_TYPE_GROUP. " AND {pages}.private_flag = "._ON.
									" AND {pages}.insert_user_id = ? " .
									" AND {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = ? " .
									" AND {pages_users_link}.role_authority_id = {authorities}.role_authority_id ".
									" ORDER BY {pages}.default_entry_flag DESC".
									" ",$params, $limit, $start ,true,  array($this, 'fetchcallback'));

		if ($result === false) {
			$this->_db->addError();
	       	return $result;
		}
		if(count($result) == 1) {
			return $result;
		} else if(isset($result[1]) && $myroom_flag == true){
			array_shift($result);
		}
		return $result;
	}

	/**
	 * ページでの子供の数を取得
	 * @param int page_id
	 * @param int lang_dirname
	 * @return int 指定ページの子供の合計数
	 * @access	public
	 */
	function getMaxChildPage($page_id, $lang_dirname="")
	{
		$params = array(
			"parent_id" => $page_id,
			"lang_dirname" => $lang_dirname
		);
		$result = $this->_db->execute("SELECT MAX(display_sequence) FROM {pages} WHERE parent_id=?" .
										" AND lang_dirname=?",$params,null,null,false);
		if ($result === false) {
	       	 $this->_db->addError();
	       	return false;
		}

		return $result[0][0];
	}

	/**
	 * ページユーザリンクSelect
	 * @param array where_params
	 * @param array order_params
	 * @param function func
	 * @param array    func_param
	 * @return boolean
	 * @access	public
	 */
	function &getPageUsersLink($where_params=array(), $order_params=array(), $func = null, $func_param = null)
	{
		$result = $this->_db->selectExecute("pages_users_link", $where_params, $order_params, null, null, $func, $func_param);
		if ($result === false) {
	       	return $result;
		}
		return $result;
	}

	/**
	 * ページモジュールリンクSelect
	 * @param array where_params
	 * @param array order_params
	 * @param function func
	 * @param array    func_param
	 * @return boolean
	 * @access	public
	 */
	function &getPageModulesLink($where_params=array(), $order_params=array(), $func = null, $func_param = null)
	{
		$result = $this->_db->selectExecute("pages_modules_link", $where_params, $order_params, null, null, $func, $func_param);
		if ($result === false) {
	       	return $result;
		}
		return $result;
	}

	/**
	 * 参加ルームデータ用WHERE句を取得する
	 *
	 * @return string 参加ルームデータ用WHERE句
	 * @access public
	 */
	function getGroupWhereStatement()
	{
		$whereStatement = '({pages}.space_type = ' . _SPACE_TYPE_GROUP . ' '
						. 'AND {pages}.private_flag = ' . _OFF . ' '
						. 'AND ({pages}.default_entry_flag = ' . _ON . ' '
								. 'AND ({pages_users_link}.role_authority_id != ' . _ROLE_AUTH_OTHER . ' '
									. 'OR {pages_users_link}.role_authority_id IS NULL)) '
							. 'OR ({pages}.default_entry_flag = ' . _OFF . ' '
								. 'AND {pages_users_link}.role_authority_id IS NOT NULL '
								. 'AND {pages_users_link}.role_authority_id != ' . _ROLE_AUTH_OTHER . ')'
						. ')';

		return $whereStatement;
	}

	/**
	 * ルームで使用可能なモジュールデータ配列を取得する
	 *
	 * @param string $roomId ルームID
	 * @return array ルームで使用可能なモジュールデータ配列
	 * @access public
	 */
	function &getUsableModulesByRoom($roomId, $useIdAsKey = false)
	{
		$fetchFunction = null;
		if ($useIdAsKey) {
			$fetchFunction = array($this, '_fetchModule');
		}
		
		$sql = "SELECT M.module_id, "
					. "M.action_name, "
					. "M.delete_action "
				. "FROM {modules} M "
				. "INNER JOIN {pages_modules_link} PM "
					. "ON M.module_id = PM.module_id "
				. "WHERE PM.room_id = ?";
		$modules =& $this->_db->execute($sql, $roomId, null, null, true, $fetchFunction);
		if ($modules === false) {
			$this->_db->addError();
		}

		return $modules;
	}

	/**
	 * モジュールIDをキーとしたモジュールデータ配列を作成する。
	 * 
	 * @param object $recordSet モジュールデータADORecordSetオブジェクト
	 * @return string 指定文字区切りの文字列
	 * @access private
	 */
	function &_fetchModule(&$recordSet)
	{
		$modules = array();
		while ($module = $recordSet->fetchRow()) {
			$moduleId = $module['module_id'];
			$modules[$moduleId] = $module;
		}

		return $modules;
	}
}
?>
