<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [pm表示用クラス]
 */
class Pm_Components_View
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
	 * @var sessionを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var pmFilterDisplayオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_pmFilterDisplay = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pm_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_pmFilterDisplay =& $this->_container->getComponent("pmFilterDisplay");
	}

	/**
	 * メッセージ件数を取得する
	 *
     * @return string	メッセージ件数
	 * @access	public
	 */
	function &getMessageCount($action = "common")
	{
		$query = &$this->generateMessagesQuery($action, true);
		$counts = $this->_db->execute($query['sql'], $query['params'], null, null, true, null);

		if (!$counts) {
			$this->_db->addError();
		}
		return $counts[0]["cnt"];
	}

	/**
	 * メッセージデータ配列を取得する
	 *
     * @return array	メッセージデータ配列
	 * @access	public
	 */
	 function &getMessages($query = false, $limit = null, $offset = null)
	 {
	    if(!$query){
			$query = &$this->generateMessagesQuery();
		}
		$messages = $this->_db->execute($query['sql'], $query['params'], $limit, $offset, true, array($this, "_makeMessageArray"));
		if (!$messages) {
			$this->_db->addError();
		}

		return $messages;
	 }

	/**
	 * メッセージ検索用データを取得する
	 *
     * @return array	メッセージ検索用データ
	 * @access	public
	 */
	 function &generateMessagesQuery($action = 'common', $count = false){
	 	$query = array();
		$sortColumn = $this->_request->getParameter("sort_col");
		$sortDirection = $this->_request->getParameter("sort_dir");

		if (!in_array($sortColumn, array('r.insert_user_name', 'm.receivers_list', 'm.subject', 'm.sent_time'))) {
			$sortColumn = "m.sent_time";
		}
		if ((empty($sortDirection) || ($sortDirection != 'ASC' && $sortDirection != 'asc'))) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		if($count){
			$sql = "SELECT COUNT(*) AS cnt ";
		}else{
			$sql = "SELECT distinct r.receiver_id, m.message_id, m.subject, m.sent_time, m.image_count, ".
				   "m.file_count, m.insert_user_id, m.insert_user_name, m.receivers_list, m.send_all_flag, r.mailbox, ".
				   "r.read_state, r.importance_flag, r.delete_state ";
		}

		switch($action){
			case 'search':
				$search = $this->_session->getParameter("search");

				$search_sender = $search["search_sender"];
				$search_cc = $search["search_cc"];
				$search_subject = $search["search_subject"];
				$search_keywords = $search["search_keywords"];
				$search_date_from = $search["search_date_from"];
				$search_date_to = $search["search_date_to"];
				$search_upload_flag = $search["search_upload_flag"];
				$search_range = $search["search_range"];

				$search_range_prefix = '';
				$search_range_value = '';
				if(!empty($search_range)){
					$search_range_pair = explode(PM_SPLIT_COLON, $search_range);
					$search_range_prefix = strtoupper($search_range_pair[0]);
					$search_range_value = $search_range_pair[1];
				}

				// if(!empty($search_sender) || !empty($search_cc)){
				if(!empty($search_cc)){
					$innerJoinSql = " INNER JOIN {pm_message_receiver} r2 ON r.message_id = r2.message_id ";
				}else{
					$innerJoinSql = "";
				}

				if($search_range_prefix === "TAG"){
					$params = array(
						"tag_user_id" => $this->_session->getParameter("_user_id"),
						"tag_id" => intval($search_range_value),
					);

				    $sql .= "FROM {pm_message_receiver} r " . $innerJoinSql . ", ".
							"{pm_message} m, {pm_message_tag_link} m2t, {pm_tag} t ".
				   		    "WHERE r.message_id = m.message_id ".
				            "AND r.receiver_id = m2t.receiver_id " .
				            "AND m2t.tag_id = t.tag_id ".
							"AND (t.insert_user_id = ? AND m2t.tag_id = ?) ";
				}else{
					$params = array();
					$sql .= "FROM {pm_message_receiver} r " . $innerJoinSql . ", {pm_message} m ".
						    "WHERE r.message_id = m.message_id ";

					switch($search_range_prefix){
						case 'MAILBOX':
							$search_mailbox = intval($search_range_value);

							if($search_mailbox == PM_LEFTMENU_TRASHBOX){
								$params["is_trashbox"] = _ON;
								$sql .= "AND r.delete_state = ? ";
							}else{
								$params["mailbox"] = $search_mailbox;
								$params["is_trashbox"] = _OFF;
								$sql .= "AND r.mailbox = ? AND r.delete_state = ? ";
							}
							break;

						case 'FLAG':
							$params["flag"] = intval($search_range_value);
							$sql .= "AND r.importance_flag = ? ";
							break;

						case 'READSTATE':
							$params["read_state"] = intval($search_range_value);
							$sql .= "AND r.read_state = ? ";
							break;
					}
				}

				$params["delete_state"] = PM_MESSAGE_STATE_DELETE;
				$params["receiver_user_id"] = $this->_session->getParameter("_user_id");
				$sql .= "AND r.delete_state < ? AND r.receiver_user_id = ? ";

				if(!empty($search_sender) && !empty($search_cc)){
					$params["sender_user_name"] = $search_sender;
					$params["receiver_user_name"] = $search_cc;

					$params["receiver1_mailbox1"] = PM_LEFTMENU_OUTBOX;
					$params["receiver1_mailbox2"] = PM_LEFTMENU_TRASHBOX;

					$sql .= "AND (m.insert_user_name = ? OR (r2.receiver_user_name = ? AND (r.mailbox = ? OR r.mailbox = ?))) ";
				}else{
					if(!empty($search_sender)){
						$params["sender_user_name"] = $search_sender;
						$sql .= "AND m.insert_user_name = ? ";
					}else{
						if(!empty($search_cc)){
							$params["receiver_user_name"] = $search_cc;
							$params["receiver1_mailbox1"] = PM_LEFTMENU_OUTBOX;
							$params["receiver1_mailbox2"] = PM_LEFTMENU_TRASHBOX;
							$sql .= "AND r2.receiver_user_name = ? AND (r.mailbox = ? OR r.mailbox = ?) ";
						}
					}
				}

				if(!empty($search_date_from) || !empty($search_date_to)){
					$sql_date_format = str_replace(array('Y', 'm', 'd', '/'), array('%Y', '%m', '%d', ''), _DATE_FORMAT);
					if(!empty($search_date_from)){
						$params["insert_time_from"] = $search_date_from;
						$sql .= "AND date_format(r.insert_time, '" . $sql_date_format . "') >= ? ";
					}

					if(!empty($search_date_to)){
						$params["insert_time_to"] = $search_date_to;
						$sql .= "AND date_format(r.insert_time, '" . $sql_date_format . "') <= ? ";
					}
				}

				if($search_upload_flag == _ON){
					$sql .= "AND (m.file_count > 0 OR m.image_count > 0) ";
				}

				if(!empty($search_subject)){
					$params["subject"] = "%" . trim($search_subject) . "%";
					$sql .= "AND m.subject LIKE ? ";
				}

				if(!empty($search_keywords)){
					$the_keywords = explode(",", $search_keywords);
					$keywords = array();
					for($i = 0; $i < count($the_keywords); $i++){
						$the_keyword = trim($the_keywords[$i]);
						if(!empty($the_keyword) && !in_array($the_keyword, $keywords)){
							$keywords[] = $the_keyword;
						}
					}

					$keywords_cnt = count($keywords);
					$j = 0;
					for($i = 0; $i < $keywords_cnt; $i++){
						if($i == 0){ $sql .= "AND ( "; }
						//$params["keyword" . $j++] = '%' . strtoupper($keywords[$i]) . '%';
						$params["keyword" . $j++] = '%' . strtoupper($keywords[$i]) . '%';
						$sql .= "(upper(m.body) LIKE ?) ";

						if($i == ($keywords_cnt - 1)){
							$sql .= ") ";
						}else{
							$sql .= "OR ";
						}
					}
				}

				$filter = $this->_request->getParameter("filter");
				$filterSql = "";

				// 絞り込みSQLを取得
				if (!empty($filter)) {
					$filterSql = $this->_getFilterSql($filter);
				}

				$sql = $sql.$filterSql;
				if(!$count){
					$sql .= $this->_db->getOrderSQL($orderParams);
				}

				$query['sql'] = $sql;
				$query['params'] = $params;
				break;

			case 'common':
			default:
				$mailbox = $this->_request->getParameter("mailbox");
				$filter = $this->_request->getParameter("filter");
				$filterSql = "";

				// 絞り込みSQLを取得
				if (!empty($filter)) {
					$filterSql = $this->_getFilterSql($filter);
				}

				// mailboxが空時、メッセージ所在トレイデフォルト値設定
				if (empty($mailbox)) {
					$mailbox = PM_LEFTMENU_INBOX;
				}

				$sql .= "FROM {pm_message_receiver} r, {pm_message} m ".
					   "WHERE r.message_id = m.message_id AND ".
					   "r.receiver_user_id = ? AND ".
					   "r.delete_state = ? ";

				// 検索条件設定
				$params = array(
					"user_id" => $this->_session->getParameter("_user_id")
				);

				if ($mailbox == PM_LEFTMENU_TRASHBOX) {
					$params["delete_state"] = PM_MESSAGE_STATE_TRASH;
				} else {
					$params["delete_state"] = PM_MESSAGE_STATE_NORMAL;
					$params["mailbox"] = $mailbox;
					$sql = $sql."AND mailbox = ? ";
				}

				$sql = $sql.$filterSql;
				if(!$count){
					$sql .= $this->_db->getOrderSQL($orderParams);
				}

				$query['sql'] = $sql;
				$query['params'] = $params;
				break;
		}

		return $query;
	 }

	/**
	 * メッセージデータ配列を生成する
	 *
	 * @param	array	$recordSet	メッセージADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	メッセージデータ配列
	 * @access	private
	 */
	function &_makeMessageArray(&$recordSet, $format = _SHORT_DATE_FORMAT) {
	    $messages = array();

		$mailbox = $this->_request->getParameter("mailbox");

		// mailboxが空時、メッセージ所在トレイデフォルト値設定
		if (empty($mailbox)) {
			$mailbox = PM_LEFTMENU_INBOX;
		}

		$receivers_list = $this->_request->getParameter("receiver_list");
		$receivers = split(",",$receivers_list);

		while ($row = $recordSet->fetchRow()) {
            $row["sent_time"] = $this->_getDate($row["sent_time"]);

			// タグを設定
			$row["hasTag"] = false;
			$tags = $this->getTagByMessage($row["receiver_id"]);
			if (!empty($tags)) {
                $row["hasTag"] = true;
			}
			$row["tags"] = $tags;

			// ハンドル名を設定
			if ($mailbox == PM_LEFTMENU_INBOX || $mailbox == PM_LEFTMENU_TRASHBOX) {
				$row["first_user_id"] = $row["insert_user_id"];
				$row["handle"] = $row["insert_user_name"];

			} elseif ($mailbox == PM_LEFTMENU_OUTBOX || $mailbox == PM_LEFTMENU_STOREBOX) {
				if($row["send_all_flag"] == _OFF){
					$row["first_user_id"] = $this->_getFirstUserId($row["receivers_list"]);
					$row["handle"] = $this->_getReceivers($row["receivers_list"]);
				}
			} elseif ($mailbox == PM_LEFTMENU_SEARCH) {
				if($row["send_all_flag"] == _OFF){
					$row["first_user_id"] = $this->_getFirstUserId($row["receivers_list"]);
					$row["handle"] = $this->_getReceivers($row["receivers_list"]);
				}
			}

			// 選択されだメッセージを設定
			$row["checked"] = false;
			if ($receivers != null && is_array($receivers)) {
				if (in_array($row["receiver_id"],$receivers)) {
					$row["checked"] = true;
				}
			}

			if ($row["mailbox"] == PM_LEFTMENU_STOREBOX) {
				$row['first_receiver'] = "";
				$row['cc_receivers'] = array();
				$this_receivers = split(",", $row["receivers_list"]);

				if(is_array($this_receivers)){
					for($i = 0; $i < sizeof($this_receivers); $i++){
						$receiver = split("\|",$this_receivers[$i]);
						if(is_array($receiver) && !empty($receiver[0])){
							if($i == 0){
								$row['first_receiver'] = $receiver[0];
							}else{
								$row['cc_receivers'][] = $receiver[0];
							}
						}
					}
				}
			}

			$messages[] = $row;
		}

		return $messages;
	}

	/**
	 * 詳細メッセージデータを取得する
	 *
     * @return array	メッセージデータ
	 * @access	public
	 */
	function &getMessage($receiver_id = 0)
	{
		if(!$receiver_id){
			$receiver_id = $this->_request->getParameter("receiver_id");
		}

		$params = array(
			$receiver_id
		);

		$sql = "SELECT r.receiver_id, r.receiver_user_id, r.receiver_user_name, m.message_id, ".
			   "m.reply_top_message_id, m.reply_last_message_id, m.subject, m.body, m.insert_user_id, ".
			   "m.insert_user_name, m.sent_time, m.image_count, m.file_count, ".
			   "m.receivers_list, m.send_all_flag, r.mailbox, r.read_state, r.importance_flag, r.delete_state ".
		       "FROM {pm_message_receiver} r, {pm_message} m ".
			   "WHERE r.message_id = m.message_id AND ".
			   "r.receiver_id = ?";

		$messages = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeDetailMessageArray"));
		if ($messages === false) {
			$this->_db->addError();
		}

		if(isset($messages[0])){
			return $messages[0];
		}else{
			$message = array();
			return $message;
		}
	}

	function getMessageById($message_id = 0)
	{
		if(!$message_id){
			$message_id = $this->_request->getParameter("message_id");
		}

		$params = array(
			$message_id
		);

		$sql = "SELECT m.message_id, m.reply_top_message_id, m.reply_last_message_id, m.subject, m.body, m.insert_user_id, ".
			   "m.insert_time, m.insert_user_name, m.sent_time, m.image_count, m.file_count, m.receivers_list, m.send_all_flag ".
		       "FROM {pm_message} m ".
			   "WHERE m.message_id = ?";

		$message = $this->_db->execute($sql, $params);
		if ($message === false) {
			$this->_db->addError();
			return false;
		}

		if(isset($message[0])){
			return $message[0];
		}else{
			return false;
		}
	}

	function getMessageReceiverId($message_id = 0){
		$user_id = $this->_session->getParameter("_user_id");

		$params = array(
			"receiver_user_id" => $user_id,
			"message_id" => $message_id
		);
		$sql = "SELECT receiver_id FROM {pm_message_receiver} ".
			   "WHERE receiver_user_id = ? AND message_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		if(isset($result[0]["receiver_id"])){
			return $result[0]["receiver_id"];
		}else{
			return false;
		}
	}

    /**
	 * 詳細メッセージデータ配列を生成する
	 *
	 * @param	array	$recordSet	メッセージADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @return array	メッセージデータ配列
	 * @access	private
	 */
	function &_makeDetailMessageArray(&$recordSet)
	{
        $messages = array();
		while ($row = $recordSet->fetchRow()) {
			$row["handles"] = array();

            $row["sent_time"] = $this->_getDate($row["sent_time"],_SHORT_FULL_DATE_FORMAT);

			// タグ設定
			$row["hasTag"] = false;
			$row["tags"] = $this->getTagByMessage($row["receiver_id"]);

			// ハンドル名を設定
			if ($row["mailbox"] == PM_LEFTMENU_INBOX || $row["mailbox"] == PM_LEFTMENU_TRASHBOX) {
				$row["first_user_id"] = $row["insert_user_id"];
				$row["handle"] = $row["insert_user_name"];
			} elseif ($row["mailbox"] == PM_LEFTMENU_OUTBOX || $row["mailbox"] == PM_LEFTMENU_STOREBOX) {
				$row["first_user_id"] = $this->_getFirstUserId($row["receivers_list"]);
				$row["handle"] = $this->_getReceivers($row["receivers_list"]);
			}

			/* elseif ($row["mailbox"] == PM_LEFTMENU_SEARCH) {
				$row["first_user_id"] = $this->_getFirstUserId($row["receivers_list"]);
				$row["handle"] = $this->_getReceivers($row["receivers_list"]);
			}
			*/

			if (!empty($row["tags"])) {
                $row["hasTag"] = true;
			}

			if ($row["delete_state"] == PM_MESSAGE_STATE_NORMAL || $row["delete_state"] == PM_MESSAGE_STATE_TRASH) {
				if ($row["mailbox"] == PM_LEFTMENU_INBOX) {
					$row["handle"] = $row["insert_user_name"];

					$row["handles"][$row["insert_user_id"]] = array('handle' => $row["insert_user_name"], 'end' => true);
				} elseif ($row["mailbox"] == PM_LEFTMENU_OUTBOX || $row["mailbox"] == PM_LEFTMENU_STOREBOX) {
					$row["handle"] = $this->_getReceivers($row["receivers_list"]);

					if($row["send_all_flag"] == _OFF){
						$receivers = split(',',$row["receivers_list"]);
						if(is_array($receivers)){
							$receivers_cnt = sizeof($receivers);
							$receivers_index = 1;

							foreach($receivers as $receiver){
								if(!empty($receiver)) {
									$receiver_pairs = split('\|',$receiver);
									if($receivers_index < $receivers_cnt){
										$row["handles"][$receiver_pairs[1]] = array('handle' => $receiver_pairs[0], 'end' => false);
									}else{
										$row["handles"][$receiver_pairs[1]] = array('handle' => $receiver_pairs[0], 'end' => true);
									}
									$receivers_index++;
								}
							}
						}
					}

					if ($row["mailbox"] == PM_LEFTMENU_STOREBOX) {
						$row['first_receiver'] = "";
						$row['cc_receivers'] = array();
						$this_receivers = split(",", $row["receivers_list"]);

						if(is_array($this_receivers)){
							for($i = 0; $i < sizeof($this_receivers); $i++){
								$receiver = split("\|",$this_receivers[$i]);
								if(is_array($receiver) && !empty($receiver[0])){
									if($i == 0){
										$row['first_receiver'] = $receiver[0];
									}else{
										$row['cc_receivers'][] = array('name' => $receiver[0], 'id' => $receiver[1]);
									}
								}
							}
						}
					}

				}
			}
			/* else if ($row["delete_state"] == PM_MESSAGE_STATE_TRASH) {
			    //　ごみメッセージ場合
				$row["handle"] = $row["insert_user_name"];
				$row["handles"][$row["insert_user_id"]] = array('handle' => $row["insert_user_name"], 'end' => true);
			}
			*/

			$messages[] = $row;
		}
		return $messages;
	}

	/**
	 * タグデータを取得する
	 *
     * @return array	タグデータ
	 * @access	public
	 */
	function &getTagByMessage($receiver_id)
	{
		$params = array(
			$receiver_id
		);

		$sql = "SELECT t.tag_id, t.tag_name ".
			   "FROM {pm_message_tag_link} m, {pm_tag} t ".
			   "WHERE m.tag_id = t.tag_id AND ".
			   "m.receiver_id = ? ";

		$records = $this->_db->execute($sql, $params, null, null, true, null);

		if ($records == false) {
			$this->_db->addError();
		}

		$tags = "";

	    for ($i = 0; $i < sizeof($records); $i++) {
		    if (empty($tags)) {
			    $tags = $tags.$records[$i]["tag_name"];
			} else {
			    $tags = $tags.",".$records[$i]["tag_name"];
			}
		 }
		return $tags;
	}

	/**
	 * タグデータ配列を取得する
	 *
     * @return array	タグデータ配列
	 * @access	public
	 */
	function &getTags()
	{
		$params = array(
			$this->_session->getParameter("_user_id")
		);

		$sql = "SELECT tag_id, tag_name ".
			   "FROM {pm_tag} ".
			   "WHERE insert_user_id = ? ";

		$tags = $this->_db->execute($sql, $params, null, null, true, null);

		if ($tags === false) {
			$this->_db->addError();
		}
		return $tags;
	}

    /**
	 * タグ詳細情報データを取得する
	 *
     * @return array	タグデータ
	 * @access	public
	 */
	function &getTag($tag_id = null, $misc = true)
	{
		if($tag_id == null){
			$tag_id = $this->_request->getParameter("tag_id");
		}

		if($misc){
			$receiver_list = $this->_request->getParameter("receiver_list");
			$mailbox = $this->_request->getParameter("mailbox");
			$top_el_id = $this->_request->getParameter("top_el_id");
		}

		$params = array(
			$tag_id
		);

		$sql = "SELECT tag_id, tag_name ".
			   "FROM {pm_tag} ".
			   "WHERE tag_id = ?";

		$tags = $this->_db->execute($sql, $params, 1);
		if ($tags === false) {
			$this->_db->addError();
			$tags = array();
		}

		if($misc){
			$tags[0]["receiver_list"] = $receiver_list;
			$tags[0]["mailbox"] = $mailbox;
			$tags[0]['top_el_id'] = $top_el_id;
		}

		if(!isset($tags[0])){
			$tag = array();
			return $tag;
		}
		return $tags[0];
	}

	/**
	 * タグIDのタグ件数を取得する
	 *
     * @return string	タグ件数
	 * @access	public
	 */
	function getTagCount()
	{
    	$params["tag_id"] = $this->_request->getParameter("tag_id");
    	$count = $this->_db->countExecute("pm_tag", $params);

		return $count;
	}

	/**
	 * 日付を取得する
	 *
     * @return array	日付データ
	 * @access	public
	 */
	 function &_getDate($dateTime,$format = _SHORT_DATE_FORMAT)
	 {
	     $tempDate = timezone_date_format($dateTime, null);

		 if (substr($tempDate, 8) == "000000") {
				$previousDay = -1;
				$format = str_replace("H", "24", $format);
		 } else {
				$previousDay = 0;
		 }

		 $date = mktime(intval(substr($tempDate, 8, 2)),
						intval(substr($tempDate, 10, 2)),
						intval(substr($tempDate, 12, 2)),
						intval(substr($tempDate, 4, 2)),
						intval(substr($tempDate, 6, 2)) + $previousDay,
						intval(substr($tempDate, 0, 4)));

         $formatDate = date($format, $date);

		 return $formatDate;
	 }

	/**
	 * ユーザ名を取得する
	 *
     * @return string	ユーザ名データ
	 * @access	private
	 */
	 function &_getReceivers($receivers_list)
	 {
	     $userName = "";
	     $receivers = split(',',$receivers_list);

		 for ($i = 0; $i < sizeof($receivers); $i++) {
		     $receiver = split('\|',$receivers[$i]);
			 if (empty($userName)) {
			     $userName = $userName.$receiver[0];
			 } else {
			     $userName = $userName.",".$receiver[0];
			 }
		 }
		 return $userName;
	 }

	/**
	 * ユーザIDを取得する
	 *
     * @return string	ユーザIDデータ
	 * @access	private
	 */
	 function &_getFirstUserId($receivers_list){
		 $userId = "";
	     $receivers = split(',',$receivers_list);
		 if(is_array($receivers) && isset($receivers[0])){
		 	$first_receiver = $receivers[0];
			$receiver_pairs = split('\|',$first_receiver);
			if(is_array($receiver_pairs) && isset($receiver_pairs[1])){
				$userId = $receiver_pairs[1];
			}
		 }
		 return $userId;
	 }

	/**
	 * 絞り込みSQLを取得する
	 *
     * @return string	絞り込みSQL
	 * @access	private
	 */
	 function &_getFilterSql($filter)
	 {
	     $filterSql = "";

		 if (empty($filter)) {

		 	$filterSql = "";

		 } elseif ($filter == PM_FILTER_READ) {

		 	$filterSql = " AND read_state = ".PM_READ_STATE;

		 } elseif ($filter == PM_FILTER_UNREAD) {

		 	$filterSql = " AND read_state = ".PM_UNREAD_STATE;

		 } elseif ($filter == PM_FILTER_HAVE_FLAG) {

			$filterSql = " AND importance_flag = ".PM_IMPORTANCE_FLAG;

		 } elseif ($filter == PM_FILTER_NO_FLAG) {

			$filterSql = " AND importance_flag = ".PM_NO_FLAG;

		 } else {
		 	$tagFilter = split('_',$filter);
			if (sizeof($tagFilter) == 2) {
				if ($tagFilter[0] == PM_FILTER_TAG) {
					$tag_id = $tagFilter[1];
					$filterSql = " AND receiver_id IN (SELECT receiver_id FROM {pm_message_tag_link} WHERE tag_id = " . (int)$tag_id . ")";
				}
			}
		 }

		 return $filterSql;
	 }

	/**
	 * メッセージ転送設定情報を取得する
	 *
	 * @param string $userId ユーザーID 
	 * @return array メッセージ転送設定情報データ配列
	 * @access	public
	 */
	function getForwardState($userId)
	{
		$params = array(
			$userId
		);
		$sql = "SELECT forward_state "
				. "FROM {pm_forward} "
				. "WHERE insert_user_id = ?";
		$forwards = $this->_db->execute($sql, $params, null, null, false);
		if ($forwards === false) {
			$this->_db->addError();
			return false;
		}

		$forwardState = null;
		if (empty($forwards)) {
			return $forwardState;
		}

		$forwardState = $forwards[0][0];
		return $forwardState;
	}

	/**
	 * PM権限判定
	 *
     * @return boolean
	 * @access	public
	 */
	 function _hasPmAuthority($pm)
	 {
		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= $pm["pm_authority"]) {
			return true;
		}

		return false;
	 }

	/**
	 * PMアクセス権限判定
	 *
     * @return boolean
	 * @access	public
	 */
	 function _hasAccessAuthority(&$insertUserID)
	 {
		$userID = $this->_session->getParameter("_user_id");

		if($userID == $insertUserID){
			return true;
		}
		return false;
	 }

	/**
	 * ハンドルより、ユーザID取得
	 *
	 * @param string ハンドル
	 * @return string ユーザID
	 * @access	public
	 */
	function getUserIdByHandle($handle)
	{
		$select_str = "SELECT {users}.user_id";
		list($from_str,$where_str) = $this->getAuthoritySQL();

		$where_str .= " AND {users}.handle = ?";
		$params = array($handle);

		$sql = $select_str.$from_str.$where_str;
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return "";
		}
		if (!isset($result[0]["user_id"])) {
			return "";
		}
		return $result[0]["user_id"];
	 }

	/**
	 * フイルタ情報データ配列を取得する
	 *
     * @return array	フイルタ情報データ配列
	 * @access	public
	 */
	 function &getFilters()
	 {
		$user_id = $this->_session->getParameter("_user_id");
		$params = array(
			$user_id
		);
		$sql = "SELECT filter_id, senders, subject, keyword_list, apply_inbox_flag ".
			   "FROM {pm_filter} WHERE insert_user_id = ? ORDER BY insert_time DESC";

	 	$filters = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeFilterArray"));
		if ($filters === false) {
			$filters = array();
			$this->_db->addError();
		}

		return $filters;
	 }

	/**
	 * フイルタ情報データ配列を生成する
	 *
	 * @param	array	$recordSet	フイルタADORecordSet
	 * @return array	フイルタ情報データ配列
	 * @access	private
	 */
	 function &_makeFilterArray(&$recordSet)
	 {
	 	$filters = array();
		while ($row = $recordSet->fetchRow()) {
			$params = array(
				$row['filter_id']
			);
			$sql = "SELECT action.action_description, link.action_parameters ".
				   "FROM {pm_filter_action_link} link, {pm_filter_action} action ".
				   "WHERE link.action_id = action.action_id ".
				   "AND link.filter_id = ? ";
			// $actions = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeFilterActionsArray"));
			$result = $this->_db->execute($sql, $params);
			$actions = array();

			for($i = 0; $i < count($result); $i++){
				$actions[] = $this->_pmFilterDisplay->getDescription($result[$i]["action_description"],
                                                                     $result[$i]["action_parameters"]);
			}

			if(is_array($actions)){
				$row['body'] = join(PM_FILTER_SEPARATOR, $actions);
			}else{
				$row['body'] = "";
			}

			$row["senders_array"] = $this->_makeFilterSendersArray($row["senders"]);
			$row["senders"] = $this->_makeFilterSenderHandleList($row["senders"]);

			$filters[] = $row;
		}

		return $filters;
	 }

	/**
	 * フイルタ情報データを取得する
	 *
     * @return array	フイルタ情報データ
	 * @access	public
	 */
	 function &getFilter()
	 {
	 	$filter_id = $this->_request->getParameter("filter_id");
		$filter = array();

		if (!empty($filter_id)) {
			$user_id = $this->_session->getParameter("_user_id");
			$params = array(
						$filter_id,
						$user_id
					);
			$sql = "SELECT filter_id, senders, subject, keyword_list, apply_inbox_flag ".
				   "FROM {pm_filter} WHERE filter_id = ? AND insert_user_id = ?";

			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->_db->addError();
			}else{
				if(isset($result[0])){
					$filter = $result[0];
					$filter["senders"] = $this->_makeFilterSenderHandleList($filter["senders"]);
				}else{
					return $filter;
				}
			}
		}

		$filter['actions'] = array();
		$sql = "SELECT action_id, show_action_name FROM {pm_filter_action} ORDER BY action_id";
		$result = $this->_db->execute($sql);
		if ($result === false) {
			$this->_db->addError();
		}else{
			for($i = 0; $i < sizeof($result); $i++){
				$checked = false;
				$default = '';

				if (!empty($filter_id)) {
					$params = array(
							$filter_id,
							$result[$i]['action_id']
						);
					$sql = "SELECT action_parameters ".
						   "FROM {pm_filter_action_link} WHERE filter_id = ? AND action_id = ?";
					$result2 = $this->_db->execute($sql, $params);
					if ($result2 === false) {
						$this->_db->addError();
					}else{
						if(isset($result2[0]['action_parameters'])){
							$checked = true;
							$default = $result2[0]['action_parameters'];
						}
					}
				}

				$show_func = $result[$i]['show_action_name'];
				$filter['actions'][] = $this->_pmFilterDisplay->$show_func($result[$i]['action_id'], $checked, $default);
			}
		}

		return $filter;
	 }

	/**
	 * フイルタ詳細情報データを取得する
	 *
     * @return array	フイルタ詳細情報データ
	 * @access	public
	 */
	 function &getFilterInfo()
	 {
	 	$filter_id = $this->_request->getParameter("filter_id");
		$filter = array();

		if (!empty($filter_id)) {
			$params = array(
				$filter_id
			);
			$sql = "SELECT filter_id, senders, subject, keyword_list, apply_inbox_flag ".
				   "FROM {pm_filter} WHERE filter_id = ?";

			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->_db->addError();
				return $filter;
			}else{
				if(isset($result[0])){
					$filter = $result[0];
					$senders = $filter["senders"];
					$filter["senders"] = $this->_makeFilterSenderHandleList($senders);
					$filter["senders_id"] = $this->_makeFilterSenderIdList($senders);
				}else{
					return $filter;
				}
			}
		}

		$filter['actions'] = array();
		$params = array(
			$filter_id,
		);
		$sql = "SELECT fa.break_afterme_flag, fa.handle_action_name, fa.execute_sequence, f2a.action_parameters ".
			   "FROM {pm_filter_action_link} as f2a, {pm_filter_action} as fa ".
			   "WHERE f2a.action_id = fa.action_id ".
			   "AND f2a.filter_id = ? ".
			   "ORDER BY fa.execute_sequence ASC";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
		}else{
			for($i = 0; $i < count($result); $i++){
				$filter['actions'][] = array('func' => $result[$i]['handle_action_name'],
											 'param' => $result[$i]['action_parameters'],
											 'sequence' => $result[$i]['execute_sequence']);
				if($result[$i]['break_afterme_flag'] == 1){
					break;
				}
			}
		}
		return $filter;
	 }

	/**
	 * ユースIDより、フイルタ詳細情報データを取得する
	 *
     * @return array	フイルタ詳細情報データ
	 * @access	public
	 */
	 function &getFiltersInfoByInsertUserId(){
	 	$insert_user_id = $this->_request->getParameter("insert_user_id");
		$filters = array();

		if (!empty($insert_user_id)) {
			$params = array(
				$insert_user_id
			);
			$sql = "SELECT filter_id, senders, subject, keyword_list, apply_inbox_flag ".
				   "FROM {pm_filter} WHERE insert_user_id = ?";

			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->_db->addError();
			}else{
				for($i = 0; $i < count($result); $i++){
					$senders = $result[$i]["senders"];
					$result[$i]["senders"] = $this->_makeFilterSenderHandleList($senders);
					$result[$i]["senders_id"] = $this->_makeFilterSenderIdList($senders);
					$result[$i]['actions'] = array();

					$params = array(
						$result[$i]["filter_id"]
					);
					$sql = "SELECT fa.break_afterme_flag, fa.handle_action_name, f2a.action_parameters ".
						   "FROM {pm_filter_action_link} as f2a, {pm_filter_action} as fa ".
						   "WHERE f2a.action_id = fa.action_id ".
						   "AND f2a.filter_id = ? ".
						   "ORDER BY fa.execute_sequence ASC";
			   		$result2 = $this->_db->execute($sql, $params);
					if ($result2 === false) {
						$this->_db->addError();
					}else{
						for($j = 0; $j < count($result2); $j++){
							$result[$i]['actions'][] = array('func' => $result2[$j]['handle_action_name'],
											 			     'param' => $result2[$j]['action_parameters']);
							if($result2[$j]['break_afterme_flag'] == 1){
								break;
							}
						}
					}
					$filters[] = $result[$i];
				}
			}
		}

		return $filters;
	 }

	/**
	 * フイルタのハンドル情報データを生成する
	 *
     * @return string	 フイルタのハンドル情報データ
	 * @access	public
	 */
	 function _makeFilterSenderHandleList($sender_list = ''){
	 	$handle_array = array();
	 	$senders = explode(",", $sender_list);
		if(is_array($senders)){
			foreach($senders as $sender){
				$sender_pair = explode(":", $sender);
				if(is_array($sender_pair)){
					$handle_array[] = $sender_pair[1];
				}
			}
		}
		$handle_list = join(",", $handle_array);
		return $handle_list;
	 }

	/**
	 * フイルタのハンドルID情報データを生成する
	 *
     * @return string	 フイルタのハンドル情報IDデータ
	 * @access	public
	 */
	 function _makeFilterSenderIdList($sender_list = ''){
	 	$id_array = array();
	 	$senders = explode(",", $sender_list);

		if(is_array($senders)){
			foreach($senders as $sender){
				$sender_pair = explode(":", $sender);
				if(is_array($sender_pair)){
					$id_array[] = $sender_pair[0];
				}
			}
		}
		$id_list = join(",", $id_array);
		return $id_list;
	 }

	/**
	 * フイルタの送信人情報データを生成する
	 *
     * @return array	 フイルタの送信人情報データ
	 * @access	public
	 */
	 function &_makeFilterSendersArray($sender_list = ''){
	 	$senders_array = array();
	 	$senders = explode(",", $sender_list);
		if(is_array($senders)){
			foreach($senders as $sender){
				$sender_pair = explode(":", $sender);
				if(is_array($sender_pair)){
					$senders_array[] = array('id' => $sender_pair[0], 'handle' => $sender_pair[1]);
				}
			}
		}
		return $senders_array;
	 }

	 function getUploadId($module_id, $unique_id){
	 	$upload_id = false;

		$params = array(
			$module_id,
			$unique_id
		);

		$sql = "SELECT upload_id ".
			   "FROM {uploads} ".
			   "WHERE module_id = ? AND unique_id = ?";
		$rows = $this->_db->execute($sql, $params, null, null, true, null);
		if ($rows === false) {
			$this->_db->addError();
		}else{
			if(sizeof($rows) && isset($rows[0]["upload_id"])){
				$upload_id = $rows[0]["upload_id"];
			}
		}

		return $upload_id;
	 }

	/**
	 * アップロード人データを取得する
	 *
     * @return string	 アップロード人データ
	 * @access	public
	 */
	 function getUploadOwner($upload_id = 0){
	 	$owner = false;

		$params = array(
			$upload_id
		);
		$sql = "SELECT insert_user_id ".
			   "FROM {uploads} ".
			   "WHERE upload_id = ?";
		$records = $this->_db->execute($sql, $params, null, null, true, null);
		if ($records === false) {
			$this->_db->addError();
		}else{
			if(sizeof($records) && isset($records[0]["insert_user_id"])){
				$owner = $records[0]["insert_user_id"];
			}
		}

		return $owner;
	 }

	/**
	 * アップロード読取人データを取得する
	 *
     * @return string	 アップロード読取人データ
	 * @access	public
	 */
	 function &getUploadReaders($upload_id = 0){
		$readers = array();

		$params = array(
			$upload_id
		);

		$sql = "SELECT r.receiver_user_id ".
			   "FROM {pm_message_receiver} as r, {uploads} as u ".
			   "WHERE u.unique_id = r.message_id ".
			   "AND u.upload_id = ?";

		$records = $this->_db->execute($sql, $params, null, null, true, null);
		if ($records === false) {
			$this->_db->addError();
		}else{
			if(is_array($records)){
				for($i = 0; $i < sizeof($records); $i++){
					if(isset($records[$i]['receiver_user_id'])){
						$readers[] = $records[$i]['receiver_user_id'];
					}
				}
			}
		}

		return $readers;
	 }

	 function &getUploadMessage($upload_id = 0){
		$message = false;

		$params = array(
			$upload_id
		);

		$sql = "SELECT m.send_all_flag ".
			   "FROM {pm_message} as m, {uploads} as u ".
			   "WHERE u.unique_id = m.message_id ".
			   "AND u.upload_id = ?";

		$records = $this->_db->execute($sql, $params, null, null, true, null);
		if ($records === false) {
			$this->_db->addError();
		}else{
			if(sizeof($records) && isset($records[0])){
				$message = $records[0];
			}
		}

		return $message;
	 }

	/**
	 * タグ名より、タグIDデータを取得する
	 *
     * @return string  、タグIDデータ
	 * @access	public
	 */
	 function getTagByName($tag_name)
	 {
	 	$params = array(
			$tag_name,
			$this->_session->getParameter("_user_id")
		);

		$sql = "SELECT tag_id FROM {pm_tag} WHERE tag_name = ? AND insert_user_id = ?";
		$result = $this->_db->execute($sql, $params, null, null, false);

		if ($result === false) {
			$this->_db->addError();
		}

		if (isset($result[0][0])) {
			return $result[0][0];
		} else {
			return "";
		}
	 }

	/**
	 * メール送信ユーザデータ配列を取得する
	 *
     * @return array	メール送信ユーザデータ配列
	 * @access	public
	 */
	 function getSendMailUsers($user_id, $email_address = '', $user_name = ''){
	 	if(empty($user_id)){
			return false;
		}

	 	$params = array(
			$user_id
		);

		$sql = "SELECT {users}.handle, {users_items_link}.content AS email ".
			   "FROM {users}, {users_items_link}, {items} ".
			   "WHERE {users}.user_id={users_items_link}.user_id ".
			   "AND {users_items_link}.item_id = {items}.item_id ".
			   // "AND ({items}.type='email' OR {items}.type='mobile_email') ".
			   "AND ({items}.type='email') ".
			   "AND {users_items_link}.content!='' ".
			   "AND {users}.user_id = ? ";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$user = array("handle" => $user_name, "email" => $email_address);
			$this->_db->addError();
		}else{
			// $users[0] = array("from" => $user["email"], "handle" => $user[0]["handle"], "email" => $email_address);
			if(isset($result[0])){
			    $user = array("handle" => $result[0]["handle"], "email" => $result[0]["email"]);
			}else{
				return false;
			}
		}

		return $user;
	 }

	/**
	 * メッセージIDデータを取得する
	 *
     * @return string  メッセージIDデータ
	 * @access	public
	 */
	function getMessageID($receiver_id)
	{
		// receiver_idより、メッセージIDを取得
		$params = array(
			intval($receiver_id)
		);

		$sql = "SELECT message_id FROM {pm_message_receiver} WHERE receiver_id = ?";
		$result = $this->_db->execute($sql, $params, null, null, false);

		if ($result === false) {
			$this->_db->addError();
		}

		if (isset($result[0][0])) {
			return $result[0][0];
		} else {
			return "";
		}
	}

	/**
	 * 全部選択の受信メッセージIDデータを取得
	 *
     * @return array  全部選択の受信メッセージIDデータ
	 * @access	public
	 */
	function getAllReceivers($action = "common")
	{
		$query = &$this->generateMessagesQuery($action,false);

		if(!$query){
			$query = &$this->generateMessagesQuery();
		}

		$messages = $this->_db->execute($query['sql'], $query['params'], null, null, true, null);
		if (!$messages) {
			$this->_db->addError();
		}

		$receivers = array();
		foreach($messages as $message) {
			$receivers[] = $message["receiver_id"];
		}

		return $receivers;
	}

	/**
	 * メッセージ削除できる判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function checkDeleteMessage($receiver_id)
	{
		$params = array(
						intval($receiver_id)
					);

		$sql = "SELECT COUNT(receiver_id) FROM {pm_message_receiver} ".
	          "WHERE message_id = (SELECT message_id FROM {pm_message_receiver} WHERE receiver_id = ?) AND ".
			  "delete_state IN (".PM_MESSAGE_STATE_NORMAL.",".PM_MESSAGE_STATE_TRASH.")";

		$counts = $this->_db->execute($sql, $params, null, null, false);

		if ($counts === false) {
			$this->_db->addError();
		}

		if ($counts[0][0] > 0) {
		    // 削除できません
			return false;
		}

		// // 削除できる
        return true;
	}

	/**
	 * タグより、フィルタ設定情報を取得
	 *
     * @return array	フィルタ設定情報
	 * @access	public
	 */
	function getFilterLinkByTag($tag_id)
	{
		$params = array($tag_id);

		$sql = "SELECT l.filter_id, l.action_id ".
		       "FROM {pm_filter_action_link} l, {pm_filter_action} a ".
			   "where l.action_id = a.action_id AND ".
			   "a.handle_action_name = 'addTag' AND ".
			   "l.action_parameters = ?";

		$filters = $this->_db->execute($sql, $params, null, null, true, null);
		return $filters;
	}

	/**
	 * フィルタ設定情報存在判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function checkFilterLinkExist($filter_id)
	{
		$params["filter_id"] = $filter_id;
    	$count = $this->_db->countExecute("pm_filter_action_link", $params);
		return $count;
	}

	/**
	 * タグ編集権限判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	 function checkTagAuth()
	 {
    	$params = array(
			"tag_id" => $this->_request->getParameter("tag_id"),
			"insert_user_id" => $this->_session->getParameter("_user_id")
		);

        $count = $this->_db->countExecute("pm_tag", $params);
        return $count;
	 }

	 /**
	 * フィルタ存在判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	 function checkFilterExist()
	 {
    	$params = array(
			"filter_id" => $this->_request->getParameter("filter_id")
		);

        $count = $this->_db->countExecute("pm_filter", $params);
        return $count;
	 }

	/**
	 * フィルタ編集権限判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	 function checkFilterAuth()
	 {
    	$params = array(
			"filter_id" => $this->_request->getParameter("filter_id"),
			"insert_user_id" => $this->_session->getParameter("_user_id")
		);

        $count = $this->_db->countExecute("pm_filter", $params);
        return $count;
	 }

	/**
	 * メッセージ存在判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	 function checkMessageExist($receiver_id)
	 {
		$params = array(
			intval($receiver_id)
		);

		$sql = "SELECT COUNT(receiver_id) FROM {pm_message_receiver} ".
	          "WHERE receiver_id = ? AND ".
			  "delete_state IN (".PM_MESSAGE_STATE_NORMAL.",".PM_MESSAGE_STATE_TRASH.")";

		$counts = $this->_db->execute($sql, $params, null, null, false);

		if ($counts === false) {
			$this->_db->addError();
		}

		if ($counts[0][0] > 0) {
		    // 存在します
			return true;
		}

		// 存在しません
        return false;
	 }

	/**
	 * メッセージ編集権限判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	 function checkMessageAuth($receiver_id)
	 {
		$params = array(
						intval($receiver_id),
						$this->_session->getParameter("_user_id")
		);

		$sql = "SELECT COUNT(receiver_id) FROM {pm_message_receiver} ".
	           "WHERE receiver_id = ? AND ".
			   "receiver_user_id = ? AND ".
			   "delete_state IN (".PM_MESSAGE_STATE_NORMAL.",".PM_MESSAGE_STATE_TRASH.")";

		$counts = $this->_db->execute($sql, $params, null, null, false);

		if ($counts === false) {
			$this->_db->addError();
		}

		if ($counts[0][0] > 0) {
		    // 存在します
			return true;
		}

		// 存在しません
        return false;
	 }

	/**
	 * フィルタ重複判定
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	 function checkFilterDuplicated($filter)
	 {
	 	$filter_id = $filter["filter_id"];
	 	$senders = $filter["senders"];
		$subject = $filter["subject"];
		$keyword_list = $filter["keyword_list"];
		$filter_actions = $filter["filter_actions"];
		$filter_actions_params = $filter["filter_actions_params"];

		if(!is_array($filter_actions)) {
			$filter_actions = array();
		}
		if(!is_array($filter_actions_params)) {
			$filter_actions_params = array();
		}

		$actions_params = array();
		foreach($filter_actions_params as $k => $v){
			$actions_params[(int)$k] = $v;
		}

		// array(action_id => action_parameters)配列を生成
		$inputFilter = array();
		foreach($filter_actions as $filter_action) {
			if(isset($actions_params[$filter_action])){
				$inputFilter[$filter_action] = $actions_params[$filter_action];
			}
		}

		$sender_array = array();
		$sender_handles = explode(",", $senders);
		if(!is_array($sender_handles)) {
			$sender_handles = array();
		}

		foreach($sender_handles as $sender_handle){
			$sender_id = $this->getUserIdByHandle(trim($sender_handle));
			$sender_array[] = $sender_id . ":" . $sender_handle;
		}

		$sql = "SELECT filter_id FROM {pm_filter} ".
	          "WHERE insert_user_id = ? AND ".
			  "senders = ? AND ".
			  "subject = ? AND ".
			  "keyword_list = ? ";

		$params = array(
						$this->_session->getParameter("_user_id"),
						join(",", $sender_array),
						$subject,
						$keyword_list
		);

		if (!empty($filter_id)) {
			$params[] =  $filter_id;
			$sql = $sql . " AND filter_id <> ?";
		}

		$filterResult = $this->_db->execute($sql, $params, null, null, false);

		//　同じなフィルタがありません場合、Trueを戻り。
		if(empty($filterResult)) {
			return true;
		} else {
			// フィルタ処理内容判定
			foreach($filterResult as $filterInfo) {
				$filterLink = $this->getFilterLink($filterInfo[0]);
				$filter_diff_one = array_diff_assoc($inputFilter, $filterLink);
				$filter_diff_two = array_diff_assoc($filterLink, $inputFilter);

				if (empty($filter_diff_one) && empty($filter_diff_two)) {
					return false;
				}

			}
		}

        return true;
	 }

	/**
	 * フィルタ設定情報を取得
	 *
     * @return array	フィルタ設定情報
	 * @access	public
	 */
	function getFilterLink($filter_id)
	{
		$params = array($filter_id);

		$sql = "SELECT action_id, action_parameters ".
		       "FROM {pm_filter_action_link} ".
			   "WHERE filter_id = ? ";

		$filters = $this->_db->execute($sql, $params, null, null, true, null);

		if ($filters === false) {
			$this->_db->addError();
		}

		$filterLink = array();
		if(isset($filters) && is_array($filters)){
			foreach($filters as $filter) {
				$filterLink[$filter["action_id"]] = $filter["action_parameters"];
			}
		}

		return $filterLink;
	}

	/**
	 * ユーザIDからアバターのパスを取得
	 *
	 * @param string ユーザID
	 * @return string アバターのパス
	 * @access	public
	 */
	function getUserAvatar($user_id)
	{
		$select_str = "SELECT {users_items_link}.content AS file";
		list($from_str,$where_str) = $this->getAuthoritySQL();

		$from_str .= " INNER JOIN {items} ON {items}.type='file'".
					" LEFT JOIN {users_items_link}".
						" ON {users}.user_id = {users_items_link}.user_id".
						" AND {items}.item_id = {users_items_link}.item_id";

		$where_str .= " AND {users_items_link}.content!=''".
					" AND {users_items_link}.user_id = ?";
		$params = array($user_id);

		$sql = $select_str.$from_str.$where_str;
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if (!isset($result[0]["file"])) {
			return false;
		}
		return $result[0]["file"];
	}

	function getMessageIdsByUserId($user_id = 0){
		$messageIds = array();
		$params = array(
			$user_id
		);

		$sql = "SELECT message_id FROM {pm_message_receiver} WHERE receiver_user_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
		}else{
			if(is_array($result)){
				for($i = 0; $i < sizeof($result); $i++){
					$messageIds[] = $result[$i]["message_id"];
				}
			}
		}
		return $messageIds;
	}

	function isDropedMessage($message_id = 0, $exclude_user_id = false) {
		if($exclude_user_id != false){
			$params = array(
				$message_id,
				$exclude_user_id
			);
			$sql = "SELECT COUNT(receiver_id) FROM {pm_message_receiver} ".
				   "WHERE message_id = ? AND receiver_user_id <> ?";
		}else{
			$params = array(
				$message_id
			);
			$sql = "SELECT COUNT(receiver_id) FROM {pm_message_receiver} ".
				   "WHERE message_id = ?";
		}

		$counts = $this->_db->execute($sql, $params, null, null, false);
		if ($counts === false) {
			$this->_db->addError();
		}

		if ($counts[0][0] > 0) {
		    // 存在します
			return false;
		}

		// 存在しません
        return true;
	}

	function getUserIdByPageId($entry_id = 0, $page_flag = true){
		$params = array(
			_ON,
			$entry_id
		);

		if($page_flag){
			$sql = "SELECT insert_user_id FROM {pages} WHERE private_flag = ? AND page_id = ?";
		}else{
			$sql = "SELECT insert_user_id FROM {pages} WHERE private_flag = ? AND room_id = ?";
		}

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
		}else{
			if(isset($result[0]["insert_user_id"])){
				return $result[0]["insert_user_id"];
			}
		}
		return false;
	}

	function getRoomIdByPageId($page_id = 0){
		$params = array(
			$page_id
		);
		$sql = "SELECT room_id FROM {pages} WHERE page_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
		}else{
			if(isset($result[0]["room_id"])){
				return $result[0]["room_id"];
			}
		}
		return false;
	}

	/**
     * ページに関する設定を行います
     *
     * @param int disp_cnt 1ページ当り表示件数
     * @param int now_page 現ページ
     */
    function setPageInfo(&$pager, $data_cnt, $disp_cnt, $now_page = NULL){
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
        if(($pager['now_page'] - PM_FRONT_AND_BEHIND_LINK_CNT) > 0){
            $start = $pager['now_page'] - PM_FRONT_AND_BEHIND_LINK_CNT;
        }else{
            $start = 1;
        }
        if(($pager['now_page'] + PM_FRONT_AND_BEHIND_LINK_CNT) >= $pager['total_page']){
            $end = $pager['total_page'];
        }else{
            $end = $pager['now_page'] + PM_FRONT_AND_BEHIND_LINK_CNT;
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

	/**
	 * 宛先情報を取得する
	 *
	 * @param array $receivers 宛先ハンドル名配列
	 * @return array 宛先情報データ配列
	 * @access	public
	 */
	function getForwardReceivers($receivers=array())
	{
		$select_str = "SELECT {users}.user_id AS receiver_user_id, {users}.handle AS receiver_handle,".
						" {authorities}.user_authority_id AS receiver_auth_id";
		list($from_str,$where_str) = $this->getAuthoritySQL();

		if (!empty($receivers)) {
			$sqlInClauseValue = "''";
			foreach ($receivers as $receiver) {
				$sqlInClauseValue .= ',' . $this->_db->_conn->qstr(trim($receiver));
			}
			$where_str .= " AND {users}.handle IN (" . $sqlInClauseValue . ")";
		}

		$sql = $select_str.$from_str.$where_str;
		$result = $this->_db->execute($sql);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if (!isset($result[0])) {
			return false;
		}
		return $result;
	}

	/**
	 * ログイン会員のメール項目IDを取得する
	 *
	 * @return string ログイン会員のメール項目ID
	 * @access public
	 */
	function getEmailItemId()
	{
		$emailItemId = '';
		$where_params = array(
			$this->_session->getParameter("_user_id")
		);
		$sql = "SELECT I.item_id ".
				"FROM {items} I, {users_items_link} U ".
				"WHERE I.type='email' AND I.item_id=U.item_id " .
				"AND U.content!='' AND U.user_id=? limit 1";
		$email = $this->_db->execute($sql, $where_params);
		if (!empty($email)) {
			$emailItemId = $email[0]['item_id'];
		}
		return $emailItemId;
	}

	/**
	 * 送信アドレスの公開区分毎に送信先配列を振分ける
	 *
	 * @param array $mail_users 送信先配列 
	 * @param string $email_item_id 送信先アドレス項目ID
	 * @return array 送信先振分けデータ配列
	 * @access	public
	 */
	function divideMailFrom($mail_users, $email_item_id)
	{
		$openUsers = array();
		$closeUsers = array();

		$usersView =& $this->_container->getComponent('usersView');
		$senderAuthorityId = $this->_session->getParameter('_user_auth_id');
		$itemAuthorities = array();
		foreach ($mail_users as $mail_user) {
			$receiverAuthorityId = $mail_user['receiver_auth_id'];
			if (!isset($itemAuthorities[$receiverAuthorityId])) {
				$params = array(
					'{items}.item_id' => $email_item_id,
					'user_authority_id' => $receiverAuthorityId
				);
				$temporaryItemAuthorities = $usersView->getItems($params);
				$itemAuthorities[$receiverAuthorityId] = $temporaryItemAuthorities[0];
			}

			if ($senderAuthorityId >= $receiverAuthorityId) {
				$flag = $itemAuthorities[$receiverAuthorityId]['over_public_flag'];
			} else {
				$flag = $itemAuthorities[$receiverAuthorityId]['under_public_flag'];
			}

			if ($flag != USER_NO_PUBLIC) {
				$openUsers[] = $mail_user;
			} else {
				$closeUsers[] = $mail_user;
			}
		}

		return array($openUsers, $closeUsers);
	}

	function getFromInfo($public_flag) {
		if ($public_flag) {
			$usersView =& $this->_container->getComponent('usersView');
			$user =& $usersView->getUserById($this->_session->getParameter('_user_id'));
			$ret = array(
				'from_name' => $user['handle']
			);
		} else {
			$configView =& $this->_container->getComponent('configView');
			$mailConfigs = $configView->getConfigByCatid(_SYS_CONF_MODID, _MAIL_CONF_CATID);
			$ret = array(
				'from_name' => $mailConfigs['fromname']['conf_value'],
				'from_email' => $mailConfigs['from']['conf_value']
			);
		}
		return $ret;
	}

	/**
	 * 権限判断用のSQLを取得する
	 *
	 * @return array array($from_sql, $where_sql), 権限判断用のSQL文
	 * @access	private
	 */
	function getAuthoritySQL()
	{
		$usersView =& $this->_container->getComponent('usersView');
		$from_sql = " FROM ({authorities},{users})".
					" INNER JOIN {authorities_modules_link}".
						" ON {authorities_modules_link}.module_id = ".$this->_request->getParameter("module_id").
						" AND {authorities}.role_authority_id = {authorities_modules_link}.role_authority_id";
		$where_sql = " WHERE {users}.role_authority_id = {authorities}.role_authority_id";
		$where_sql .= " AND {authorities}.user_authority_id > "._AUTH_GUEST;
		$where_sql .= $usersView->createSearchWhereString();
		$where_sql .= " AND {users}.active_flag = ". _USER_ACTIVE_FLAG_ON;

		return array($from_sql, $where_sql);
	}
}
?>
