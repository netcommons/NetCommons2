<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Components_View
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
	 * @var ページ
	 *
	 * @access	private
	 */


	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Multidatabase_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 権限判断用のSQL文FROM句を取得する
	 *
     * @return string	権限判断用のSQL文FROM句
	 * @access	public
	 */
	function &_getAuthorityFromSQL() {
		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");

		$sql = "";
		if ($auth_id >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "LEFT JOIN {pages_users_link} PU ".
					"ON {multidatabase_content}.insert_user_id = PU.user_id ".
					"AND {multidatabase_content}.room_id = PU.room_id ";
		$sql .= "LEFT JOIN {authorities} A ".
					"ON A.role_authority_id = PU.role_authority_id ";
		return $sql;
	}

	/**
	 * 権限判断用のSQL文WHERE句を取得する
	 * パラメータ用配列に必要な値を追加する
	 *
	 * @param	array	$params	パラメータ用配列
     * @return string	権限判断用のSQL文WHERE句
	 * @access	public
	 */
	function &_getAuthorityWhereSQL(&$params) {

		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");

		$sql = "";
		if ($auth_id >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "AND (({multidatabase_content}.temporary_flag = ? AND {multidatabase_content}.agree_flag = ?) OR A.hierarchy < ? OR {multidatabase_content}.insert_user_id = ?";

		$defaultEntry = $session->getParameter("_default_entry_flag");

		$hierarchy = $session->getParameter("_hierarchy");

		if ($defaultEntry == _ON && $hierarchy > $session->getParameter("_default_entry_hierarchy")) {
			$sql .= " OR A.hierarchy IS NULL) ";
		} else {
			$sql .= ") ";
		}

		//$request =& $this->_container->getComponent("Request");
		//$params[] = $request->getParameter("room_id");
		$params[] = MULTIDATABASE_STATUS_RELEASED_VALUE;
		$params[] = MULTIDATABASE_STATUS_AGREE_VALUE;
		$params[] = $hierarchy;
		$params[] = $session->getParameter("_user_id");

		return $sql;
	}

	/**
	 * 汎用データベースが配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock() {
		$request =& $this->_container->getComponent("Request");
		$params = array($request->getParameter("multidatabase_id"));
		$sql = "SELECT M.room_id, B.block_id ".
				"FROM {multidatabase} M ".
				"INNER JOIN {multidatabase_block} B ".
				"ON M.multidatabase_id = B.multidatabase_id ".
				"WHERE M.multidatabase_id = ? ".
				"ORDER BY B.block_id";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0];
	}

	/**
	 * 在配置されている汎用データベースIDを取得する
	 *
     * @return string	配置されている汎用データベースID
	 * @access	public
	 */
	function &getCurrentMdbId() {
		$request =& $this->_container->getComponent("Request");
		$params = array($request->getParameter("block_id"));
		$sql = "SELECT multidatabase_id ".
				"FROM {multidatabase_block} ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0]["multidatabase_id"];
	}

	/**
	 * 汎用データベースが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function mdbExists() {
		$request =& $this->_container->getComponent("Request");
		$params = array(
			$request->getParameter("multidatabase_id"),
			$request->getParameter("room_id")
		);
		$sql = "SELECT multidatabase_id ".
				"FROM {multidatabase} ".
				"WHERE multidatabase_id = ? ".
				"AND room_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		if (count($result) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * 汎用データベース用デフォルトデータを取得する
	 *
     * @return array	汎用データベース用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultMdb() {
		$configView =& $this->_container->getComponent("configView");
		$request =& $this->_container->getComponent("Request");
		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
        	return $config;
        }

		$mdb = array(
			"active_flag" => constant($config["active_flag"]["conf_value"]),
			"mail_flag" => constant($config["mail_flag"]["conf_value"]),
			"contents_authority" => constant($config["contents_authority"]["conf_value"]),
			"new_period" => $config["new_period"]["conf_value"],
			"vote_flag" => constant($config["vote_flag"]["conf_value"]),
			"comment_flag" => constant($config["comment_flag"]["conf_value"]),
			"agree_flag" => constant($config["agree_flag"]["conf_value"]),
			"agree_mail_flag" => constant($config["agree_mail_flag"]["conf_value"]),
			"visible_item" => $config["visible_item"]["conf_value"],
			"default_sort" => constant($config["default_sort"]["conf_value"])
		);

		return $mdb;
	}

	/**
	 * 汎用データベースデータを取得する
	 *
     * @return array	汎用データベースデータ配列
	 * @access	public
	 */
	function &getMdb() {
		$request =& $this->_container->getComponent("Request");
		$configView =& $this->_container->getComponent("configView");

		$sql = "SELECT multidatabase_id, multidatabase_name, ";
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "multidatabase_view_edit_create" ||
				$actionName == "multidatabase_action_edit_create" || $actionName == "multidatabase_view_edit_modify") {
			$sql .= "active_flag, mail_flag, mail_authority, mail_subject, mail_body, contents_authority, new_period, vote_flag, ".
							"comment_flag, agree_flag, agree_mail_flag, agree_mail_subject, agree_mail_body, title_metadata_id ";
		} else {
			$prefix_id_name = $request->getParameter("prefix_id_name");
			if ($prefix_id_name == MULTIDATABASE_REFERENCE_PREFIX_NAME.$request->getParameter("multidatabase_id")) {
				$sql .= _OFF . " AS active_flag";
			} else {
				$sql .= "active_flag";
			}
			$sql .= ", mail_flag, mail_authority, vote_flag, comment_flag, new_period, agree_flag, agree_mail_flag, title_metadata_id ";
		}

		$params = array($request->getParameter("multidatabase_id"));
		$sql .=	"FROM {multidatabase} ".
				"WHERE multidatabase_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
        	return $config;
        }

        $result[0]['visible_item'] = $config["visible_item"]["conf_value"];
        $result[0]['default_sort'] = constant($config["default_sort"]["conf_value"]);

		return $result[0];
	}

	/**
	 * 現在配置されている汎用データベースデータを取得する
	 *
     * @return array	配置されている汎用データベースデータ配列
	 * @access	public
	 */
	function &getCurrentMdb() {
		$request =& $this->_container->getComponent("Request");

		$params = array($request->getParameter("block_id"));
		$sql = "SELECT B.block_id, B.multidatabase_id, B.visible_item, B.default_sort, ".
					"M.multidatabase_name, M.active_flag, M.contents_authority, M.new_period, M.mail_flag, M.mail_authority, ".
					"M.mail_subject, M.mail_body, M.vote_flag, M.comment_flag, M.agree_flag, M.agree_mail_flag, ".
					"M.agree_mail_subject, M.agree_mail_body,M.title_metadata_id ".
				"FROM {multidatabase_block} B ".
				"INNER JOIN {multidatabase} M ".
				"ON B.multidatabase_id = M.multidatabase_id ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$result[0]['post_auth'] = $this->_hasPostAuthority($result[0]);
		$result[0]['new_period_time'] = $this->_getNewPeriodTime($result[0]["new_period"]);

		return $result[0];
	}

	/**
	 * コンテンツ投稿権限を取得する
	 *
	 * @param	array	$bbs	汎用データベース状態、表示方法、コンテンツ投稿権限の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasPostAuthority($mdb) {
		if ($mdb["active_flag"] != _ON) {
			return false;
		}

		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");
		if ($auth_id >= $mdb["contents_authority"]) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDの汎用データベース件数を取得する
	 *
     * @return string	汎用データベース件数
	 * @access	public
	 */
	function getMdbCount() {
		$request =& $this->_container->getComponent("Request");
    	$params["room_id"] = $request->getParameter("room_id");
    	$count = $this->_db->countExecute("multidatabase", $params);

		return $count;
	}

	/**
	 * 汎用データベース一覧データを取得する
	 *
     * @return array	汎用データベース一覧データ配列
	 * @access	public
	 */
	function &getMdbs() {
		$request =& $this->_container->getComponent("Request");
		$limit = $request->getParameter("limit");
		$offset = $request->getParameter("offset");

		$sortColumn = $request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "multidatabase_id";
		}
		$sortDirection = $request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($request->getParameter("room_id"));
		$sql = "SELECT multidatabase_id, multidatabase_name, active_flag, insert_time, insert_user_id, insert_user_name ".
				"FROM {multidatabase} ".
				"WHERE room_id = ? ".
				$this->_db->getOrderSQL($orderParams);
		$result = $this->_db->execute($sql, $params, $limit, $offset);
		if ($result === false) {
			$this->_db->addError();
		}

		return $result;
	}

    /**
	 * metadataを取得する
	 *
	 * @param   int   $metadata_id  項目ID
	 * @return array
	 * @access	public
	 */
	function &getMetadataById($metadata_id)	{
		$result =& $this->_db->selectExecute("multidatabase_metadata", array("metadata_id"=>intval($metadata_id)), null, 1, 0);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0];
	}

	/**
	 * 汎用データベースobjectを取得する
	 *
	 * @param   int   $multidatabase_id
	 * @return array
	 * @access	public
	 */
	function &getMdbById($multidatabase_id)
	{
		$result =& $this->_db->selectExecute("multidatabase", array("multidatabase_id"=>intval($multidatabase_id)));
		if($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0];
	}

	/**
	 *
	 * メタデータ情報取得取得
	 * @param array params
	 * @param array $order_params
	 * @return array
	 */
	function &getMetadatas($params, $order_params = null) {
		if(!isset($params)) {
			return false;
		}

		if(!isset($order_params)) {
			$order_params = array(
				"display_pos" => "ASC",
	        	"display_sequence" => "ASC"
	        );
		}

		$result = $this->_db->selectExecute("multidatabase_metadata", $params, $order_params, null, null, array($this,"_getMetadatasFetchcallback"));
		if ( $result === false ) {
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
	function &_getMetadatasFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if($row['type'] == MULTIDATABASE_META_TYPE_SECTION 
				|| $row['type'] == MULTIDATABASE_META_TYPE_MULTIPLE) {
				$options = explode("|", $row['select_content']);
				$count = 0;
				foreach($options as $option) {
					$row['select_content_array'][$count] = $option;
					$count++;
				}
			}
			$ret[$row['metadata_id']] = $row;
		}
		return $ret;
	}

	function &getLayout($params) {
		if(!is_array($params)) {
			return false;
		}

		$result = array();
		$pos_1 = $this->getMetadatas(array_merge($params, array("display_pos" => 1)));
    	if($pos_1 === false) {
    		$this->_db->addError();
			return $pos_1;
    	}
		$pos_2 = $this->getMetadatas(array_merge($params, array("display_pos" => 2)));
    	if($pos_2 === false) {
    		$this->_db->addError();
			return $pos_2;
    	}
    	$pos_3 = $this->getMetadatas(array_merge($params, array("display_pos" => 3)));
    	if($pos_3 === false) {
    		$this->_db->addError();
			return $pos_3;
    	}
    	$pos_4 = $this->getMetadatas(array_merge($params, array("display_pos" => 4)));
    	if($pos_4 === false) {
    		$this->_db->addError();
			return $pos_4;
    	}

    	$result[1] = $pos_1;
    	$result[2] = $pos_2;
    	$result[3] = $pos_3;
    	$result[4] = $pos_4;

    	return $result;
	}

	/**
	 * 入力データ用SQLを取得する
	 *
     * @return array	入力データ用SQL
	 * @access	public
	 */
	function &_getDataSQL($metadatas, $add_select= "") {
		$select = "";
		$join = "";
		foreach ($metadatas as $key => $metadata) {
			$alias = "m_content". $metadata['metadata_id'];
			$select .= ", ". $alias. ".content as content". $metadata['metadata_id']. " ";
			$join .= " LEFT JOIN {multidatabase_metadata_content} ". $alias. " ".
						" ON {multidatabase_content}.content_id = ". $alias. ".content_id ".
						" AND ". $alias. ".metadata_id = ". $metadata['metadata_id']. " ";

			if ($metadata['type'] == MULTIDATABASE_META_TYPE_FILE || $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) {
				$select .= ", F".$metadata['metadata_id'].".upload_id as upload_id". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".file_name as file_name". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".physical_file_name as physical_file_name". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".file_password as file_password". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".download_count as download_count". $metadata['metadata_id']. " ";
				$join .= " LEFT JOIN {multidatabase_file} F".$metadata['metadata_id'].
							" ON ". $alias. ".metadata_content_id = F".$metadata['metadata_id'].".metadata_content_id ";
			}
		}

		$sql = "SELECT {multidatabase}.mail_authority, {multidatabase}.mail_subject,{multidatabase}.mail_body,".
				"{multidatabase}.multidatabase_name,{multidatabase_content}.content_id,".
				"{multidatabase_content}.insert_time,{multidatabase_content}.vote, {multidatabase_content}.vote_count,".
				"{multidatabase_content}.insert_user_id,{multidatabase_content}.insert_user_name,".
				"{multidatabase_content}.update_time,{multidatabase_content}.update_user_id,{multidatabase_content}.update_user_name," .
				"{multidatabase_content}.agree_flag,{multidatabase_content}.temporary_flag ". $select.$add_select.
				" FROM {multidatabase} ".
				"INNER JOIN {multidatabase_content} ".
				"ON {multidatabase}.multidatabase_id = {multidatabase_content}.multidatabase_id ". $join;

		return $sql;
	}

	/**
	 * 一覧数取得
	 * @param string $multidatabase_id 汎用データベースID
	 * @param array $metadatas メタデータ項目配列
	 * @param array $where_params 絞込み文字列配列
	 * @param string $wheresql キーワード検索条件WHERE句文字列
	 * @param array $keywordBindValues 検索キーワードデータ配列
	 * @return コンテンツ件数
	 */
	function &getMDBListCount($multidatabase_id, $metadatas, $where_params, $wheresql = '', $keywordBindValues = array()) {
		$sql = "";
		if(!empty($metadatas)){
			$sql = "SELECT count({multidatabase_content}.content_id) list_count";
			$sql_join = "";
			$sql_where = "";

			foreach (array_keys($metadatas) as $i) {
				$key = 'm_content' . $i . '.content';
				if ((!isset($where_params[$key])
						|| !strlen($where_params[$key]))
					&& $wheresql == "") {
					continue;
				}

				$sql_join .= "LEFT JOIN {multidatabase_metadata_content} m_content".$metadatas[$i]['metadata_id'];
				$sql_join .= " ON ({multidatabase_content}.content_id = m_content".$metadatas[$i]['metadata_id'].".content_id";
				$sql_join .= " AND m_content".$metadatas[$i]['metadata_id'].".metadata_id = ". $metadatas[$i]['metadata_id'].") ";
			}

			list($sql_where, $params) = $this->_getSqlContentWhereStatement($multidatabase_id, $where_params, $keywordBindValues);

			$sql .= " FROM {multidatabase_content} ";
			$sql .= $sql_join;
			$sql .= $this->_getAuthorityFromSQL();
			$sql .= $sql_where;
			$sql .= $wheresql;
			$sql .= $this->_getAuthorityWhereSQL($params);
		}

		$result = $this->_db->execute($sql, $params);
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}

		$count = $result[0]['list_count'];
		return $count;
	}

	/**
	 * 一覧取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMDBList($multidatabase_id, $metadatas, $where_params, $order_params, $disp_cnt=0, $begin=0) {
		$sql = "";
		if(!empty($metadatas)){
			list($sql_where, $params) = $this->_getSqlContentWhereStatement($multidatabase_id, $where_params);

			$sql .= $this->_getDataSQL($metadatas, ',URL.short_url');
			$sql .= $this->_getAuthorityFromSQL();
			$sql .= $this->_getAbbreviateUrlJoinStatement();
			$sql .= $sql_where;
			$sql .= $this->_getAuthorityWhereSQL($params);
			$sql .= $this->_db->getOrderSQL($order_params);
		}

		$result = $this->_db->execute($sql, $params ,$disp_cnt, $begin, true, array($this,"_getMDBListFetchcallback"));
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	function getImageBlockSize($file_name, $display_pos=null) {
		$real_size = array();
		if($display_pos == "1" || $display_pos == "4") {
			$width = MULTIDATABASE_IMAGE_LIST_WIDTH;		// 画像サイズ(幅)
			$height = MULTIDATABASE_IMAGE_LIST_HEIGHT;	// 画像サイズ(高さ)
		}else {
			$width = MULTIDATABASE_IMAGE_DEF_LIST_WIDTH;		// 画像サイズ(幅)
			$height = MULTIDATABASE_IMAGE_DEF_LIST_HEIGHT;	// 画像サイズ(高さ)
		}
		$image_size = getimagesize(FILEUPLOADS_DIR."multidatabase/".$file_name);
		if($image_size[0] <= $width) {
			return $image_size;
		}
		$rate_num = (float)($image_size[1]/$image_size[0]);
		$height = intval((float)($width*$rate_num));
		$real_size[0] = $width;
		$real_size[1] = $height;
		return $real_size;
	}

	function &getFileLink($url, $file_name, $physical_file_name, $metadata, $extent_url=null, $insert_user_id=null, $file_password=null) {
		$value = "";

		$session =& $this->_container->getComponent("Session");
		$mobile_flag = $session->getParameter("_mobile_flag");
		$smartphone_flag = $session->getParameter("_smartphone_flag");

		if(!empty($url) && strpos($url, "upload_id=") != false) {
			if($metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) {
				$size = $this->getImageBlockSize($physical_file_name, $metadata['display_pos']);

				if ($mobile_flag == _ON) {
					if($smartphone_flag == _ON) {
						$w = '&amp;w=' . MULTIDATABASE_IMAGE_DEF_SMARTPHONE_WIDTH;
					} else {
						$w = '';
					}
					$value = "<a target='_blank' href='" . htmlspecialchars($extent_url.$url."&metadata_id=".$metadata['metadata_id']."&download_flag="._ON, ENT_QUOTES) . $w . "&amp;" . session_name() . "=" . session_id() . "'" . ' data-ajax="false"  data-rel="dialog" ' . "><img src='". htmlspecialchars($extent_url.$url."&metadata_id=".$metadata['metadata_id']."&download_flag="._ON, ENT_QUOTES) . "&amp;w=" . MULTIDATABASE_IMAGE_DEF_MOBILE_LIST_WIDTH . "&amp;" . session_name() . "=" . session_id() . "' title='".$file_name."' alt='".$file_name."' /></a>";
				} else{
					$value = "<a href='#' onclick=\"commonCls.showPopupImageFullScale(this); return false;\"><img src='".htmlspecialchars($extent_url.$url."&metadata_id=".$metadata['metadata_id']."&download_flag="._ON, ENT_QUOTES)."' title='".$file_name."' alt='".$file_name."' style='height:".$size[1]."px;width:".$size[0]."px;padding:10px;' /></a>";
				}
			}else if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE) {
				$session =& $this->_container->getComponent("Session");
				$user_id = $session->getParameter("_user_id");
				$auth_id = $session->getParameter("_auth_id");
				$pathList = explode("&", $url);
				$upload_id = intval(str_replace("upload_id=","", $pathList[1]));
				$id = $session->getParameter("_id");
				if(!empty($file_password) && $metadata['file_password_flag'] == _ON) {
					$request =& $this->_container->getComponent("Request");
					$block_id = $request->getParameter("block_id");
					$multidatabase_id = $request->getParameter("multidatabase_id");
					$room_id = $request->getParameter("room_id");
					if ($mobile_flag == _ON) {
						$filepwd_nonform = $request->getParameter("filepwd_nonform");
						if (isset($filepwd_nonform) && $filepwd_nonform == _ON) {
							//formに「しない」指定あり。
							//コンテンツ編集画面なので、<form>の必要なし。逆にformにすると、formの入れ子になり画面がくずれる。
							//
							$value = "<a href='". $extent_url . $url . "&amp;" . session_name() . "=" . session_id() . "' >".$file_name."</a>";
						} else {
							//登録した本人、あるいは主担以上の権限者ならfile_passwordをセットして表示する。
							$file_password_val = ($insert_user_id == $user_id || $auth_id >= _AUTH_CHIEF ) ? $file_password : "";

							$filterChain =& $this->_container->getComponent("FilterChain");
							$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
							$btn_val = sprintf($smartyAssign->getLang("_ref"));

							$token =& $this->_container->getComponent("Token");
							$token_name = "_token";		//nccore/TokenExtra.class.phpのcheck()が_token固定に変更されているので合わせた。
							$token_val = $token->getValue();
							$metadataId = $metadata['metadata_id'];
							if($smartphone_flag == _ON) {
								$w = '<input type="hidden" name="w" value="' . MULTIDATABASE_IMAGE_DEF_SMARTPHONE_WIDTH . '" />';
							} else {
								$w = '';
							}
							$value =  '<form action=".' . INDEX_FILE_NAME . '" method="get" data-ajax="false">'
									. "<input type='hidden' name='action' value='multidatabase_action_main_filedownload' >"
									. "<input type='hidden' name='upload_id' value='{$upload_id}' >"
									. "<input type='hidden' name='metadata_id' value='{$metadataId}' >"
									. "<input type='hidden' name='download_flag' value='1' >"
									. $w
									. "<input type='hidden' name='". session_name() . "' value='" . session_id() . "' >"
									. $file_name . "<br />"
									. sprintf( MULTIDATABASE_MOBILE_FILE_PASSWORD_INPUT, $btn_val )
									. "<input type='password' size='10' maxlength='100' name='password' value='{$file_password_val}' >"
									. "<input type='submit' name='ref' value='{$btn_val}' >"
									. "</form>";
						}
					} else {
						$value = "<a href='#' onclick=\"commonCls.sendPopupView(event,{action:'multidatabase_view_main_filepassword', block_id:'".$block_id."', upload_id:'".$upload_id."', metadata_id:'".$metadata['metadata_id']."', insert_user_id:'".$insert_user_id."', prefix_id_name:'mdb_popup_password'}, {'modal_flag':true});return false;\">".$file_name."</a>";
					}
				} else {
					if ($mobile_flag == _ON) {
						if($smartphone_flag == _ON) {
							$w = '&amp;w=' . MULTIDATABASE_IMAGE_DEF_SMARTPHONE_WIDTH;
						} else {
							$w = '';
						}
						$value = "<a target='_blank' href='". $extent_url . "?action=multidatabase_action_main_filedownload&amp;download_flag=1&amp;upload_id=" . $upload_id ."&amp;metadata_id=". $metadata['metadata_id'] . $w . "&amp;" . session_name() . "=" . session_id() . "'" . ' data-ajax="false">' . $file_name. " </a>";
					} else {
						if(!empty($extent_url)) {
							$value = "<a href='".$extent_url."?action=multidatabase_action_main_filedownload&amp;download_flag="._ON."&amp;upload_id=".$upload_id."&amp;metadata_id=".$metadata['metadata_id']."' target='_blank'>".$file_name."</a>";
						}else {
							$value = "<a href='?action=multidatabase_action_main_filedownload&amp;download_flag="._ON."&amp;upload_id=".$upload_id."&amp;metadata_id=".$metadata['metadata_id']."' onclick=\"mdbCls['".$id."'].setDownloadCount('".$upload_id."');\">".$file_name."</a>";
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getMDBListFetchcallback($result) {
		$session =& $this->_container->getComponent("Session");
		$request =& $this->_container->getComponent("Request");
		$metadatas = $request->getParameter("metadatas");

		$data = array();
		while ($row = $result->fetchRow()) {
			$layout = array();
			$items = array();
			foreach ($metadatas as $metadata) {
				if($metadata['list_flag'] == _ON) {
					$layout[$metadata['display_pos']][$metadata['metadata_id']] = $metadata;
				}
				if($metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE || $metadata['type'] == MULTIDATABASE_META_TYPE_FILE) {
					$items[$metadata['metadata_id']] = $this->getFileLink($row['content'.$metadata['metadata_id']],
																			$row['file_name'.$metadata['metadata_id']],
																			$row['physical_file_name'.$metadata['metadata_id']],
																			$metadata, null, $row['insert_user_id'], $row['file_password'.$metadata['metadata_id']]);
					unset($row['file_name'.$metadata['metadata_id']]);
					unset($row['physical_file_name'.$metadata['metadata_id']]);
				}elseif ($metadata['type'] == MULTIDATABASE_META_TYPE_MULTIPLE) {
					$itemArr = explode("|",$row['content'.$metadata['metadata_id']]);
					$multiple = array();
					foreach ($itemArr as $val) {
						$multiple[] = $val;
					}
					$items[$metadata['metadata_id']] = $multiple;
				}else {
					$items[$metadata['metadata_id']] = $row['content'.$metadata['metadata_id']];
				}
				unset($row['content'.$metadata['metadata_id']]);
			}

			foreach($row as $key => $val) {
				$items[$key] = $val;
			}
			$voted = false;
			if($items['vote'] != "") {
				$who_voted = explode(",", $items['vote']);
				$user_id = $session->getParameter("_user_id");
				if(empty($user_id)) {
					$votes = $session->getParameter("multidatabase_votes");
					if(!empty($votes)) {
						if(in_array($items['content_id'], $votes)) {
							$voted = true;
						}
					}
				}else if (in_array($user_id, $who_voted)) {
					$voted = true;
				}
			}
			$items['voted'] = $voted;

			$data['metadata'] = $layout;
			$data['value'][] = $items;
		}
		return $data;
	}

	/**
	 * 汎用ＤＢを取得する
	 *
	 * @param	int photoalbum_id
     * @return 　array
	 * @access	public
	 */
	function &getMultidatabase($multidatabase_id) {
		$result = $this->_db->selectExecute("multidatabase", array("multidatabase_id" => intval($multidatabase_id)));
		if($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * new記号表示期間から対象年月日を取得する
	 *
	 * @param	string	$new_period		new記号表示期間
     * @return string	new記号表示対象年月日(YmdHis)
	 * @access	public
	 */
	function &_getNewPeriodTime($new_period) {
		if (empty($new_period)) {
			$new_period = -1;
		}

		$time = timezone_date();
		$time = mktime(0, 0, 0,
						intval(substr($time, 4, 2)),
						intval(substr($time, 6, 2)) - $new_period,
						intval(substr($time, 0, 4))
						);
		$time = date("YmdHis", $time);

		return $time;
	}

	/**
	 * コンテンツの詳細取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMDBDetail($content_id, $metadatas) {
		$sql = "";

		$params = array(
			"content_id" => $content_id
		);

		if(!empty($metadatas)){
			$sql .= $this->_getDataSQL($metadatas);
			$sql .= $this->_getAuthorityFromSQL();
			$sql .= " WHERE {multidatabase_content}.content_id=? ";
			$sql .= $this->_getAuthorityWhereSQL($params);
		}

		$result = $this->_db->execute($sql, $params ,null, null, true, array($this,"_getMDBDetailFetchcallback"));
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
	function &_getMDBDetailFetchcallback($result) {
		$session =& $this->_container->getComponent("Session");
		$request =& $this->_container->getComponent("Request");
		$metadatas = $request->getParameter("metadatas");

		$data = array();
		while ($row = $result->fetchRow()) {
			$layout = array();
			$items = array();
			foreach ($metadatas as $metadata) {
				if($metadata['detail_flag'] == _ON) {
					$layout[$metadata['display_pos']][$metadata['metadata_id']] = $metadata;
				}
				if($metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE || $metadata['type'] == MULTIDATABASE_META_TYPE_FILE) {
					$items[$metadata['metadata_id']] = $this->getFileLink($row['content'.$metadata['metadata_id']],
																			$row['file_name'.$metadata['metadata_id']],
																			$row['physical_file_name'.$metadata['metadata_id']],
																			$metadata, null, $row['insert_user_id'], $row['file_password'.$metadata['metadata_id']]);
					unset($row['file_name'.$metadata['metadata_id']]);
					unset($row['physical_file_name'.$metadata['metadata_id']]);
				}elseif ($metadata['type'] == MULTIDATABASE_META_TYPE_MULTIPLE) {
					$itemArr = explode("|",$row['content'.$metadata['metadata_id']]);
					$multiple = array();
					foreach ($itemArr as $val) {
						$multiple[] = $val;
					}
					$items[$metadata['metadata_id']] = $multiple;
				}else {
					$items[$metadata['metadata_id']] = $row['content'.$metadata['metadata_id']];
				}
				unset($row['content'.$metadata['metadata_id']]);
			}

			$voted = false;
			$comment_count = 0;
			$comments = "";
			foreach($row as $key => $val) {
				$items[$key] = $val;
			}
			$items['has_edit_auth'] = $this->_hasEditAuthority($items['insert_user_id']);
			$items['has_confirm_auth'] = $this->_hasConfirmAuthority();

			$comment_count = $this->_db->countExecute("multidatabase_comment", array("content_id" => $items['content_id']));
			$order_params = array(
				"insert_time" => "ASC"
			);
			$comments = $this->_db->selectExecute("multidatabase_comment",array("content_id" => $items['content_id']), $order_params);
			if($comments === false) {
	    		return 'error';
	    	}
	    	foreach($comments as $key => $val) {
	    		$edit_auth = $this->_hasEditAuthority($val['insert_user_id']);
	    		$comments[$key]['edit_auth'] = $edit_auth;
	    	}
	    	$items['comment_count'] = $comment_count;
	    	$items['comments'] = $comments;

	    	if($items['vote'] != "") {
				$who_voted = explode(",", $items['vote']);
				$user_id = $session->getParameter("_user_id");
				if(empty($user_id)) {
					$votes = $session->getParameter("multidatabase_votes");
					if(!empty($votes)) {
						if(in_array($items['content_id'], $votes)) {
							$voted = true;
						}
					}
				}else if (in_array($user_id, $who_voted)) {
					$voted = true;
				}
			}
			$items['voted'] = $voted;

			$data['metadata'] = $layout;
			$data['value'] = $items;
		}
		return $data;
	}

	/**
	 * コンテンツの編集データ取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMdbEditData($content_id, $metadatas) {
		$sql = "";
		if(!empty($metadatas)){
			$sql .= $this->_getDataSQL($metadatas);
			$sql .= " WHERE {multidatabase_content}.content_id=? ";
		}

		$params = array(
			"content_id" => $content_id
		);

		$result = $this->_db->execute($sql, $params ,null, null, true, array($this,"_getMdbEditDataFetchcallback"));
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
	function &_getMdbEditDataFetchcallback($result) {
		$request =& $this->_container->getComponent("Request");
		$metadatas = $request->getParameter("metadatas");

		$data = array();
		while ($row = $result->fetchRow()) {
			$layout = array();
			$items = array();
			foreach ($metadatas as $metadata) {
				$layout[$metadata['display_pos']][$metadata['metadata_id']] = $metadata;
				if($metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE || $metadata['type'] == MULTIDATABASE_META_TYPE_FILE) {
					if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE && !empty($row['file_password'.$metadata['metadata_id']])) {
						$items[$metadata['metadata_id']."_file_password"] = $row['file_password'.$metadata['metadata_id']];
					}
					$items[$metadata['metadata_id']] = $this->getFileLink($row['content'.$metadata['metadata_id']],
																			$row['file_name'.$metadata['metadata_id']],
																			$row['physical_file_name'.$metadata['metadata_id']],
																			$metadata, null, $row['insert_user_id'], $row['file_password'.$metadata['metadata_id']]);
				} elseif($metadata['type'] == MULTIDATABASE_META_TYPE_INSERT_TIME) {
					$items[$metadata['metadata_id']] = $row['insert_time'];
				} elseif($metadata['type'] == MULTIDATABASE_META_TYPE_UPDATE_TIME) {
					$items[$metadata['metadata_id']] = $row['update_time'];
				} elseif($metadata['type'] == MULTIDATABASE_META_TYPE_MULTIPLE) {
					$itemArr = explode("|",$row['content'.$metadata['metadata_id']]);
					$multiple = array();
					foreach ($itemArr as $val) {
						$multiple[$val] = $val;
					}
					$items[$metadata['metadata_id']] = $multiple;
				} else {
					$items[$metadata['metadata_id']] = $row['content'.$metadata['metadata_id']];
				}
			}

			$data['metadata'] = $layout;
			$data['value'] = $items;
		}

		return $data;
	}

	/**
	 * 一覧取得
	 * @param string $multidatabase_id 汎用データベースID
	 * @param array $metadatas メタデータ項目配列
	 * @param array $where_params 絞込み文字列配列
	 * @param array $order_params ソートデータ配列
	 * @param string $search_sql キーワード検索条件WHERE句文字列
	 * @param string $disp_cnt 表示件数
	 * @param string $begin 対象データ開始行番号
	 * @param array $keywordBindValues 検索キーワードデータ配列
	 * @return コンテンツデータ配列
	 */
	function &getSearchResult($multidatabase_id,
								$metadatas,
								$where_params,
								$order_params,
								$search_sql,
								$disp_cnt = 0,
								$begin = 0,
								$keywordBindValues = array())
	{
		list($sql_where, $params) = $this->_getSqlContentWhereStatement($multidatabase_id, $where_params, $keywordBindValues);

		$sql = $this->_getDataSQL($metadatas, ',URL.short_url');
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= $this->_getAbbreviateUrlJoinStatement();;
		$sql .= $sql_where;
		$sql .= $search_sql;
		$sql .= $this->_getAuthorityWhereSQL($params);
		$sql .= $this->_db->getOrderSQL($order_params);

		$result = $this->_db->execute($sql, $params ,$disp_cnt, $begin, true, array($this,"_getSearchResultFetchcallback"));
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
	function &_getSearchResultFetchcallback($result) {
		$ret = array();

		while ($row = $result->fetchRow()) {
			foreach($row as $key => $val) {
				$metadata_id = substr($key, 7, strlen($key));
				if (is_numeric($metadata_id)) {
					$content[$metadata_id] = $val;
				} else {
					$content[$key] = $val;
				}
			}
			$ret[] = $content;
		}
		return $ret;
	}

	/**
	 * 編集権限を取得する
	 *
	 * @param	array	$insetUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasEditAuthority($inset_user_id)
	{
		$session =& $this->_container->getComponent("Session");

		$user_id = $session->getParameter("_user_id");
		$auth_id = $session->getParameter("_auth_id");
		if ($inset_user_id == $user_id || $auth_id >= _AUTH_CHIEF) {
			return true;
		}

		$request =& $this->_container->getComponent("Request");
		$room_id = $request->getParameter("room_id");
		$hierarchy = $session->getParameter("_hierarchy");
		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($inset_user_id, $room_id);
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * 承認権限を取得する
	 *
	 * @param	array	$insetUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasConfirmAuthority()
	{
		$session =& $this->_container->getComponent("Session");
		$_auth_id = $session->getParameter("_auth_id");

		if ($_auth_id >= _AUTH_CHIEF) {
			return true;
		}

	    return false;
	}

	/**
	 * メール送信データを取得する
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMail($content_id, $metadatas) {
		$sql = "";
		if(!empty($metadatas)){
			$sql .= $this->_getDataSQL($metadatas);
			$sql .= " WHERE {multidatabase_content}.content_id=? ";
		}

		$params = array(
			"content_id" => $content_id
		);

		$result = $this->_db->execute($sql, $params);
		if ( $result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result[0];
	}

	/**
	 * タイトル一覧取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMDBTitleList($multidatabase_id, $metadata_title_id, $order_params) {
		$sql = "";
		$params = array(
			"multidatabase_id" => $multidatabase_id
		);

		$sql = "SELECT {multidatabase_content}.content_id, {multidatabase_content}.vote_count, " .
		$sql .=	" {multidatabase_content}.insert_time, {multidatabase_content}.update_time, ";
		$sql .=	" m_content.content AS title, m_file.file_name ";
		$sql .= " FROM {multidatabase_content} ";
		$sql .= " LEFT JOIN {multidatabase_metadata_content} m_content ";
		$sql .= " ON ({multidatabase_content}.content_id = m_content.content_id";
		$sql .= " AND m_content.metadata_id = ". $metadata_title_id .")";
		$sql .= " LEFT JOIN {multidatabase_file} m_file ";
		$sql .= " ON (m_content.metadata_content_id = m_file.metadata_content_id) ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= " WHERE {multidatabase_content}.multidatabase_id=? ";
		$sql .= $this->_getAuthorityWhereSQL($params);
		$sql .= $this->_db->getOrderSQL($order_params);

		$result = $this->_db->execute($sql, $params);
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * コンテンツ番号データを取得する
	 *
     * @return array	コンテンツ番号データ配列
	 * @access	public
	 */
	function &getContentSequence() {
		$request =& $this->_container->getComponent("Request");

		$params = array(
			$request->getParameter("drag_content_id"),
			$request->getParameter("drop_content_id"),
			$request->getParameter("multidatabase_id")
		);

		$sql = "SELECT content_id, display_sequence ".
				"FROM {multidatabase_content} ".
				"WHERE (content_id = ? ".
				"OR content_id = ?) ".
				"AND multidatabase_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false ||
			count($result) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$result[0]["content_id"]] = $result[0]["display_sequence"];
		$sequences[$result[1]["content_id"]] = $result[1]["display_sequence"];

		return $sequences;
	}

	/**
	 * 携帯用ブロックデータを取得 Add by AllCreator
 	 *
 	 * @access  public
 	 */
	function getBlocksForMobile($block_id_arr)
	{
		$sql = "SELECT multidatabase.*, block.block_id, block.visible_item" .
				" FROM {multidatabase} multidatabase" .
				" INNER JOIN {multidatabase_block} block ON (multidatabase.multidatabase_id=block.multidatabase_id)" .
				" WHERE block.block_id IN (".implode(",", $block_id_arr).")" .
				" ORDER BY block.insert_time DESC, block.multidatabase_id DESC";

		return $this->_db->execute($sql, null, null, null, true);
	}

	/**
	 * 日付チェック
 	 *
 	 * @access  public
 	 */
	function checkDate($attributes)
	{
		$session =& $this->_container->getComponent("Session");
		$mobile_flag = $session->getParameter("_mobile_flag");

		if ($mobile_flag == _ON) {
			$date = $attributes;
			$replace_of = array('Y', 'm', 'd');
			$replace_by = array($date["year"], $date["month"], $date["day"]);

			$attributes = str_replace($replace_of, $replace_by, _INPUT_DATE_FORMAT);
			if ($attributes == "//") {
				$attributes = "";
			}
		}
		if (empty($attributes)) {
			return "";
		}
		switch (_INPUT_DATE_FORMAT) {
    		case "Y/m/d":
		    	$pattern = "/^([0-9]{4})\/([0-1]?[0-9])\/([0-3]?[0-9])$/";
    			break;
    		case "m/d/Y":
    		case "d/m/Y":
    			$pattern = "/^([0-3]?[0-9])\/([0-1]?[0-9])\/([0-9]{4})$/";
    			break;
    		default:
    			return "";
    	}

		if (!preg_match($pattern, $attributes, $matches)) {
			return "";
		}

		switch (_INPUT_DATE_FORMAT) {
    		case "Y/m/d":
		    	$check = checkdate($matches[2], $matches[3], $matches[1]);
		    	$dateString = $matches[1]. sprintf("%02d", intval($matches[2])). sprintf("%02d", intval($matches[3]));
    			break;
    		case "m/d/Y":
		    	$check = checkdate($matches[1], $matches[2], $matches[3]);
		    	$dateString = $matches[3]. sprintf("%02d", intval($matches[1])). sprintf("%02d", intval($matches[2]));
    			break;
    		case "d/m/Y":
    			$check = checkdate($matches[2], $matches[1], $matches[3]);
    			$dateString = $matches[3]. sprintf("%02d", intval($matches[2])). sprintf("%02d", intval($matches[1]));
    			break;
    	}
    	if (!$check) {
			return "";
		}

		return $dateString;
	}

	/**
	 * 自動番号の取得
 	 *
 	 * @access  public
 	 */
	function getAutoNumber($metadata_id) {
		$params = array(
			"metadata_id" => $metadata_id,
		);
		$number = $this->_db->maxExecute("multidatabase_metadata_content", "content", $params);
		if ($number === false) {
			return 0;
		}
		return sprintf(MULTIDATABASE_META_AUTONUM_FORMAT, intval($number)+1);
	}

	/**
	 * コンテンツデータ用WHERE句取得処理
	 *
	 * @param string $multidatabaseId 対象の汎用データベースID
	 * @param array $whereValues 検索条件配列
	 * @param array $keywordBindValues 検索キーワードデータ
	 * @return array 0:コンテンツデータ用WHERE句文字列
	 *               1:バインドパラメータ値配列
	 * @access  public
	 */
	function &_getSqlContentWhereStatement($multidatabaseId, $whereValues, $keywordBindValues = array()) {
		$whereStatement = ' WHERE {multidatabase_content}.multidatabase_id=? ';
		$bindValues = array(
			'multidatabase_id' => $multidatabaseId
		);
		if (empty($whereValues)) {
			$bindValues += $keywordBindValues;

			$returnValue = array(
				$whereStatement,
				$bindValues
			);
			return $returnValue;
		}

		foreach($whereValues as $columnName => $value) {
			if (!strlen($value)) {
				continue;
			}

			$key = 'm_content' . $columnName . '.content';
			if (mb_strlen($value, INTERNAL_CODE) < _MYSQL_FT_MIN_WORD_LEN) {
				$whereStatement .= ' AND ' . $columnName . ' LIKE ? ';
				$bindValues[$key] = '%' . $value . '%';
			} else {
				$whereStatement .= ' AND MATCH(' . $columnName . ') '
									. 'AGAINST (? IN BOOLEAN MODE)';
				$bindValues[$key] = '"' . $value . '"';
			}
		}

		$bindValues += $keywordBindValues;

		$returnValue = array(
			$whereStatement,
			$bindValues
		);
		return $returnValue;
	}

	/**
	 * 短縮URLデータ用JOIN句取得処理
	 *
	 * @return string 短縮URLデータ用JOIN句文字列
	 * @access  public
	 */
	function &_getAbbreviateUrlJoinStatement() {
		$request =& $this->_container->getComponent('Request');
		$moduleId = $request->getParameter('module_id');

		$joinStatement = 'LEFT JOIN {abbreviate_url} URL '
							. "ON URL.module_id = '" . $moduleId . "' "
							. 'AND URL.contents_id = {multidatabase_content}.multidatabase_id '
							. 'AND URL.unique_id = {multidatabase_content}.content_id ';
		return $joinStatement;
	}
}
?>