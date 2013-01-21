<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 掲示板データ取得コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Components_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Bbs_Components_View() 
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
		$this->_session =& $container->getComponent("Session");
	}

	/**
	 * 掲示板が配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock() 
	{
		$params = array($this->_request->getParameter("bbs_id"));
		$sql = "SELECT B.room_id, BL.block_id ".
				"FROM {bbs} B ".
				"INNER JOIN {bbs_block} BL ".
				"ON B.bbs_id = BL.bbs_id ".
				"WHERE B.bbs_id = ? ".
				"ORDER BY BL.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}
	
	/**
	 * 掲示板が存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function bbsExists() 
	{
		$params = array(
			$this->_request->getParameter("bbs_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT bbs_id ".
				"FROM {bbs} ".
				"WHERE bbs_id = ? ".
				"AND room_id = ?";
		$bbsIDs = $this->_db->execute($sql, $params);
		if ($bbsIDs === false) {
			$this->_db->addError();
			return $bbsIDs;
		}
		
		if (count($bbsIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDの掲示板件数を取得する
	 *
     * @return string	掲示板件数
	 * @access	public
	 */
	function getBbsCount() 
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$count = $this->_db->countExecute("bbs", $params);

		return $count;
	}

	/**
	 * 在配置されている掲示板IDを取得する
	 *
     * @return string	配置されている掲示板ID
	 * @access	public
	 */
	function &getCurrentBbsID() 
	{
		$params = array($this->_request->getParameter("block_id"));		
		$sql = "SELECT bbs_id ".
				"FROM {bbs_block} ".
				"WHERE block_id = ?";
		$bbsIDs = $this->_db->execute($sql, $params);
		if ($bbsIDs === false) {
			$this->_db->addError();
			return $bbsIDs;
		}

		return $bbsIDs[0]["bbs_id"];
	}

	/**
	 * 掲示板一覧データを取得する
	 *
     * @return array	掲示板一覧データ配列
	 * @access	public
	 */
	function &getBbses() 
	{
		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "bbs_id";
		}
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT bbs_id, bbs_name, activity, insert_time, insert_user_id, insert_user_name ".
				"FROM {bbs} ".
				"WHERE room_id = ? ".
				$this->_db->getOrderSQL($orderParams);
		$bbses = $this->_db->execute($sql, $params, $limit, $offset);
		if ($bbses === false) {
			$this->_db->addError();
		}
		
		return $bbses;
	}

	/**
	 * 掲示板用デフォルトデータを取得する
	 *
     * @return array	掲示板用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultBbs()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);
		if ($config === false) {
        	return $config;
        }
        
		$bbs = array(
			"activity" => constant($config["activity"]["conf_value"]),
			"topic_authority" => constant($config["topic_authority"]["conf_value"]),
			"child_flag" => constant($config["child_flag"]["conf_value"]),
			"vote_flag" => constant($config["vote_flag"]["conf_value"]),
			"new_period" => $config["new_period"]["conf_value"],
			"mail_send" => constant($config["mail_send"]["conf_value"]),
			"mail_authority" => constant($config["mail_authority"]["conf_value"]),
			"display" => constant($config["display"]["conf_value"]),
			"expand" => constant($config["expand"]["conf_value"]),
			"visible_row" => $config["visible_row"]["conf_value"]
		);
		
		return $bbs;
	}

	/**
	 * 掲示板データを取得する
	 *
     * @return array	掲示板データ配列
	 * @access	public
	 */
	function &getBbs() 
	{
		$sql = "SELECT bbs_id, bbs_name, ";
		
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "bbs_view_edit_entry" ||
				$actionName == "bbs_action_edit_entry") {
			$sql .= "activity, topic_authority, child_flag, vote_flag, new_period, ".
							"mail_send, mail_authority, mail_subject, mail_body ";
		} else {
			$prefixID = $this->_request->getParameter("prefix_id_name");
			if (strpos($prefixID, BBS_PREFIX_REFERENCE) === 0) {
				$sql .= _OFF . " AS activity";
			} else {
				$sql .= "activity";
			}
			$sql .= ", child_flag, vote_flag, new_period, mail_send, ".
					BBS_DISPLAY_TOPIC_VALUE. " AS display ";
		} 

		$params = array($this->_request->getParameter("bbs_id"));
		$sql .=	"FROM {bbs} ".
				"WHERE bbs_id = ?";
		$bbses = $this->_db->execute($sql, $params);
		if ($bbses === false) {
			$this->_db->addError();
			return $bbses;
		}

		$default = $this->getDefaultBbs();
		$bbses[0]["expand"] = $default["expand"];
		$bbses[0]["visible_row"] = $default["visible_row"];
		$bbses[0]["display"] = $default["display"];
		
		return $bbses[0];
	}

	/**
	 * 現在配置されている掲示板データを取得する
	 *
     * @return array	配置されている掲示板データ配列
	 * @access	public
	 */
	function &getCurrentBbs() 
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT BL.block_id, BL.bbs_id, BL.display, BL.expand, BL.visible_row, ".
					"B.bbs_name, B.activity, B.topic_authority, B.child_flag, B.vote_flag, B.new_period, B.mail_send ".
				"FROM {bbs_block} BL ".
				"INNER JOIN {bbs} B ".
				"ON BL.bbs_id = B.bbs_id ".
				"WHERE BL.block_id = ?";
		$bbses = $this->_db->execute($sql, $params);
		if ($bbses === false) {
			$this->_db->addError();
		}
		if (empty($bbses)) {
			return $bbses;
		}
		
		$bbses[0]["topic_authority"] = $this->_hasTopicAuthority($bbses[0]);
		$bbses[0]["new_period_time"] = $this->_getNewPeriodTime($bbses[0]["new_period"]);

		return $bbses[0];
	}

	/**
	 * 根記事投稿権限を取得する
	 *
	 * @param	array	$bbs	掲示板状態、表示方法、根記事投稿権限の配列 
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasTopicAuthority($bbs) 
	{
		if ($bbs["activity"] != _ON) {
			return false;
		}
		
		if ($bbs["display"] == BBS_DISPLAY_OLD_VALUE) {
			return false;
		} 
		
		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= $bbs["topic_authority"]) {
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
	function &_getNewPeriodTime($new_period) 
	{
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
	 * 権限判断用のSQL文FROM句を取得する
	 *
     * @return string	権限判断用のSQL文FROM句
	 * @access	public
	 */
	function &_getAuthorityFromSQL() 
	{
		$authID = $this->_session->getParameter("_auth_id");

		$sql = "";
		if ($authID >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "LEFT JOIN {pages_users_link} PU ".
					"ON P.insert_user_id = PU.user_id ".
					"AND P.room_id = PU.room_id ".
				"LEFT JOIN {authorities} A ".
					"ON PU.role_authority_id = A.role_authority_id ";

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
		$authID = $this->_session->getParameter("_auth_id");

		$sql = "";
		if ($authID >= _AUTH_CHIEF) {
			return $sql;
		}
		
		$sql .= "AND (P.status = ? OR A.hierarchy < ? OR P.insert_user_id = ?";
		
		$defaultEntry = $this->_session->getParameter("_default_entry_flag");
		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($defaultEntry == _ON && $hierarchy > $this->_session->getParameter("_default_entry_hierarchy")) {
			$sql .= " OR A.hierarchy IS NULL) ";
		} else {
			$sql .= ") ";
		}

		$params[] = BBS_STATUS_RELEASED_VALUE;
		$params[] = $hierarchy;
		$params[] = $this->_session->getParameter("_user_id");

		return $sql;
	}

	/**
	 * 根記事の件数を取得する
	 *
     * @return string	根記事の件数
	 * @access	public
	 */
	function &getTopicCount() 
	{
		$params = array($this->_request->getParameter("bbs_id"));
		$sql = "SELECT COUNT(T.topic_id) ".
				"FROM {bbs_topic} T ".
				"INNER JOIN {bbs_post} P ".
				"ON T.topic_id = P.post_id ".
				$this->_getAuthorityFromSQL().
				"WHERE P.bbs_id = ? ".
				$this->_getAuthorityWhereSQL($params);
		$counts = $this->_db->execute($sql, $params, null, null, false);
		if ($counts === false) {
        	$this->_db->addError();
			return $counts;
		}
		
		return $counts[0][0];
	}

	/**
	 * 最新根記事IDを取得する
	 *
     * @return string	最新根記事ID
	 * @access	public
	 */
	function &getNewestTopicID() 
	{
		$params = array(
			$this->_request->getParameter("bbs_id"),
			BBS_STATUS_RELEASED_VALUE
		);
		$sql = "SELECT T.topic_id ".
				"FROM {bbs_topic} T ".
				"INNER JOIN {bbs_post} P ".
				"ON T.topic_id = P.post_id ".
				"WHERE P.bbs_id = ? ".
				"AND P.status = ? ".
				"ORDER BY P.insert_time DESC";
		$topicIDs = $this->_db->execute($sql, $params, 1, null, false);
		if ($topicIDs === false) {
        	$this->_db->addError();
			return $topicIDs;
		}
		
		return $topicIDs[0][0];
	}

	/**
	 * 根記事データ取得用のSQL文を取得する
	 *
	 * @param	string	$body	本文使用フラグ
     * @return string	根記事データ取得用のSQL文
	 * @access	public
	 */
	function &_getTopicSQL($body = false) 
	{
		$bodySelect = "";
		$bodyFrom = "";
		if ($body) {
			$bodySelect = ", BD.body ";
			$bodyFrom = "INNER JOIN {bbs_post_body} BD ".
							"ON T.topic_id = BD.post_id ";
		}

		$userID = $this->_session->getParameter("_user_id");
		$readSelect = "";
		$readFrom = "";
		if (!empty($userID)) {
			$readSelect = ",U.read_flag ";
			$readFrom = "LEFT JOIN {bbs_user_post} U ".
							"ON U.user_id = '". $userID. "' ".
							"AND U.post_id = T.topic_id ";
		} else {
			$readSelect = ",". _ON. " AS read_flag ";
		}
		
		$sql = "SELECT T.topic_id, T.newest_time, T.child_num, ".
					"P.post_id, P.bbs_id, P.bbs_id, P.parent_id, P.subject, P.icon_name, ".
					"P.contained_sign, P.vote_num, P.status, P.insert_time, P.insert_user_id, P.insert_user_name ".
					$bodySelect.
					$readSelect.
				"FROM {bbs_topic} T ".
				"INNER JOIN {bbs_post} P ".
				"ON T.topic_id = P.post_id ".
				$bodyFrom.
				$readFrom.
				$this->_getAuthorityFromSQL();

		return $sql;
	}

	/**
	 * スレッド記事配列を生成する
	 * 親ID、記事IDをKeyとした2次元配列（スレッド記事配列）及び、枝配列を生成する
	 * 親記事毎に再起処理を行う
	 * 枝配列についてT字型は兄弟の弟がある場合、L字型はない場合、I字型は親兄弟の弟がある場合、B字型はいない場合
	 *
	 * @param	array	$recordSet	親記事ID、記事IDでソートされているADORecordSet
	 * @param	array	$params[0]	$parentID:対象の親記事ID
	 * @param	array	$params[1]	$branches:枝データ配列(記事IDをkeyとした根記事まで辿った枝データ)
	 * @return array	スレッド記事配列
	 * @access	private
	 */
	function &_makeThreadArray(&$recordSet, &$params) 
	{
		// 初期処理
		$parentID = $params[0];
		$branches = $params[1];
		$parentArray = array();
		$pattern = array("/Y/", "/L/");
		$replacement = array("I", "B");

		// 元になる記事データ配列のループ
		while ($post = $recordSet->fields) {
			if (!isset($parentID)) {
				$parentID = $post["parent_id"];
			}
			if ($post["parent_id"] == $parentID) {
				// === 包含表示データの分割 ===
				$post["contained_sign"] = explode("|", $post["contained_sign"]);

				// === スレッド記事配列を生成 ===
				$parentArray[$parentID][$post["post_id"]] = $post;
				
				// === 枝配列を生成 ===
				// 次の記事を取得
				$recordSet->MoveNext();
				if ($post["parent_id"] == "0") {
					// 枝配列無し
					$branches[$post["post_id"]] = array();
					$parentArray[$parentID][$post["post_id"]]["branches"] = array();
					continue;
				} else {
					// 根記事までの枝配列（先祖）に対してT字型をI字型、L字型をB字型に変換
					$tmpBranches = preg_replace($pattern, $replacement, $branches[$post["parent_id"]]);
				}

				$nextPost = $recordSet->fields;
				if ($nextPost && $nextPost["parent_id"] == $parentID) {
					// 兄弟の弟がある場合T字型を付加
					$branches[$post["post_id"]] = array_merge($tmpBranches, array("Y"));
				} else {
					// 兄弟の弟がない場合L字型を付加
					$branches[$post["post_id"]] = array_merge($tmpBranches, array("L"));
				}
				$parentArray[$parentID][$post["post_id"]]["branches"] = $branches[$post["post_id"]];
			} else {
				// === 親記事IDが変わった場合は、変わった親記事IDを元に再帰処理 ===
				$tmpParentArray =& call_user_func(array($this, "_makeThreadArray"), $recordSet, array($post["parent_id"], $branches));
				if (!empty($tmpParentArray)) {
					$parentArray += $tmpParentArray;
				}
				return $parentArray;
			}
		}
		return $parentArray;
	}
	
	/**
	 * 根記事配列を取得する
	 *
	 * @param	string	$limi	件数
	 * @param	string	$offset	取得開始行
     * @return array	根記事配列
	 * @access	public
	 */
	function &getTopic($limit = null, $offset = null) 
	{
		$params =  array($this->_request->getParameter("bbs_id"));

		$sql = $this->_getTopicSQL().
				"WHERE P.bbs_id = ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY newest_time DESC";
		$parentID = null;
		$branches = array();
		$result = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makeThreadArray"), array($parentID, $branches));
		if ($result === false) {
        	$this->_db->addError();
		}

		return $result;
	}
	
	/**
	 * 記事データ取得用のSQL文を取得する
	 *
	 * @param	string	$body	本文使用フラグ
     * @return string	記事データ取得用のSQL文
	 * @access	public
	 */
	function &_getPostSQL($body = false) 
	{
		$bodySelect = "";
		$bodyFrom = "";
		if ($body) {
			$bodySelect = ", BD.body ";
			$bodyFrom = "INNER JOIN {bbs_post_body} BD ".
							"ON P.post_id = BD.post_id ";
		}

		$userID = $this->_session->getParameter("_user_id");
		$readSelect = "";
		$readFrom = "";
		if (!empty($userID)) {
			$readSelect = ",U.read_flag ";
			$readFrom = "LEFT JOIN {bbs_user_post} U ".
							"ON U.user_id = '". $userID. "' ".
							"AND U.post_id = P.post_id ";
		} else {
			$readSelect = ",". _ON. " ";
		}

		$sql = "SELECT P.post_id, P.bbs_id, P.bbs_id, P.topic_id, P.parent_id, P.subject, P.icon_name, ".
					"P.contained_sign, P.vote_num, P.status, P.insert_time, P.insert_user_id, P.insert_user_name ".
					$bodySelect. $readSelect. ", T.newest_time, T.child_num ".
				"FROM {bbs_post} P ".
				$bodyFrom.
				$readFrom.
				"LEFT JOIN {bbs_topic} T ".
				"ON P.post_id = T.topic_id ";

		return $sql;
	}

	/**
	 * スレッド表示用記事配列を取得する
	 *
	 * @param	string	$topicID	根記事ID
     * @return array	スレッド表示用記事配列
	 * @access	public
	 */
	function &getThread($topicID) 
	{
		$params = array($topicID);
		$sql = $this->_getPostSQL().
				$this->_getAuthorityFromSQL().
				"WHERE P.topic_id = ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY P.parent_id, P.post_id";
		$parentID = null;
		$branches = array();
		$topics = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeThreadArray"), array($parentID, $branches));
		if ($topics === false) {
        	$this->_db->addError();
		}

		return $topics;
	}

	/**
	 * 過去根記事配列を取得する
	 *
	 * @param	string	$limit		件数
	 * @param	string	$offset		取得開始行
     * @return array	過去根記事配列
	 * @access	public
	 */
	function &getOldTopic($limit = null, $offset = null) 
	{
		$params = array(
			$this->_request->getParameter("bbs_id"),
			$this->getNewestTopicID()
		);
		$sql = $this->_getTopicSQL().
				"WHERE P.bbs_id = ? ".
				"AND T.topic_id != ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY newest_time DESC";
		$parentID = null;
		$branches = array();
		$topics = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makeThreadArray"), array($parentID, $branches));
		if ($topics === false) {
        	$this->_db->addError();
		}
		return $topics;
	}

	/**
	 * 根記事IDをIN句の値として取得する
	 *
	 * @param	string	$limit	件数
	 * @param	string	$offset	取得開始行
	 * @return string	根記事IDのIN句の値
	 * @access	private
	 */
	function &_getTopicIDString($limit = null, $offset = null) 
	{
		$params = array($this->_request->getParameter("bbs_id"));
		$sql = "SELECT T.topic_id ".
				"FROM {bbs_topic} T ".
				"INNER JOIN {bbs_post} P ".
				"ON T.topic_id = P.post_id ".
				$this->_getAuthorityFromSQL().
				"WHERE P.bbs_id = ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY newest_time DESC";
		$topicIDString = $this->_db->execute($sql, $params, $limit, $offset, false, array($this, "_makeImplodeString"));
		if (empty($topicIDString)) {
        	$this->_db->addError();
        	return false;
		}
		
		return $topicIDString;
	}
		
	/**
	 * ADORecordSetの1カラム目を指定文字区切りの文字列にする
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$glue		区切り文字
	 * @return array	指定文字区切りの文字列
	 * @access	private
	 */
	function &_makeImplodeString(&$recordSet, $glue = ",") 
	{
		$string = "";
		while ($row = $recordSet->fetchRow()) {
			$string .= $row[0]. $glue;
		}
		if (!empty($glue)) {
			$string = substr($string, 0, strlen($glue) * -1);
		}

		return 	$string;	
	}

	/**
	 * 全件記事配列を取得する
	 *
	 * @param	string	$limi	件数
	 * @param	string	$offset	取得開始行
     * @return array	全件記事配列
	 * @access	public
	 */
	function &getAll($limit = null, $offset = null) 
	{
		$topicIDString = $this->_getTopicIDString($limit, $offset);
		if (empty($topicIDString)) {
        	return false;
		}
		
		$params = array();
		$sql = $this->_getPostSQL().
				$this->_getAuthorityFromSQL().
				"WHERE P.topic_id IN (". $topicIDString. ") ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY newest_time DESC, P.parent_id, P.post_id";
		$parentID = null;
		$branches = array();
		$topics = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeThreadArray"), array($parentID, $branches));
		if ($topics === false) {
        	$this->_db->addError();
		}

		return $topics;
	}

	/**
	 * フラット表示用全件記事配列を生成する
	 * 最新返信日時順の根記事ID毎に、返信日付の降順でソートされた子記事配列を並べ替える
	 *
	 * @param	array	$recordSet	根記事ID、投稿日時降順でソートされているADORecordSet
	 * @return array	フラット表示用全件記事配列
	 * @access	private
	 */
	function &_makeFlatArray(&$recordSet) 
	{
		$mobile_flag = $this->_session->getParameter("_mobile_flag");
		if ($mobile_flag == _ON) {
			$container =& DIContainerFactory::getContainer();
			$commonMain =& $container->getComponent("commonMain");
			$convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
		}

		// === 根記事ID毎にまとめる ===
		$topics = array();
		while ($row = $recordSet->fetchRow()) {
			if ($mobile_flag == _ON) {
				$row["body"] = $convertHtml->convertHtmlToText($row["body"]);
			}
			$topicID = $row["topic_id"];
			
			// === 包含表示データの分割 ===
			$row["contained_sign"] = explode("|", $row["contained_sign"]);
			
			$topics[$topicID][] = $row;
		}

		// === まとめた根記事ID毎のデータをマージ ===
		$result = array();
		foreach (array_keys($topics) as $topicID) {
			$result = array_merge($result, $topics[$topicID]);
		}

		return $result;
	}

	/**
	 * フラット表示用根記事配列を取得する
	 *
	 * @param	string	$limi	件数
	 * @param	string	$offset	取得開始行
     * @return array	フラット表示用根記事配列
	 * @access	public
	 */
	function &getFlatTopic($limit = null, $offset = null) 
	{
		$params = array($this->_request->getParameter("bbs_id"));
		$sql = $this->_getTopicSQL(true).
				"WHERE P.bbs_id = ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY newest_time DESC";
		$posts = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makeFlatArray"));
		if ($posts === false) {
        	$this->_db->addError();
		}

		return $posts;
	}

	/**
	 * フラット表示用記事配列を取得する
	 *
	 * @param	string	$topicID	根記事ID
     * @return array	フラット表示用記事配列
	 * @access	public
	 */
	function &getFlat($topicID) 
	{
		$params = array($topicID);
		$sql = $this->_getPostSQL(true).
				$this->_getAuthorityFromSQL().
				"WHERE P.topic_id = ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY T.topic_id DESC, insert_time DESC";
		$posts = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeFlatArray"));
		if ($posts === false) {
        	$this->_db->addError();
		}

		return $posts;
	}

	/**
	 * 過去根記事配列を取得する
	 *
	 * @param	string	$limit		件数
	 * @param	string	$offset		取得開始行
     * @return array	過去根記事配列
	 * @access	public
	 */
	function &getFlatOldTopic($limit = null, $offset = null) 
	{
		$params = array(
			$this->_request->getParameter("bbs_id"),
			$this->getNewestTopicID()
		);
		$sql = $this->_getTopicSQL(true).
				"WHERE P.bbs_id = ? ".
				"AND T.topic_id != ? ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY newest_time DESC";
		$posts = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makeFlatArray"));
		if ($posts === false) {
        	$this->_db->addError();
		}

		return $posts;
	}

	/**
	 * フラット表示用全件記事配列を取得する
	 *
	 * @param	string	$limi	件数
	 * @param	string	$offset	取得開始行
     * @return array	フラット表示用全件記事配列
	 * @access	public
	 */
	function &getFlatAll($limit = null, $offset = null) 
	{
		$topicIDString = $this->_getTopicIDString($limit, $offset);
		if (empty($topicIDString)) {
        	return false;
		}

		$params = array();
		$sql = $this->_getPostSQL(true).
				$this->_getAuthorityFromSQL().
				"WHERE P.topic_id IN (". $topicIDString. ") ".
				$this->_getAuthorityWhereSQL($params).
				"ORDER BY newest_time DESC, P.topic_id DESC, insert_time DESC";

		$posts = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeFlatArray"));
		if ($posts === false) {
        	$this->_db->addError();
		}

		return $posts;
	}

	/**
	 * 記事が存在するか判断する
	 *
	 * @param	string	$postID	記事ID
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function postExists($postID) 
	{
		$params = array(
			$this->_request->getParameter("bbs_id"),
			$postID
		);
		$sql = "SELECT post_id ".
				"FROM {bbs_post} ".
				"WHERE bbs_id = ? ".
				"AND post_id = ?";
		$postIDs = $this->_db->execute($sql, $params);
		if ($postIDs === false) {
			$this->_db->addError();
			return $postIDs;
		}
		
		if (count($postIDs) > 0) {
			return true;
		}
		
		return false;
	}

	/**
	 * 根記事IDを取得する
	 *
	 * @param	string	$postID	記事ID
     * @return string	根記事ID
	 * @access	public
	 */
	function &getTopicID($postID) 
	{
		$params = array($postID);
		$sql = "SELECT topic_id ".
				"FROM {bbs_post} ".
				"WHERE post_id = ?";
		$topicIDs = $this->_db->execute($sql, $params, null, null, false);
		if ($topicIDs === false) {
        	$this->_db->addError();
        	return $topicIDs;
		}
		
		return $topicIDs[0][0];
	}

	/**
	 * 参照可能な子記事が存在するか判断する
	 *
	 * @param	string	$postID	記事ID
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function viewableChildExists($postID) 
	{
		$params = array($postID);
		$sql = "SELECT post_id ".
				"FROM {bbs_post} P ".
				$this->_getAuthorityFromSQL().
				"WHERE parent_id = ? ".
				$this->_getAuthorityWhereSQL($params);
		$postIDs = $this->_db->execute($sql, $params, 1);
		if ($postIDs === false) {
        	$this->_db->addError();
			return $postIDs;
		}
		
		if (count($postIDs) > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * 記事内容を取得する
	 *
	 * @param	string	$postID	記事ID
	 * @return array	記事内容配列
	 * @access	public
	 */
	function &getPost($postID) 
	{
		$params = array($postID);
		$sql = $this->_getPostSQL(true).
				$this->_getAuthorityFromSQL().
				"WHERE P.post_id = ? ".
				$this->_getAuthorityWhereSQL($params);
		$posts = $this->_db->execute($sql, $params);
		if (empty($posts)) {
        	$this->_db->addError();
			return $posts;
		}
		
		$post = $posts[0];
		$bbs = $this->getBbs($post["bbs_id"]);
		if (empty($bbs)) {
        	$this->_db->addError();
			return $bbs;
		}

		$post["contained_sign"] = explode("|", $post["contained_sign"]);
		
		$post["activity"] = $bbs["activity"];
		$post["child_flag"] = $bbs["child_flag"];
		$post["vote_flag"] = $bbs["vote_flag"];
		$post["new_period"] = $bbs["new_period"];
		
		$post["edit_authority"] = $this->_hasEditAuthority($post);
		$post["reply_authority"] = $this->_hasReplyAuthority($post);
		$post["vote_authority"] = $this->_hasVoteAuthority($post);
		$post["temporary_authority"] = $this->_hasTemporaryAuthority($post);
		
		return $post;
	}

	/**
	 * 記事編集権限を取得する
	 *
	 * @param	array	$post	記事ID、根記事ID、投稿者ID、動作の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasEditAuthority($post) 
	{
		if ($post["activity"] != _ON) {
			return false;
		}
		
		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}
		
		$bbs = $this->_request->getParameter("bbs");
		if ($post["post_id"] == $post["topic_id"]
				&& !$bbs["topic_authority"]) {
			return false;
		}
		
		if ($this->childExists($post["post_id"])) {
			return false;
		}
		
		$userID = $this->_session->getParameter("_user_id");
		if ($userID == $post["insert_user_id"]) {
			return true;
		} 
		
		$container =& DIContainerFactory::getContainer();
		$authCheck =& $container->getComponent("authCheck");
		
		$roomID = $this->_request->getParameter("room_id");
		$postHierarchy = $authCheck->getPageHierarchy($post["insert_user_id"], $roomID);
		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($hierarchy > $postHierarchy) {
			return true;
		}
		
		return false;
	}

	/**
	 * 返信権限を取得する
	 *
	 * @param	array	$post	記事ID、投稿者ID、動作、返信有無の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasReplyAuthority($post) 
	{
		if ($post["activity"] != _ON) {
			return false;
		}
		
		if ($post["child_flag"] != _ON) {
			return false;
		}
		
		if ($post["status"] != BBS_STATUS_RELEASED_VALUE) {
			return false;
		}

		$authID = $this->_session->getParameter("_auth_id");
		if ($authID <= _AUTH_GUEST) {
			return false;
		}
		
		return true;
	}

	/**
	 * 投票権限を取得する
	 *
	 * @param	array	$post	記事ID、状態、投稿者ID、動作の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasVoteAuthority($post) 
	{
    	if ($post["status"] != BBS_STATUS_RELEASED_VALUE) {
			return false;
		}
		
		if ($post["activity"] != _ON) {
			return false;
		}
		
		if ($post["vote_flag"] != _ON) {
			return false;
		}

		$votes = $this->_session->getParameter("bbs_votes");
		if (!empty($votes) && in_array($post["post_id"], $votes)) {
			return false;
    	}

		$userID = $this->_session->getParameter("_user_id");
		if (empty($userID)) {
			return true;
		}
		
		$params = array(
			$userID,
			$post["post_id"]
		);
		$sql = "SELECT vote_flag ".
				"FROM {bbs_user_post} ".
				"WHERE user_id = ? ".
				"AND post_id = ?";
		$voteFlags = $this->_db->execute($sql, $params, null, null, false);
		if ($voteFlags === false) {
        	$this->_db->addError();
			return false;
		}

		if (empty($voteFlags) || $voteFlags[0][0] != _ON) {
			return true;
		}
		
		return false;
	}

	/**
	 * 一時保存権限を取得する
	 *
	 * @param	array	$post	記事ID、動作の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasTemporaryAuthority($post) 
	{
		if ($post["activity"] != _ON) {
			return false;
		}
		
		if ($this->childExists($post["post_id"])) {
			return false;
		}

		return true;
	}

	/**
	 * 子記事が存在するか判断する
	 *
	 * @param	string	$postID	記事ID
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function childExists($postID) 
	{
		$params = array($postID);
		$sql = "SELECT post_id ".
				"FROM {bbs_post} ".
				"WHERE parent_id = ?";
		$postIDs = $this->_db->execute($sql, $params, 1);
		if ($postIDs === false) {
        	$this->_db->addError();
			return $postIDs;
		}
		
		if (count($postIDs) > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * 親記事ID、子記事ID、前記事ID、後記事IDを取得する
	 *
	 * @return array	親記事ID、子記事ID、前記事ID、後記事IDの配列
	 * @access	public
	 */
	function &getMoveData() 
	{
		$postID = $this->_request->getParameter("post_id");
		$params = array($postID);
		$postIDArray = array(0, 0, 0, 0);
		
		$authorityFromSQL = $this->_getAuthorityFromSQL();
		$authorityWhereSQL = $this->_getAuthorityWhereSQL($params);

		// 親記事IDの取得
		$sql = "SELECT parent_id ".
				"FROM {bbs_post} P ".
				$authorityFromSQL.
				"WHERE post_id = ? ".
				$authorityWhereSQL;
		$result = $this->_db->execute($sql, $params, null, null, false);
		if (!$result) {
			$this->_db->addError();
			return $result;
		}
		$postIDArray[0] = $result[0][0];
		
		// 子記事IDの取得
		$sql = "SELECT post_id ".
				"FROM {bbs_post} P ".
				$authorityFromSQL.
				"WHERE parent_id = ? ".
				$authorityWhereSQL.
				"ORDER BY post_id";
		$result = $this->_db->execute($sql, $params, 1, null, false);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if (count($result) > 0) {
			$postIDArray[1] = $result[0][0];
		}

		if (empty($postIDArray[0])) {
			return $postIDArray;
		}
		
		$params = array(
			$postIDArray[0],
			$postID
		);

		$authorityWhereSQL = $this->_getAuthorityWhereSQL($params);

		// 前記事IDの取得
		$sql = "SELECT post_id ".
				"FROM {bbs_post} P ".
				$authorityFromSQL.
				"WHERE parent_id = ? ".
				"AND post_id < ? ".
				$authorityWhereSQL.
				"ORDER BY post_id DESC";
		$result = $this->_db->execute($sql, $params, 1, null, false);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if (count($result) > 0) {
			$postIDArray[2] = $result[0][0];
		}

		// 後記事IDの取得
		$sql = "SELECT post_id ".
				"FROM {bbs_post} P ".
				$authorityFromSQL.
				"WHERE parent_id = ?".
				"AND post_id > ? ".
				$authorityWhereSQL.
				"ORDER BY post_id";
		$result = $this->_db->execute($sql, $params, 1, null, false);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if (count($result) > 0) {
			$postIDArray[3] = $result[0][0];
		}
		
		return $postIDArray;
	}

	/**
	 * メール送信データを取得する
	 *
	 * @param	string	$postID	記事ID
	 * @return array	メール送信データ配列
	 * @access	public
	 */
	function &getMail($postID) 
	{
		$params = array($postID);
		$sql = "SELECT P.subject, P.status, P.insert_time, P.insert_user_name, BD.body, ".
						"B.bbs_name, B.mail_send, B.mail_authority, B.mail_subject, B.mail_body ".
				"FROM {bbs_post} P ".
				"INNER JOIN {bbs_post_body} BD ".
				"ON P.post_id = BD.post_id ".
				"INNER JOIN {bbs} B ".
				"ON P.bbs_id = B.bbs_id ".
				"WHERE P.post_id = ?";
		$mails = $this->_db->execute($sql, $params);
		if ($mails === false) {
			$this->_db->addError();
			return $mails;
		}
		
		return $mails[0];
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($block_id_arr) 
	{
    	$sql = "SELECT bbs.*, block.block_id, block.display, block.expand, block.visible_row" .
    			" FROM {bbs} bbs" .
    			" INNER JOIN {bbs_block} block ON (bbs.bbs_id=block.bbs_id)" .
    			" WHERE block.block_id IN (".implode(",", $block_id_arr).")" .
    			" ORDER BY block.insert_time DESC, block.bbs_id DESC";
    	
        return $this->_db->execute($sql, null, null, null, true);
	}
}
?>
