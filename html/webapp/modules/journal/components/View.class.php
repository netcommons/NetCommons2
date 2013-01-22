<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日誌取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Components_View
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
	function Journal_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * ルームIDの日誌件数を取得する
	 *
     * @return string	日誌件数
	 * @access	public
	 */
	function getJournalCount() {
		$request =& $this->_container->getComponent("Request");
    	$params["room_id"] = $request->getParameter("room_id");
    	$count = $this->_db->countExecute("journal", $params);

		return $count;
	}

	/**
	 * 在配置されている日誌IDを取得する
	 *
     * @return string	配置されている日誌ID
	 * @access	public
	 */
	function &getCurrentJournalId() {
		$request =& $this->_container->getComponent("Request");
		$params = array($request->getParameter("block_id"));
		$sql = "SELECT journal_id ".
				"FROM {journal_block} ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0]["journal_id"];
	}

	/**
	 * 日誌一覧データを取得する
	 *
	 * @return array	日誌一覧データ配列
	 * @access	public
	 */
	function &getJournals() {
		$request =& $this->_container->getComponent("Request");
		$limit = $request->getParameter("limit");
		$offset = $request->getParameter("offset");

		$sortColumn = $request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "journal_id";
		}
		$sortDirection = $request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($request->getParameter("room_id"));
		$sql = "SELECT journal_id, journal_name, active_flag, insert_time, insert_user_id, insert_user_name ".
				"FROM {journal} ".
				"WHERE room_id = ? ".
				$this->_db->getOrderSQL($orderParams);
		$result = $this->_db->execute($sql, $params, $limit, $offset);
		if ($result === false) {
			$this->_db->addError();
		}

		return $result;
	}

	/**
	 * 日誌が存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function journalExists() {
		$request =& $this->_container->getComponent("Request");
		$params = array(
			$request->getParameter("journal_id"),
			$request->getParameter("room_id")
		);
		$sql = "SELECT journal_id ".
				"FROM {journal} ".
				"WHERE journal_id = ? ".
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
	 * 日誌用デフォルトデータを取得する
	 *
     * @return array	日誌用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultJournal() {
		$configView =& $this->_container->getComponent("configView");
		$request =& $this->_container->getComponent("Request");
		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
        	return $config;
        }

		$journal = array(
			"active_flag" => constant($config["active_flag"]["conf_value"]),
			"visible_item" => $config["visible_item"]["conf_value"],
			"post_authority" => constant($config["post_authority"]["conf_value"]),
			"mobile_mail_flag" => constant($config["mobile_mail_flag"]["conf_value"]),
			"mail_flag" => constant($config["mail_flag"]["conf_value"]),
			"new_period" => $config["new_period"]["conf_value"],
			"vote_flag" => constant($config["vote_flag"]["conf_value"]),
			"comment_flag" => constant($config["comment_flag"]["conf_value"]),
			"trackback_transmit_flag" => constant($config["trackback_transmit_flag"]["conf_value"]),
			"trackback_receive_flag" => constant($config["trackback_receive_flag"]["conf_value"]),
			"agree_flag" => constant($config["agree_flag"]["conf_value"]),
			"agree_mail_flag" => constant($config["agree_mail_flag"]["conf_value"]),
			"comment_agree_flag" => constant($config["comment_agree_flag"]["conf_value"]),
			"comment_agree_mail_flag" => constant($config["comment_agree_mail_flag"]["conf_value"])
		);

		return $journal;
	}

	/**
	 * 日誌データを取得する
	 *
     * @return array	日誌データ配列
	 * @access	public
	 */
	function &getJournal() {
		$request =& $this->_container->getComponent("Request");
		$configView =& $this->_container->getComponent("configView");

		$sql = "SELECT journal_id, journal_name, ";
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "journal_view_edit_create" ||
				$actionName == "journal_action_edit_create") {
			$sql .= "active_flag, post_authority, mobile_mail_flag, mail_flag, mail_authority, ".
			        "mail_subject, mail_body, vote_flag, comment_flag, new_period, trackback_transmit_flag, ".
					"trackback_receive_flag, transmit_blogname, agree_flag, agree_mail_flag, agree_mail_subject, agree_mail_body, ".
                    "comment_agree_flag, comment_agree_mail_flag, comment_agree_mail_subject, comment_agree_mail_body ";
		} else {
			$prefix_id_name = $request->getParameter("prefix_id_name");
			if ($prefix_id_name == JOURNAL_REFERENCE_PREFIX_NAME.$request->getParameter('journal_id')) {
				$sql .= _OFF . " AS active_flag";
			} else {
				$sql .= "active_flag";
			}
			$sql .= ", mobile_mail_flag, mail_flag, vote_flag, comment_flag, sns_flag, new_period, trackback_transmit_flag, ".
				    "trackback_receive_flag, agree_flag, agree_mail_flag, comment_agree_flag, comment_agree_mail_flag ";
		}

		$params = array($request->getParameter("journal_id"));
		$sql .=	"FROM {journal} ".
				"WHERE journal_id = ?";
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

		return $result[0];
	}

	/**
	 * 現在配置されている日誌データを取得する
	 *
     * @return array	配置されている日誌データ配列
	 * @access	public
	 */
	function &getCurrentJournal() {
		$request =& $this->_container->getComponent("Request");

		$params = array($request->getParameter("block_id"));
		$sql = "SELECT B.block_id, B.journal_id, B.visible_item, ".
					"J.journal_name, J.active_flag, J.post_authority, J.new_period, J.mobile_mail_flag, J.mail_flag, ".
					"J.mail_authority, J.mail_subject, J.mail_body, J.vote_flag, J.comment_flag, J.sns_flag, J.agree_flag, J.agree_mail_flag, ".
					"J.agree_mail_subject, J.agree_mail_body, J.trackback_transmit_flag, J.trackback_receive_flag, J.transmit_blogname, ".
					"J.comment_agree_flag, J.comment_agree_mail_flag, J.comment_agree_mail_subject, J.comment_agree_mail_body ".
				"FROM {journal_block} B ".
				"INNER JOIN {journal} J ".
				"ON B.journal_id = J.journal_id ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$result[0]['has_post_auth'] = $this->_hasPostAuthority($result[0]);
		$result[0]['new_period_time'] = $this->_getNewPeriodTime($result[0]["new_period"]);

		return $result[0];
	}

	/**
	 * 投稿権限を取得する
	 *
	 * @param	array	$journal	日誌状態、表示方法、投稿権限の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasPostAuthority($journal) {
		if ($journal["active_flag"] != _ON) {
			return false;
		}

		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");
		if ($auth_id >= $journal["post_authority"]) {
			return true;
		}

		return false;
	}

	/**
	 * コメント投稿権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasCommentAuthority() {
		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");
		if ($auth_id >= _AUTH_GENERAL) {
			return true;
		}

		return false;
	}

	/**
	 * 承認権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasConfirmAuthority() {
		$session =& $this->_container->getComponent("Session");
		$_auth_id = $session->getParameter("_auth_id");

		if ($_auth_id >= _AUTH_CHIEF) {
			return true;
		}

	    return false;
	}

	/**
	 * 編集権限を取得する
	 *
	 * @param	array	$inset_user_id	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasEditAuthority($inset_user_id) {
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
	 * カテゴリを取得する
	 *
     * @return array	カテゴリデータ配列
	 * @access	public
	 */
	function getCatByJournalId($journal_id) {
		$params = array(
			"journal_id" => intval($journal_id)
		);
		$order_params = array(
    		"display_sequence" =>"ASC"
    	);
		$journal_categories = $this->_db->selectExecute("journal_category", $params, $order_params);

		if ( $journal_categories === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $journal_categories;
		}

		return $journal_categories;
	}

	/**
	 * カテゴリを取得する
	 *
     * @return array	カテゴリデータ配列
	 * @access	public
	 */
	function getCatByPostId($post_id) {
		$sql = "SELECT C.* ".
				"FROM {journal_category} C ";
		$sql .= "LEFT JOIN {journal_post} J ".
					"ON (C.journal_id = J.journal_id) AND (C.category_id = J.category_id)";
		$sql .= "WHERE J.post_id = ? ";
		$params = array(
			"post_id" => intval($post_id)
		);
		$category = $this->_db->execute($sql, $params, null, null, true);

		if ( $category === false || !isset($category[0])) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}

		return $category[0];
	}

	/**
	 * 日誌数を取得する
	 *
     * @return int	日誌数
	 * @access	public
	 */
	function getPostCount($journal_id, $category_id) {
		$sql = "";
		$sql .= "SELECT count(*) post_count ";
		$sql .= " FROM {journal_post} P ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= " WHERE P.root_id=? AND P.journal_id=? ";
		$params[] = "";
		$params[] = intval($journal_id);

		if(!empty($category_id)) {
			$sql .= " AND P.category_id=? ";
			$params[] = intval($category_id);
		}

		$sql .= $this->_getAuthorityWhereSQL($params);
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result[0]['post_count'];
	}

	/**
	 * 日誌一覧を取得する
	 *
     * @return array	日誌一覧データ
	 * @access	public
	 */
	function getPostList($journal_id, $category_id, $disp_cnt, $begin) {
		$params[] = $journal_id;
		$params[] = "";

		$request =& $this->_container->getComponent("Request");
		$module_id = $request->getParameter("module_id");

		$sql = "SELECT P.*, C.category_name, URL.short_url ".
				"FROM {journal_post} P".
				" LEFT JOIN {journal_category} C ON (C.journal_id = P.journal_id) AND (C.category_id = P.category_id)".
				//--URL短縮形関連
				" LEFT JOIN {abbreviate_url} URL" .
					" ON (URL.module_id = '".$module_id."' AND URL.contents_id = P.journal_id AND URL.unique_id = P.post_id) ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= "WHERE P.journal_id = ? AND P.root_id = ? ";
		if(!empty($category_id)) {
			$params[] = $category_id;
			$sql .= "AND P.category_id = ? ";
		}
		$sql .= $this->_getAuthorityWhereSQL($params);
		$sql .= " ORDER BY journal_date DESC, insert_time DESC";
    	$posts = $this->_db->execute($sql, $params, $disp_cnt, $begin, true, array($this,"_getPostFetchcallback"));
		if ( $posts === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}

		return $posts;
	}

	/**
	 * 日誌詳細を取得する
	 *
     * @return array	日誌詳細データ
	 * @access	public
	 */
	function getPostDetail($post_id) {
		$params[] = $post_id;

		$request =& $this->_container->getComponent("Request");
		$module_id = $request->getParameter("module_id");

		$sql = "SELECT P.*, C.category_name, URL.short_url ".
				"FROM {journal_post} P".
				" LEFT JOIN {journal_category} C ON (C.journal_id = P.journal_id) AND (C.category_id = P.category_id)".
				//--URL短縮形関連
				" LEFT JOIN {abbreviate_url} URL" .
					" ON (URL.module_id = '".$module_id."' AND URL.contents_id = P.journal_id AND URL.unique_id = P.post_id) ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= "WHERE P.post_id = ? ";
		$sql .= $this->_getAuthorityWhereSQL($params);

		$post = $this->_db->execute($sql, $params, null, null, true, array($this,"_getPostFetchcallback"));
		if ( $post === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}


		return $post;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getPostFetchcallback($result) {
		$session =& $this->_container->getComponent("Session");
		$ret = array();
		while ($row = $result->fetchRow()) {
			$comment_count = 0;
			$vote_count = 0;
			$trackback_count = 0;

			$comment_count = $this->getChildCount($row['post_id']);
			$row['comment_count'] = $comment_count;
			$trackback_count = $this->getChildCount($row['post_id'], JOURNAL_TRACKBACK_RECEIVE);
			$row['trackback_count'] = $trackback_count;
			$row['voted'] = false;
			if($row['vote'] != "") {
				$who_voted = explode("|", $row['vote']);
				$user_id = $session->getParameter("_user_id");
				if(empty($user_id)) {
					$votes = $session->getParameter("journal_votes");
					if(!empty($votes)) {
						if(in_array($row['post_id'], $votes)) {
							$row['voted'] = true;
						}
					}
				}else if (in_array($user_id, $who_voted)) {
					$row['voted'] = true;
				}
				$row['vote_count'] = count($who_voted);
			}else {
				$row['vote_count'] = 0;
			}
			$row['has_edit_auth'] = $this->_hasEditAuthority($row['insert_user_id']);
			$row['has_confirm_auth'] = $this->_hasConfirmAuthority();
			$row['has_comment_auth'] = $this->_hasCommentAuthority();
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * 日誌詳細を取得する
	 *
     * @return array	日誌詳細データ
	 * @access	public
	 */
	function getPostDetailData($post_id) {
		$request =& $this->_container->getComponent("Request");
		$params = array(
			$request->getParameter("journal_id"),
			$post_id
		);

		$module_id = $request->getParameter("module_id");

		$sql = "SELECT P.*, C.category_name, URL.short_url ".
				"FROM {journal_post} P".
				" LEFT JOIN {journal_category} C ON (C.journal_id = P.journal_id) AND (C.category_id = P.category_id)".
				//--URL短縮形関連
				" LEFT JOIN {abbreviate_url} URL" .
					" ON (URL.module_id = '".$module_id."' AND URL.contents_id = P.journal_id AND URL.unique_id = P.post_id) ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= "WHERE P.journal_id=? AND P.post_id = ? ";
		$sql .= $this->_getAuthorityWhereSQL($params);

		$post = $this->_db->execute($sql, $params, null, null, true, array($this,"_getPostFetchcallback"));
		if($post === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}

		if(isset($post[0]) && empty($post[0]['root_id']) && empty($post[0]['parent_id'])) {
			$params = array();
			$sql_common = "SELECT post_id, URL.short_url FROM {journal_post} P " .
							$this->_getAuthorityFromSQL();
			//--URL短縮形関連
			$sql_common .= " LEFT JOIN {abbreviate_url} URL" .
					" ON (URL.module_id = '".$module_id."' AND URL.contents_id = P.journal_id AND URL.unique_id = P.post_id) ";
			$sql_common .= "WHERE P.root_id = 0 AND P.parent_id = 0 AND P.journal_id = ".$post[0]['journal_id']." ";
			$sql_common .= $this->_getAuthorityWhereSQL($params);

			$sql_where = " AND  (P.journal_date < '".$post[0]['journal_date']."' OR P.journal_date = '".$post[0]['journal_date']."' AND P.post_id < ".$post[0]['post_id'].")";
			$sql_order = " ORDER BY P.journal_date DESC, P.post_id DESC";

			$sql = $sql_common.$sql_where.$sql_order;
			$result = $this->_db->execute($sql, $params, 1);
			if($result === false) {
				$this->_db->addError();
				return false;
			}
			$post[0]['older_post_id'] = empty($result) ? "":$result[0]['post_id'];
			$post[0]['older_short_url'] = empty($result) ? "":$result[0]['short_url'];

			$sql_where = " AND  (P.journal_date > '".$post[0]['journal_date']."' OR P.journal_date = '".$post[0]['journal_date']."' AND P.post_id > ".$post[0]['post_id'].")";
			$sql_order = " ORDER BY P.journal_date ASC, P.post_id ASC";

			$sql = $sql_common.$sql_where.$sql_order;
			$result = $this->_db->execute($sql, $params, 1);
			if($result === false) {
				$this->_db->addError();
				return false;
			}
			$post[0]['newer_post_id'] = empty($result) ? "":$result[0]['post_id'];
			$post[0]['newer_short_url'] = empty($result) ? "":$result[0]['short_url'];
		}

		return $post;
	}

	/**
	 * 日誌のコメントとトラックバック数を取得する
	 *
     * @return int	コメント数
	 * @access	public
	 */
	function getChildCount($post_id, $direction_flag=0) {
		$params[] = $post_id;
		$params[] = $direction_flag;

		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");

		$sql = "SELECT count(*) post_count FROM {journal_post} P ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= "WHERE P.root_id = ? AND P.direction_flag = ? ";
		if(!empty($direction_flag) && $auth_id < _AUTH_CHIEF) {
			$sql .= "AND (P.agree_flag = ".JOURNAL_STATUS_AGREE_VALUE." OR P.insert_user_id != '0') ";
		}
		$sql .= $this->_getAuthorityWhereSQL($params);
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result[0]['post_count'];
	}

	/**
	 * 日誌のコメントを取得する
	 *
     * @return array	日誌のコメントデータ
	 * @access	public
	 */
	function getChildDetail($post_id, $direction_flag=0) {
		$params[] = $post_id;
		$params[] = $direction_flag;

		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");

		$sql = "SELECT P.* FROM {journal_post} P ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= "WHERE P.root_id = ? AND P.direction_flag = ? ";
		if(!empty($direction_flag) && $auth_id < _AUTH_CHIEF) {
			$sql .= "AND (P.agree_flag = ".JOURNAL_STATUS_AGREE_VALUE." OR P.insert_user_id != '0') ";
		}
		$sql .= $this->_getAuthorityWhereSQL($params);
		$sql .= " ORDER BY insert_time ASC";
		$limit = null;
		$offset = null;

		$mobile_flag = $session->getParameter("_mobile_flag");
		if ($mobile_flag == _ON) {
			$limit = JOURNAL_MOBILE_COMMENT_CNT;
			$offset = 0;
		}
    	$posts = $this->_db->execute($sql, $params, $limit, $offset, true, array($this,"_getChildFetchcallback"), $direction_flag);
		if ( $posts === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}

		return $posts;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getChildFetchcallback($result, $direction_flag) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(!empty($row['journal_date'])) {
				$row['journal_date'] = timezone_date_format($row['journal_date'], "YmdHis");
			}
			if($direction_flag == JOURNAL_TRACKBACK_RECEIVE) {
				if(!empty($row['tb_url'])) {
					$pos = strpos($row['tb_url'], "?action=journal_view_main_detail");
					if($pos != false) {
						$row['tb_url'] = str_replace("?action=journal_view_main_detail", "?action=pages_view_main&active_action=journal_view_main_detail", $row['tb_url']);
					}
				}
				$row["title"] = mb_convert_encoding($row["title"], _CHARSET, "auto");
				$row["content"] = mb_convert_encoding($row["content"], _CHARSET, "auto");
			}
			$row['has_edit_auth'] = $this->_hasEditAuthority($row['insert_user_id']);
			$row['has_confirm_auth'] = $this->_hasConfirmAuthority();
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * 権限判断用のSQL文FROM句を取得する
	 *
     * @return string	権限判断用のSQL文FROM句
	 * @access	public
	 */
	function &_getAuthorityFromSQL()
	{
		$session =& $this->_container->getComponent("Session");
		$authId = $session->getParameter("_auth_id");

		$sql = "";
		if ($authId >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "LEFT JOIN {pages_users_link} PU ".
					"ON P.insert_user_id = PU.user_id ".
					"AND P.room_id = PU.room_id ";
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
	function &_getAuthorityWhereSQL(&$params)
	{
		$session =& $this->_container->getComponent("Session");
		$authId = $session->getParameter("_auth_id");
		$date = timezone_date(null, true, "YmdHis");

		$sql = "";
		if ($authId >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "AND ((P.status = ? AND P.agree_flag = ? AND P.journal_date <= ? ) OR A.hierarchy < ? OR P.insert_user_id = ? ";

		$defaultEntry = $session->getParameter("_default_entry_flag");
		$hierarchy = $session->getParameter("_hierarchy");
		if ($defaultEntry == _ON && $hierarchy > $session->getParameter("_default_entry_hierarchy")) {
			$sql .= " OR A.hierarchy IS NULL) ";
		} else {
			$sql .= ") ";
		}

		//$sql .= " AND P.journal_date <= ? ";

		//$request =& $this->_container->getComponent("Request");
		//$params[] = $request->getParameter("room_id");
		$params[] = JOURNAL_POST_STATUS_REREASED_VALUE;
		$params[] = JOURNAL_STATUS_AGREE_VALUE;
		$params[] = $date;
		$params[] = $hierarchy;
		$params[] = $session->getParameter("_user_id");

		return $sql;
	}

	/**
	 * メール送信データを取得する
	 *
	 * @param	string	$post_id	記事ID
	 * @return array	メール送信データ配列
	 * @access	public
	 */
	function &getMail($post_id)
	{
		$params = array($post_id);
		$sql = "SELECT P.room_id, P.journal_id, P.parent_id, P.title, P.status, P.insert_user_id, P.insert_user_name, P.journal_date, P.insert_time, P.content, P.direction_flag, ".
						"B.journal_name, B.mail_flag, B.mail_authority, B.mail_subject, B.mail_body, C.category_name, ".
						"B.agree_mail_flag, B.agree_mail_subject, B.agree_mail_body, ".
						"B.comment_agree_mail_flag, B.comment_agree_mail_subject, B.comment_agree_mail_body ".
				"FROM {journal_post} P ".
				"INNER JOIN {journal} B ".
					"ON (P.journal_id = B.journal_id) ".
				"LEFT JOIN {journal_category} C ".
					"ON (P.journal_id = C.journal_id AND P.category_id = C.category_id) ".
				"WHERE P.post_id = ?";
		$mails = $this->_db->execute($sql, $params);
		if ($mails === false || !isset($mails[0])) {
			$this->_db->addError();
			return $mails;
		}

		return $mails[0];
	}

	/**
	 * 投稿権限チェック
	 *
	 * @access	public
	 */
	function hasPostAuth($journal_id)
	{
		$session =& $this->_container->getComponent("Session");
		$_user_id = $session->getParameter("_user_id");
		$_auth_id = $session->getParameter("_auth_id");

		if ($_auth_id >= _AUTH_CHIEF) {
			return true;
		}
        $result = $this->_db->selectExecute("journal", array("journal_id"=>$journal_id));
		if ($result === false || !isset($result[0])) {
	       	return false;
		}
		if ($_auth_id >= $result[0]["post_authority"]) {
			return true;
		}
		return false;
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($block_id_arr)
	{
    	$sql = "SELECT journal.*, block.block_id" .
    			" FROM {journal} journal" .
    			" INNER JOIN {journal_block} block ON (journal.journal_id=block.journal_id)" .
    			" WHERE block.block_id IN (".implode(",", $block_id_arr).")" .
    			" ORDER BY block.insert_time DESC, block.journal_id DESC";

        return $this->_db->execute($sql, null, null, null, true);
	}

/**
     * ページに関する設定を行います
     *
     * @param int disp_cnt 1ページ当り表示件数
     * @param int now_page 現ページ
     */
    function setPageInfo(&$pager, $data_cnt, $disp_cnt, $now_page = NULL)
    {
    	$pager['data_cnt']    = 0;
    	$pager['total_page']  = 0;
    	$pager['next_link']   = FALSE;
    	$pager['prev_link']   = FALSE;
    	$pager['disp_begin']  = 0;
    	$pager['disp_end']    = 0;
    	$pager['link_array']  = NULL;

    	if(empty($disp_cnt)) {
    		return false;
    	}

    	$pager['data_cnt'] = $data_cnt;
        // now page
        $pager['now_page'] = (NULL == $now_page) ? 1 : $now_page;
        // total page
        $pager['total_page'] = ceil($pager['data_cnt'] / $disp_cnt);
        if($pager['total_page'] < $pager['now_page']) {
        	$pager['now_page'] = 1;
        }
        // link array {{
        if(($pager['now_page'] - JOURNAL_FRONT_AND_BEHIND_LINK_CNT) > 0){
            $start = $pager['now_page'] - JOURNAL_FRONT_AND_BEHIND_LINK_CNT;
        }else{
            $start = 1;
        }
        if(($pager['now_page'] + JOURNAL_FRONT_AND_BEHIND_LINK_CNT) >= $pager['total_page']){
            $end = $pager['total_page'];
        }else{
            $end = $pager['now_page'] + JOURNAL_FRONT_AND_BEHIND_LINK_CNT;
        }
        $i = 0;
        for($i = $start; $i <= $end; $i++){
            $pager['link_array'][] = $i;
        }
        // next link
        if($disp_cnt < $pager['data_cnt']){
            if($pager['now_page'] < $pager['total_page']){
                $pager['next_link'] = TRUE;
            }
        }
        // prev link
        if(1 < $pager['now_page']){
            $pager['prev_link'] = TRUE;
        }
        // begin disp number
        $pager['disp_begin'] = ($pager['now_page'] - 1) * $disp_cnt;
        // end disp number
        $tmp_cnt = $pager['now_page'] * $disp_cnt;
        $pager['disp_end'] = ($pager['data_cnt'] < $tmp_cnt) ? $pager['data_cnt'] : $tmp_cnt;
	}

}
?>
