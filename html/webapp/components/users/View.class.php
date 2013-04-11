<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員テーブル表示用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Users_View
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

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Users_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * user_idからusers情報を取得する
	 * @param int user_id
	 * @return array users
	 * @access	public
	 */
	function &getUserById($id, $func = null, $func_params = null) {
		$params = array("user_id" => $id);
		$result =& $this->getUsers($params, null, $func, $func_params);
		if($result === false || !isset($result[0])) {
			// エラーが発生した場合、エラーリストに追加
			$result = false;
			return $result;
		}
		return $result[0];
	}

	/**
	 *
	 * Item情報取得取得
	 * @param int user_id
	 * @param int user_auth_id
	 * @param array where_params
	 * @param boolean init_flag user_idのチェックされたデータを初期化するかどうか
	 * @return array items
	 */
	function &getShowItems($user_id, $user_auth_id, $where_params = array("display_flag"=>_ON), $init_flag = false) {
		//_AUTH_ADMIN _AUTH_CHIEF _AUTH_MODERATE _AUTH_GENERAL _AUTH_GUEST
		$params = array(); //array("user_id"=>$user_id);
		//
		// すべての項目を取得
		//
		$sql = "SELECT {items}.* , {items_desc}.description, {items_desc}.attribute, {items_options}.options, {items_options}.default_selected," .
					"{users_items_link}.public_flag,{users_items_link}.email_reception_flag, {users_items_link}.content, ".
					"{items_authorities_link}.under_public_flag, {items_authorities_link}.self_public_flag,{items_authorities_link}.over_public_flag ".
					"FROM {items}  ".
					" LEFT JOIN {items_desc} ON ({items}.item_id={items_desc}.item_id)".
					" LEFT JOIN {items_options} ON ({items}.item_id={items_options}.item_id)".
					" LEFT JOIN {users_items_link} ON ({items}.item_id={users_items_link}.item_id AND {users_items_link}.user_id='".$user_id."')".
					" LEFT JOIN {items_authorities_link} ON ({items}.item_id={items_authorities_link}.item_id AND {items_authorities_link}.user_authority_id=".intval($user_auth_id).")".
					" WHERE {items}.type != \"" . USER_TYPE_SYSTEM."\"";
		$sql_where = "";
		if (isset($where_params)) {
	        foreach ($where_params as $key=>$value) {
				if (isset($value)) {
					$params[] = $value;
					$sql_where .= " AND ".$key."=?";
				} else {
					$sql_where .= " AND ".$key;
				}
			}
		}
		$sql .= $sql_where;
		////$sql .= $sql_where ? " WHERE ".substr($sql_where,5) : "";
		$sql .= " ORDER BY {items}.col_num,{items}.row_num ";
		$func_params = array(false, $init_flag);
		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getShowItemsFetchcallback"), $func_params);
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getShowItemsFetchcallback($result, $params= array()) {
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$commonMain =& $container->getComponent("commonMain");
		$languagesView =& $commonMain->registerClass(WEBAPP_DIR.'/components/languages/View.class.php', "Languages_View", "languagesView");
        $timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");

		$languages =& $languagesView->getLanguagesList();
		$id_flag = false;
		if(isset($params[0])) {
			$id_flag = $params[0];
		}
		$init_flag = isset($params[1]) ? $params[1] : false;

		$ret = array();
		while ($row = $result->fetchRow()) {
			if ($row["define_flag"] == _ON) {
				if (defined($row["item_name"])) {
					$row["item_name"] = constant($row["item_name"]);
				}
				if (isset($row["description"]) && defined($row["description"])) {
					$row["description"] = constant($row["description"]);
				}
			}
			if($row["type"] == USER_TYPE_CHECKBOX || $row["type"] == USER_TYPE_RADIO ||
				$row["type"] == USER_TYPE_SELECT) {
				// options:日本語名
				// options_value:数値(ロール権限ID等)
				// def_options:実際比較する値
				if ($row["tag_name"] == "role_authority_name") {
					//
					// 権限
					//
					// 権限一覧取得し、set_optionsにセット
					$authoritiesView =& $this->_container->getComponent("authoritiesView");
					$authorities =& $authoritiesView->getAuthorities();
					$count = 0;
					foreach($authorities as $authority) {

						$row["set_options"][$count]["options"] = $authority['role_authority_name'];
						$row["set_options"][$count]["options_value"] = $authority['role_authority_id'];
						$row["set_options"][$count]["def_options"] = $authority['role_authority_id'];
						//会員ベース権限保存
						$row["set_options"][$count]["user_authority_id"] = $authority['user_authority_id'];
						$row["set_options"][$count]["default_selected"] = _OFF;	//_OFF固定
						$count++;
					}
				} else if ($row["tag_name"] == "active_flag_lang") {
					$row["set_options"][0]["options"] = USER_ITEM_ACTIVE_FLAG_ON;
					$row["set_options"][0]["def_options"] = _USER_ACTIVE_FLAG_ON;
					$row["set_options"][0]["options_value"] = _USER_ACTIVE_FLAG_ON;
					$row["set_options"][0]["default_selected"] = _OFF;	//_OFF固定
					$row["set_options"][1]["options"] = USER_ITEM_ACTIVE_FLAG_OFF;
					$row["set_options"][1]["def_options"] = _USER_ACTIVE_FLAG_OFF;
					$row["set_options"][1]["options_value"] = _USER_ACTIVE_FLAG_OFF;
					$row["set_options"][1]["default_selected"] = _OFF;	//_OFF固定
					$actionChain =& $container->getComponent("ActionChain");
					if($actionChain->getCurActionName() == "user_view_main_search" ||
						$actionChain->getCurActionName() == "user_view_admin_import_export") {
						// 検索アクション,エクスポート処理ならば、承認待ち、承認済みを追加
						$row["set_options"][2]["options"] = USER_ITEM_ACTIVE_FLAG_PENDING;
						$row["set_options"][2]["def_options"] = _USER_ACTIVE_FLAG_PENDING;
						$row["set_options"][2]["options_value"] = _USER_ACTIVE_FLAG_PENDING;
						$row["set_options"][2]["default_selected"] = _OFF;	//_OFF固定

						$row["set_options"][3]["options"] = USER_ITEM_ACTIVE_FLAG_MAILED;
						$row["set_options"][3]["def_options"] = _USER_ACTIVE_FLAG_MAILED;
						$row["set_options"][3]["options_value"] = _USER_ACTIVE_FLAG_MAILED;
						$row["set_options"][3]["default_selected"] = _OFF;	//_OFF固定
					}
				} else if ($row["tag_name"] == "lang_dirname_lang") {
					$row["set_options"][0]["options"] = defined('LANG_NAME_AUTO') ? LANG_NAME_AUTO:'';
					$row["set_options"][0]["def_options"] = "";
					$count = 1;
					foreach($languages as $key => $language) {
						//	language/lang_dir/global.iniをみて現在、使用可能な言語を取得
						if(file_exists(WEBAPP_DIR."/language/".$key."/global.ini") || $key == '') {
							$row["set_options"][$count]["options"] = $language;
							$row["set_options"][$count]["def_options"] = $key;
							if(!isset($row["content"]) && $key == $session->getParameter("_lang")) {
								$row["set_options"][$count]["default_selected"] = _ON;
							} else if ( $row["content"] == $language) {
								$row["set_options"][$count]["default_selected"] = _ON;
							} else {
								$row["set_options"][$count]["default_selected"] = _OFF;
							}
							$count++;
						}
					}
				} else if ((!isset($row["content"]) || $row["content"] === null) || $init_flag) {
					$row["content"] = "";
					// デフォルト値セット

					$default_selected_options = explode("|", $row["default_selected"]);
					$options = explode("|", $row["options"]);
					$count = 0;
					$total_len = count($default_selected_options);
					foreach($default_selected_options as $key => $default_selected_option) {
						if ($row["define_flag"] == _ON && defined($options[$key])) {
							$value = constant($options[$key]);
						} else {
							$value = $options[$key];
						}
						if($value == "" && $total_len - 1 == $count) continue;		//最後の「|」の後の空文字列
						//if($default_selected_option == _ON) {
							//if($row["content"] == "") {
							//	$row["content"] = $value;
							//} else {
							//	$row["content"] .= ",".$value;
							//}
						//}
						$row["set_options"][$count]["options"] = $value;
						$row["set_options"][$count]["def_options"] = $options[$key];
						if($row["tag_name"] == "timezone_offset_lang") {
							// タイムゾーンならば、	default_TZセット
							if($timezoneMain->getFloatTimeZone($value) == $session->getParameter("_default_TZ")) {
								$row["set_options"][$count]["default_selected"] = _ON;
							} else {
								$row["set_options"][$count]["default_selected"] = _OFF;
							}
						} else {
							$row["set_options"][$count]["default_selected"] = $default_selected_option;
						}
						$count++;
			    	}
				} else {
					//設定済み
					$options = explode("|", $row["options"]);
					$edit_options = explode("|", $row["content"]);
					$count = 0;
					$row["content"] = "";
					$total_len = count($options);
					foreach($options as $option) {
						if($option == "" && $total_len - 1 == $count) continue;		//最後の「|」の後の空文字列
						if ($row["define_flag"] == _ON && defined($option)) {
							$row["set_options"][$count]["options"] = constant($option);
						} else {
							$row["set_options"][$count]["options"] = $option;
						}
						$row["set_options"][$count]["def_options"] = $option;
						if(in_array($option, $edit_options, true)) {
							$row["set_options"][$count]["default_selected"] = true;

							if($row["content"] == "") {
								$row["content"] = $row["set_options"][$count]["options"];
							} else {
								$row["content"] .= ",".$row["set_options"][$count]["options"];
							}
						}else {
							$row["set_options"][$count]["default_selected"] = false;
						}
						$count++;
					}
				}
			}

			if( $row['tag_name'] != "" ) {
				$is_users_tbl_fld = $this->isUsersTableField( $row['tag_name'] );
				if( $is_users_tbl_fld == false ) {
					$row['is_users_tbl_fld'] = false;
				}
				else {
					$row['is_users_tbl_fld'] = true;
				}
			}
			else {
				$row['is_users_tbl_fld'] = false;
			}

			//if($row["type"] == USER_TYPE_SYSTEM) {
			//	//参加ルーム、アクセス状況、レポート
			//	continue;
			//}
			if($id_flag) {
				$ret[] = $row;
			} else {
				$ret[intval($row['col_num'])][intval($row['row_num'])] = $row;
			}
		}
		return $ret;
	}

	/**
	 * ユーザー取得
	 *
	 * @param params:　array: user_id, login_id, user_name, handle,
	 * 						email, role_authority_id, active_flag, system_flag, activate_key
	 * @return  array  取得結果配列
	 * @access	public
	 */
	function &getUsers($where_params, $order_params=null, $func = null, $func_params = null)
	{
		$ret_false = false;

		$db_params = array();
		$sql = "SELECT {users}.*, ".
						"{authorities}.role_authority_id,".
						"{authorities}.role_authority_name,".
						"{authorities}.system_flag AS authority_system_flag,".
						"{authorities}.user_authority_id,".
						"{authorities}.public_createroom_flag,".
						"{authorities}.group_createroom_flag,".
						"{authorities}.private_createroom_flag,".
						"{authorities}.myroom_use_flag".
				" FROM {users}".
				" INNER JOIN {authorities} ON ({users}.role_authority_id={authorities}.role_authority_id) ";

		$params = array();
		if(is_array($where_params) && array_key_exists("user_authority_id", $where_params)) {
			$where_params["{authorities}.user_authority_id"] = $where_params["user_authority_id"];
			unset($where_params["user_authority_id"]);
		}
		if(is_array($where_params) && array_key_exists("myroom_use_flag", $where_params)) {
			$where_params["{authorities}.myroom_use_flag"] = $where_params["myroom_use_flag"];
			unset($where_params["myroom_use_flag"]);
		}
		$sql .= $this->_db->getWhereSQL($params, $where_params);
		$sql .= $this->_db->getOrderSQL($order_params);

		$result = $this->_db->execute($sql, $params, null, null, true, $func, $func_params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}


	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_getUsersFetchcallback($result) {
		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");
		$languagesView =& $commonMain->registerClass(WEBAPP_DIR.'/components/languages/View.class.php', "Languages_View", "languagesView");

		$languages =& $languagesView->getLanguagesList();

		$ret = array();
		while ($row = $result->fetchRow()) {
			if(defined("_TZ_GMT0")) {
				//timezone.iniがincludeされているならば
				$row['timezone_offset_lang'] = $timezoneMain->getLangTimeZone($row['timezone_offset']);
			}
			if($row['authority_system_flag'] == _ON && defined($row['role_authority_name'])) {
				$row['role_authority_name'] = constant($row['role_authority_name']);
			}
			if($row['active_flag'] == _USER_ACTIVE_FLAG_PENDING) {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_PENDING;
			} else if($row['active_flag'] == _USER_ACTIVE_FLAG_MAILED) {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_MAILED;
			} else if($row['active_flag'] == _USER_ACTIVE_FLAG_ON) {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_ON;
			} else {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_OFF;
			}
			// 言語
			if(isset($languages[$row['lang_dirname']])) {
				$row['lang_dirname_lang'] = $languages[$row['lang_dirname']];
			}
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * tag_nameからitem_id を取得する
	 * @param string tag_name
	 * @return item_id
	 * @access	public
	 */
	function &getItemIdByTagName($tag_name)
	{
		$ret = $this->_db->selectExecute( "items", array( "tag_name"=>$tag_name ) );
		if( !$ret ) {
			return false;
		}
		return $ret[0]['item_id'];
	}

	/**
	 * 指定されたtag_name（フィールド名）がusersテーブルに存在するか判断する
	 * @param string $tag_name tag名称
	 * @return boolean true:存在する、false:存在しない
	 * @access	public
	 */
	function isUsersTableField($tag_name)
	{
		if (!empty($tag_name)
			&& $tag_name != 'user_name'
			&& $tag_name != 'email'
			&& $tag_name != 'mobile_texthtml_mode'
			&& $tag_name != 'mobile_imgdsp_size') {
			return true;
		}

		return false;
	}

	/**
	 * user_id, item_idからusers_items_link を取得する
	 * @param int user_id
	 * @param int item_id
	 * @return array users_items_link
	 * @access	public
	 */
	function &getUserItemLinkById($user_id, $item_id = null)
	{
		if($item_id != null) {
			$params = array(
				"user_id" => $user_id,
				"item_id" => $item_id
			);

			$result = $this->_db->execute("SELECT {users_items_link}.* FROM {users_items_link} " .
											" WHERE {users_items_link}.user_id=? AND {users_items_link}.item_id=?",$params);
		} else {
			$params = array(
				"user_id" => $user_id
			);

			$result = $this->_db->execute("SELECT {users_items_link}.* FROM {users_items_link} " .
											" WHERE {users_items_link}.user_id=?",$params, null, null, true, array($this, "_getUserItemLinkByIdFetchcallback"));
		}
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if($item_id == null) {
			return $result;
		}
		return $result[0];
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getUserItemLinkByIdFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}

	/**
	 * メール送信先一覧取得
	 *
	 * @param int or array(page_id)		$page_id
	 * @param int						$authority_id(モデレータ以上、主担以上等)
	 * 									page_id指定ありの場合、ルーム権限
	 * 									page_id指定なしの場合、会員権限
	 * @param string					$type(text or html) text/plainのメール、あるいは、htmlメールのみのメール配信の場合に指定
	 * 				　					デフォルト：携帯はtext/plain、PCはhtmlメール
	 * @param array						$where_params
	 * @param array						$order_params
	 *
	 * @return  array  取得結果配列
	 * @access	public
	 */
	function &getSendMailUsers($page_id=null, $more_than_authority_id=null, $type = null ,$where_params=array(), $order_params=array()) {
		if(!empty($page_id)) {
			$pagesView =& $this->_container->getComponent("pagesView");
			$page = $pagesView->getPageById(intval($page_id));
			if ($page === false || !isset($page['page_id'])) {
				$this->_db->addError();
		       	return false;
			}
			//if(($page['private_flag'] == _ON ||
			//	($page['private_flag'] == _OFF && $page['space_type'] == _SPACE_TYPE_GROUP && $page['default_entry_flag'] == _ON))
			//	&& $more_than_authority_id == _AUTH_GUEST) {
			//	$more_than_authority_id = _AUTH_GENERAL;
			//}
			//
			// email項目取得
			//
			$sql = "SELECT item_id, allow_email_reception_flag,";
			if($type == null) {
				$sql .= "{items}.type AS type";
			} else {
				$sql .= "\"".$type."\" AS type";
			}
			$sql .= " FROM {items}".
					" WHERE ({items}.type = 'email' OR {items}.type = 'mobile_email')";
			$items = $this->_db->execute($sql, null, null, null, true, array($this, "_getUserItemLinkByIdFetchcallback"));
			if ($items === false) {
		       	$this->_db->addError();
		       	return $items;
			}else if(count($items) == 0) {
				// email項目なし
				return $items;
			}

			$sql = "SELECT {users}.*,".
						"{authorities}.user_authority_id,{pages_users_link}.role_authority_id AS pages_users_role_authority_id,";
			$sql .= "{users_items_link}.item_id, {users_items_link}.content AS email,".
					$page['space_type']." AS space_type, ". $page['private_flag']. " AS private_flag, ". $page['default_entry_flag']. " AS default_entry_flag ".
				" FROM {users_items_link},{users}";
			if(($page['space_type'] == _SPACE_TYPE_PUBLIC ||
				($page['space_type'] == _SPACE_TYPE_GROUP && $page['private_flag'] == _OFF &&
					$page['default_entry_flag'] == _ON)) &&
					$more_than_authority_id <= _AUTH_GENERAL
					) {
				$sql .= " LEFT JOIN {pages_users_link} ON {users}.user_id={pages_users_link}.user_id";
			} else {
				$sql .= " INNER JOIN {pages_users_link} ON {users}.user_id={pages_users_link}.user_id";
			}
			$sql .= " AND {pages_users_link}.room_id = ". $page_id;
			$sql .= " LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id={authorities}.role_authority_id".
						" WHERE {users}.user_id={users_items_link}.user_id ".
						" AND {users}.active_flag="._ON.
						" AND {users_items_link}.content!='' ";
			$sql .= " AND (1!=1";
			foreach($items as $item) {
				if($item['allow_email_reception_flag'] == _OFF) {
					$sql .= " OR {users_items_link}.item_id = ".$item['item_id']."";
				} else {
					$sql .= " OR ({users_items_link}.item_id = ".$item['item_id']." AND ".
						"{users_items_link}.email_reception_flag="._ON.")";
				}
			}
			$sql .= ")";
			$func = array($this, '_fetchcallbackSendMail');
			$authoritiesView =& $this->_container->getComponent("authoritiesView");
			$authorities = $authoritiesView->getAuthorities(null, null, null, null, true);
			$func_params = array($more_than_authority_id, $authorities, $items);
		} else {
			// ルームID指定なし
			// 会員の user_authority_idのみでメールを送信
			//
			$sql = "SELECT {users}.*, ".
						"{authorities}.user_authority_id,";
			if($type == null) {
				$sql .= "{items}.type,";
			} else {
				$sql .= "\"".$type."\" AS type,";
			}
			$sql .= "{users_items_link}.content AS email".
				" FROM {users},{authorities},{users_items_link},{items}".
				" WHERE {users}.role_authority_id={authorities}.role_authority_id ".
				" AND {users}.user_id={users_items_link}.user_id ".
				" AND {users_items_link}.item_id = {items}.item_id ".
				" AND {users}.active_flag="._ON.
				" AND ({items}.type='email' OR {items}.type='mobile_email') ".
				" AND ({items}.allow_email_reception_flag="._OFF." OR {users_items_link}.email_reception_flag="._ON.") ".
				" AND {users_items_link}.content!='' ";
			if($more_than_authority_id != null) {
				$sql .= "AND {authorities}.user_authority_id >= ".$more_than_authority_id;
			}
			$func = null;
			$func_params = null;
		}
		$params = array();
		$sql .= $this->_db->getWhereSQL($params, $where_params, false);
		$sql .= $this->_db->getOrderSQL($order_params);
		$result = $this->_db->execute($sql, $params, null, null, true, $func, $func_params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @param array  $func_param
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackSendMail($result, $func_param) {
		$ret = array();
		$more_than_authority_id = $func_param[0];
		if($more_than_authority_id === null) {
			$more_than_authority_id = _AUTH_GUEST;
		}
		$authorities =& $func_param[1];
		$items =& $func_param[2];
		$session =& $this->_container->getComponent("Session");

		while ($row = $result->fetchRow()) {
			if(!is_null($row["pages_users_role_authority_id"]) && $row["pages_users_role_authority_id"] == _AUTH_OTHER) {
				continue;
			}
			if($row["user_authority_id"] === null) {
				if($row["default_entry_flag"] != _ON) {
					continue;
				}
				if($row['private_flag'] == _ON) {
					$row["user_authority_id"] = $session->getParameter("_default_entry_auth_private");
				} elseif($row['space_type'] == _SPACE_TYPE_PUBLIC) {
					$row["user_authority_id"] = $session->getParameter("_default_entry_auth_public");
				} else {
					$row["user_authority_id"] = $session->getParameter("_default_entry_auth_group");
				}
				if($row['user_authority_id'] === null) {
					continue;
					//$row['user_authority_id'] = _AUTH_OTHER;
				} else if($authorities[$row['role_authority_id']]['user_authority_id'] == _AUTH_GUEST && $row['user_authority_id'] == _AUTH_GENERAL) {
					$row['user_authority_id'] = _AUTH_GUEST;
				}
			}
			$row['type'] = $items[$row['item_id']]['type'];
			if($row['user_authority_id'] >= $more_than_authority_id) {
				$ret[] = $row;
			}
		}
		return $ret;
	}

	/**
	 * itemを取得する
	 *
	 * @param   int   $item_id  項目ID
	 * @return array
	 * @access	public
	 */
	function &getItemById($item_id, $func=null, $func_param=null)
	{
		$session =& $this->_container->getComponent("Session");
		// 権限管理で会員管理で設定された権限を用いる(authorities_modules_linkのauthority_id)
		$_user_auth_id = $session->getParameter("_user_auth_id");
		$sql = "SELECT {items}.*, {items_authorities_link}.under_public_flag, {items_authorities_link}.self_public_flag,{items_authorities_link}.over_public_flag, ".
					" {items_desc}.description, {items_desc}.attribute, {items_options}.options, {items_options}.default_selected ".
					"FROM {items}  ".
					"LEFT JOIN {items_authorities_link} ON ({items}.item_id={items_authorities_link}.item_id AND {items_authorities_link}.user_authority_id=".intval($_user_auth_id).") ".
					"LEFT JOIN {items_desc} ON {items}.item_id={items_desc}.item_id ".
					"LEFT JOIN {items_options} ON {items}.item_id={items_options}.item_id ".
					"WHERE {items}.item_id=".intval($item_id);

		//$result =& $this->_db->selectExecute("items", array("item_id"=>intval($item_id)), null, 1, 0);
		$result = $this->_db->execute($sql, null, 1, 0, true,  $func, $func_param);
		if($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$result = false;
			$this->_db->addError();
			return $result;
		}
		if(!isset($result[0])) {
			return 	$result;
		}
		return $result[0];
	}

	/**
	 * itemを取得する
	 *
	 * @param   int   $item_id  項目ID
	 * @return array
	 * @access	public
	 */
	function &getItems($where_params=null, $order_params=null, $limit = null, $offset = null, $func=null, $func_param=null)
	{
		if($where_params != null && isset($where_params['user_authority_id'])) {
			$sql = "SELECT {items}.*,". 		//" {users_items_link}.user_id, {users_items_link}.content, ".
					" {items_authorities_link}.under_public_flag, {items_authorities_link}.self_public_flag,{items_authorities_link}.over_public_flag,{items_options}.options,{items_options}.default_selected ".
					" FROM {items}  ".
					//" LEFT JOIN {users_items_link} ON ({items}.item_id={users_items_link}.item_id)".
					" LEFT JOIN {items_authorities_link} ON ({items}.item_id={items_authorities_link}.item_id AND {items_authorities_link}.user_authority_id=".intval($where_params['user_authority_id']).") ".
					" LEFT JOIN {items_options} ON ({items}.item_id={items_options}.item_id)";
			unset($where_params['user_authority_id']);
		} else {
			$sql = "SELECT {items}.*, {users_items_link}.user_id, {users_items_link}.content,{items_options}.options,{items_options}.default_selected ".
					"FROM {items}  ".
					" LEFT JOIN {users_items_link} ON ({items}.item_id={users_items_link}.item_id)".
					" LEFT JOIN {items_options} ON ({items}.item_id={items_options}.item_id)";
		}
		$sql .= $this->_db->getWhereSQL($params, $where_params);
		$sql .= $this->_db->getOrderSQL($order_params);

		$result =$this->_db->execute($sql, $params, $limit, $offset, true,  $func, $func_param);
		if($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}


	/**
	 * ルームに所属するpages_users_linkテーブルを取得する
	 *
	 * @param  array    $room_id_arr  ルームID
	 * @param	array	 $where_params		キー名称配列、whereデータ配列
	 * @param	array    $order_params		        キー名称配列、orderデータ配列
	 * @param	integer	 $offset	取得し始めるレコードのオフセット
	 * @param	integer	 $limit	取得する件数
	 * @param	function $func	各レコード処理で実行させるメソッド
	 * @param	array	 $func_param	各レコード処理で実行させるメソッドの引数
	 * @return array
	 * @access	public
	 */
	function &getPagesUsersLinkByRoom($room_id_arr, $where_params=null, $order_params=null, $limit = null, $offset = null, $func=null, $func_param=null)
	{
		//if($func == null) $func = array($this, "_getPagesUsersLinkFetchcallback");
		$params = array();
		$sql = "SELECT {pages_users_link}.*,{authorities}.user_authority_id AS authority_id,{authorities}.hierarchy " .
						" FROM {pages_users_link} ".
				" LEFT JOIN {authorities} ON ({pages_users_link}.role_authority_id={authorities}.role_authority_id)".
				" WHERE room_id IN (". implode(",", $room_id_arr). ") ";
		$sql .= $this->_db->getWhereSQL($params, $where_params, false);
		//$sql .= " GROUP BY {pages_users_link}.user_id ";
		$sql .= $this->_db->getOrderSQL($order_params);

		$result =$this->_db->execute($sql, $params, $limit, $offset, true,  $func, $func_param);
		if($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * 検索結果一覧項目データをフェッチする
	 * 
	 * @param array $result 検索結果一覧項目データADORecordSet
	 * @return array items
	 * @access	private
	 */
	function _fetchSearchResultItem($result) {
		$ret = array();
		$ret_tags = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
			if(isset($row['tag_name']) && $row['tag_name'] !="") {
				switch ($row['tag_name']) {
					case "active_flag_lang":
						$tag_name = "active_flag";
						break;
					case "timezone_offset_lang":
						$tag_name = "timezone_offset";
						break;
					default :
						$tag_name = $row['tag_name'];
				}
				$ret_tags[$tag_name] = $row;
			}
		}
		return array($ret, $ret_tags);
	}

	/**
	 * 会員検索SQLのFROM句、および、WHERE句を作成する
	 *
	 * @param string $from_str FROM句文字列
	 * @param array $from_params FROM句パラメータ
	 * @param string $where_str WHERE句文字列
	 * @param array $where_params WHERE句パラメータ
	 * @param array $item 会員項目配列
	 * @param string $value 検索条件値
	 * @param string $suffix 複数選択肢用識別文字列
	 * @return void
	 * @access private
	 */
	function createSearchSqlParameter(&$from_str, &$from_params, &$where_str, &$where_params, $item, $value, $suffix = '') {
		if (strlen($value) == 0
			|| $item['tag_name'] == 'password'
			|| $item['tag_name'] == 'lang_dirname_lang') {
			return;
		}

		$itemTableAlias = 'I' . $item['item_id'] . $suffix;
		$userItemTableAlias = 'UI' . $item['item_id'] . $suffix;
		$itemAuthorityTableAlias = 'IA' . $item['item_id'] . $suffix;

		$from_str .= " INNER JOIN {items} " . $itemTableAlias . " "
						. "ON " . $itemTableAlias . ".item_id = ? "
					. "INNER JOIN {items_authorities_link} " . $itemAuthorityTableAlias . " "
						. "ON " . $itemAuthorityTableAlias . ".item_id = " . $itemTableAlias . ".item_id "
						. "AND " . $itemAuthorityTableAlias . ".user_authority_id = ? "
						. "AND (({users}.user_id != ? " 
									. "AND {authorities}.user_authority_id >= " . $itemAuthorityTableAlias . ".user_authority_id "
									. "AND " . $itemAuthorityTableAlias . ".over_public_flag != ?) "
							. "OR ({users}.user_id = ? "
									. "AND " . $itemAuthorityTableAlias . ".self_public_flag != ?) "
							. "OR ({users}.user_id != ? " 
									. "AND {authorities}.user_authority_id < " . $itemAuthorityTableAlias . ".user_authority_id "
									. "AND ". $itemAuthorityTableAlias . ".under_public_flag != ?)) ";
		$session =& $this->_container->getComponent('Session');
		$userId = $session->getParameter('_user_id');
		$userAuthorityId = $session->getParameter('_user_auth_id');
		$inputs = array(
			$item['item_id'],
			$userAuthorityId,
			$userId,
			USER_NO_PUBLIC,
			$userId,
			USER_NO_PUBLIC,
			$userId,
			USER_NO_PUBLIC
		);
		$from_params = array_merge($from_params, $inputs);

		if (strlen($item['tag_name']) > 0
			&& $this->isUsersTableField($item['tag_name']) != false) {
			switch ($item['tag_name']) {
				case "role_authority_name":
					$tag_name = "role_authority_id";
					$value_arr = explode("|", $value);
					$value = intval($value_arr[0]);
					
					$where_str .= " AND {users}.".$tag_name." = ?";
					$where_params[] = $value;
					break;
				case "active_flag_lang":
					$tag_name = "active_flag";
					$value_arr = explode("|", $value);
					$value = intval($value_arr[1]);
					
					$where_str .= " AND {users}.".$tag_name." = ?";
					$where_params[] = $value;
					break;
				case "insert_time":
				case "update_time":
				case "password_regist_time":
				case "last_login_time":
					$tag_name = $item['tag_name'];
					if($suffix == "_0") {
						// 日以上前 <=
						$operator = "<=";
					} else {
						// 日以内 >=
						$operator = ">=";
					}
					$value = intval($value);
					$time = timezone_date();
					$time = mktime(intval(substr($time, 8, 2)), intval(substr($time, 10, 2)), 
										intval(substr($time, 12, 2)), intval(substr($time, 4, 2)), 
										intval(substr($time, 6, 2)) - $value, intval(substr($time, 0, 4)));
					$time = date("YmdHis", $time);
					
					$where_str .= " AND {users}.".$tag_name." ".$operator." ?";
					$where_params[] = $time;
					
					break;
				default :
					$tag_name = $item['tag_name'];
					$where_str .= " AND {users}.".$tag_name." LIKE ?";
					$where_params[] = "%".$value."%";
			}
		} else {
			$from_str .= "INNER JOIN {users_items_link} " . $userItemTableAlias . " "
						. "ON " . $userItemTableAlias . ".item_id = " . $itemTableAlias . ".item_id "
						. "AND {users}.user_id = " . $userItemTableAlias . ".user_id ";
			$inputs = array();
			if ($userAuthorityId < _AUTH_ADMIN) {
				$from_str .= "AND ({users}.user_id = ? " 
							. "OR " . $itemTableAlias . ".allow_public_flag = ? "
							. "OR (" . $itemTableAlias . ".allow_public_flag = ? "
								. "AND " . $userItemTableAlias . ".public_flag = ?)) ";
				$inputs = array(
					$userId,
					_OFF,
					_ON,
					_ON
				);
			}
			$from_str .= "AND " . $userItemTableAlias . ".content "; 
			if ($item['type'] == 'select'
				|| $item['type'] == 'radio') {
				$from_str .= "= ? ";
				$searchValue = $value . '|';
			} else {
				$from_str .= "LIKE ? ";
				$searchValue = '%' . $value;
				if ($item['type'] == 'checkbox') {
					$searchValue .= '|%';
				} else {
					$searchValue .= '%';
				}
			}
			$from_params = array_merge($from_params, $inputs, array($searchValue));
		}

		// session登録処理
		// 検索条件をセッションに保存
		$sessionKey = array(
			'search',
			$item['item_id']
		); 
		if (strlen($suffix) > 0) {
			array_push($sessionKey, substr($suffix, 1));
		} 
		$actionChain =& $this->_container->getComponent('ActionChain');
		$actionName = $actionChain->getCurActionName();
		if ($actionName == 'user_action_main_search') {
			array_unshift($sessionKey, 'user');
		} elseif ($actionName == 'room_action_admin_search') {
			$request =& $this->_container->getComponent('Request');
			$roomCurrentId = $request->getParameter('room_current_id');
			$roomCurrentId = intval($roomCurrentId);
			array_unshift($sessionKey, 'room', $roomCurrentId);
		}
		$session->setParameter($sessionKey, $value);
	}

	/**
	 * 会員検索SQLのWHERE句を作成する
	 *
	 * @return  会員検索SQLのWHERE句文字列
	 * @access private
	 */
	function &createSearchWhereString() {
		$request =& $this->_container->getComponent('Request');
		$session =& $this->_container->getComponent('Session');
		$actionChain =& $this->_container->getComponent('ActionChain');

		$othersSearchAuthority = false;
		$actionName = $actionChain->getCurActionName();
		if ($session->getParameter('_user_auth_id') == _AUTH_ADMIN
			|| $actionName == 'room_action_admin_search') {
			$othersSearchAuthority = true;
		}

		$moduleName = explode('_', $actionName);
		$moduleName = $moduleName[0];
		$selectedRoomId = $request->getParameter('sel_room_id_list');
		$roomIds = $request->getParameter('room_id_arr');
		$rooms = $request->getParameter('room_arr_flat');

		$not_enroll_flag = false;
		if ($selectedRoomId == 'USER_NOT_ENROLL'
			&& $othersSearchAuthority) {
			// ルームに参加していない会員
			// 管理者の場合のみ
			// ルームに参加していない会員を求める
			$not_enroll_flag = true;
			// 検索条件をセッションに保存 item_id=0を使用
			$session->setParameter(array($moduleName, "search", 0), "USER_NOT_ENROLL");
		} else if($selectedRoomId != null && in_array( intval($selectedRoomId), $roomIds )) {
			//参加ルーム指定あり
			$roomIds = array();
			$roomIds[] = intval($selectedRoomId);
			// 検索条件をセッションに保存 item_id=0を使用
			$session->setParameter(array($moduleName, "search", 0), $selectedRoomId);
		} else if($othersSearchAuthority) {
			// 管理者　すべての会員
			$roomIds = null;
		}

		//
		// グループスペースのみセット
		//
		$groupRoomIds = null;
		if($roomIds != null){
			$groupRoomIds = array();
			foreach($roomIds as $room_id) {
				if ($rooms[$room_id]['space_type'] == _SPACE_TYPE_GROUP
					&& $rooms[$room_id]['private_flag'] == _OFF) {
					$groupRoomIds[] = $room_id;
				}
			}
		}

		$where_str = '';
		if (!isset($groupRoomIds)) {
			return $where_str;
		}

		// 自分のルームに参加している会員を取得
		// デフォルトで参加するルームが１つでもあればすべての会員から検索
		// 但し、pages_users_link-authority_idが_AUTH_OTHER(不参加)されたものは引く
		$default_entry_room_arr = array();
		$default_entry_flag = false;
		if($selectedRoomId != null && $selectedRoomId != "USER_NOT_ENROLL") {
			// 参加ルーム指定あり
			if(isset($rooms[$selectedRoomId]) && 
				$rooms[$selectedRoomId]['default_entry_flag'] == _ON &&
				$rooms[$selectedRoomId]['space_type'] == _SPACE_TYPE_GROUP &&
				$rooms[$selectedRoomId]['private_flag'] == _OFF) {
				// デフォルトで参加しているグループルームあり
				$default_entry_room_arr[$rooms[$selectedRoomId]['page_id']] = $rooms[$selectedRoomId]['page_id'];
				$default_entry_flag = true;
			}
		} else {
			foreach($rooms as $room) {
				if ($room['default_entry_flag'] == _ON
					&& $room['space_type'] == _SPACE_TYPE_GROUP
					&& $room['private_flag'] == _OFF
					&& $room['page_id'] != _SELF_TOPGROUP_ID) {
					// デフォルトで参加しているグループルーム
					$default_entry_room_arr[$room['page_id']] = $room['page_id'];
					$default_entry_flag = true;
				}
			}
		}

		$pages_users_id_arr =& $this->getPagesUsersLinkByRoom($groupRoomIds, null,null,null, null, array($this, '_fetchRoomUser'), array($not_enroll_flag, $default_entry_flag, $default_entry_room_arr));
		// ルームに参加していない会員
		$sel_default_entry_flag = $default_entry_flag;
		if($not_enroll_flag) {
			$sel_default_entry_flag = ($default_entry_flag === true) ? false : true;
		}
		if($sel_default_entry_flag) {
			// 不参加者を省く
			if(!empty($pages_users_id_arr)) {
				$where_str = " AND {users}.user_id NOT IN ('". implode("','", $pages_users_id_arr). "') ";
			}
		} else {
			// 自分のルームに参加している会員を取得
			// 自分自身は必ず検索結果に含める
			if ($selectedRoomId == null
				&& $actionName == 'user_action_main_search') {
				$pages_users_id_arr[] = $session->getParameter("_user_id");
			}
			$where_str = " AND {users}.user_id IN ('". implode("','", $pages_users_id_arr). "') ";
		}

		return $where_str;
	}

	/**
	 * ルーム参加者、不参加者データをフェッチする
	 * 
	 * @param array $result ルーム参加者、不参加者データADORecordSet
	 * @param boolean $func_params[0] ルーム不参加者対象フラグ
	 * @param boolean $func_params[1] デフォルト参加ルーム有無フラグ
	 * @param array $func_params[2] デフォルト参加ルームID配列
	 * @return array ルーム参加者 または 不参加者ID配列
	 * @access	private
	 */
	function &_fetchRoomUser($result, $func_params)
	{
		$not_enroll_flag = $func_params[0];
		$default_entry_flag = $func_params[1];
		$default_entry_room_arr =& $func_params[2];

		$presence_users = array();	//参加会員
		$absence_users = array();	//不参加会員
		$absence_rooms = array();	//不参加会員がいるルームID配列
		while ($row = $result->fetchRow()) {
			if ($row['role_authority_id'] == _ROLE_AUTH_OTHER) {
				//不参加会員
				$absence_users[$row['user_id']] = $row['user_id'];
				$absence_rooms[$row['room_id']][] = $row['user_id'];
			} else {
				$presence_users[$row['user_id']] = $row['user_id'];
			}
		}
		if (!$default_entry_flag) {
			return $presence_users;
		}

		$noParticipationRoomUsers = array();
		foreach ($default_entry_room_arr as $room_id) {
			// 不参加会員が誰もいないデフォルト参加ルームがある場合は全ての会員から検索する
			if (!isset($absence_rooms[$room_id])) {
				$absence_users = array();
				return $absence_users;
			}

			if (empty($noParticipationRoomUsers)) {
				$noParticipationRoomUsers = $absence_rooms[$room_id];
			} 
			$noParticipationRoomUsers = array_intersect($noParticipationRoomUsers, $absence_rooms[$room_id]);
		}

		// 参加会員配列にいない不参加会員を取得
		$absence_users = array_diff($noParticipationRoomUsers, $presence_users);

		// 不参加者を返す
		return $absence_users;
	}

	/**
	 * メールアドレスからユーザーIDを取得する
	 *
	 * @param string $mail メールアドレス
	 * @param boolean $isActive 利用可能ユーザー対象フラグ
	 * @return ユーザーID
	 * @access private
	 */
	function &getUserIdByMail($mail, $isActive = false) {
		$userId = null;
		if (empty($mail)) {
			return $userId;
		}

		$sql = "SELECT UI.user_id "
			. "FROM {items} I "
			. "INNER JOIN {users_items_link} UI "
				. "ON I.item_id = UI.item_id ";

		if ($isActive) {
			$sql .= "INNER JOIN {users} U "
					. "ON UI.user_id = U.user_id ";
		}

		$sql .= "WHERE (I.type = ? "
					. "OR I.type = ?) "
				. "AND UI.content = ? ";

		$bindValues = array(
			USER_TYPE_EMAIL,
			USER_TYPE_MOBILE_EMAIL,
			$mail
		);

		if ($isActive) {
			$sql .= "AND U.active_flag = ? ";
			$bindValues[] = _USER_ACTIVE_FLAG_ON;
		}

		$users = $this->_db->execute($sql, $bindValues);
		if ($users === false) {
			$this->_db->addError();
		}

		if (!empty($users)) {
			$userId = $users[0]['user_id'];
		}

		return $userId;
	}
}
?>