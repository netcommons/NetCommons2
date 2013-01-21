<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 掲示板データ登録コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Components_Action
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
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Bbs_Components_Action()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * 掲示板データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBbs()
	{
		$params = array(
			"bbs_name" => $this->_request->getParameter("bbs_name"),
			"topic_authority" => intval($this->_request->getParameter("topic_authority")),
			"child_flag" => intval($this->_request->getParameter("child_flag")),
			"vote_flag" => intval($this->_request->getParameter("vote_flag")),
			"new_period" => intval($this->_request->getParameter("new_period")),
			"mail_send" => intval($this->_request->getParameter("mail_send")),
			"mail_authority" => intval($this->_request->getParameter("mail_authority")),
			"mail_subject" => $this->_request->getParameter("mail_subject"),
			"mail_body" => $this->_request->getParameter("mail_body")
		);

		$bbs = $this->_request->getParameter("bbs");
		$bbsID = $this->_request->getParameter("bbs_id");
		if (empty($bbsID)) {
			$params["activity"] = $bbs["activity"];
			$result = $this->_db->insertExecute("bbs", $params, true, "bbs_id");
		} else {
			$params["bbs_id"] = $bbsID;
			$result = $this->_db->updateExecute("bbs", $params, "bbs_id", true);
		}
		if (!$result) {
			return false;
		}

        if (!empty($bbsID)) {
        	return true;
        }

		$bbsID = $result;
		$this->_request->setParameter("bbs_id", $bbsID);
        if (!$this->setBlock()) {
			return false;
		}

		return true;
	}

	/**
	 * 掲示板動作/停止を変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setActivity()
	{
		$params = array(
			"bbs_id" => $this->_request->getParameter("bbs_id"),
			"activity" => $this->_request->getParameter("activity")
		);
        if (!$this->_db->updateExecute("bbs", $params, "bbs_id", true)) {
			return false;
		}

		return true;
	}

	/**
	 * 掲示板データを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteBbs()
	{
		$params = array("bbs_id" => $this->_request->getParameter("bbs_id"));

    	if (!$this->_db->deleteExecute("bbs_block", $params)) {
    		return false;
    	}

		$sql = "SELECT T.topic_id ".
				"FROM {bbs_topic} T ".
				"INNER JOIN {bbs_post} P ".
				"ON T.topic_id = P.post_id ".
				"WHERE P.bbs_id = ? ";
		$topicIDs = $this->_db->execute($sql, $params, null, null, false);
		if ($topicIDs === false) {
        	$this->_db->addError();
        	return $topicIDs;
		}

		foreach ($topicIDs as $topicID) {
			if (!$this->deletePost($topicID[0])) {
		        return false;
	        }

			if (!$this->deleteTopic($topicID[0])) {
			    return false;
			}
		}

    	if (!$this->_db->deleteExecute("bbs", $params)) {
    		return false;
    	}

		//--URL短縮形関連 Start--
		$container =& DIContainerFactory::getContainer();
		$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
		$result = $abbreviateurlAction->deleteUrlByContents($this->_request->getParameter("bbs_id"));
		if ($result === false) {
			//return false;
		}
		//--URL短縮形関連 End--

		return true;
	}

	/**
	 * 掲示板用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$blockID = $this->_request->getParameter("block_id");

		$params = array($blockID);
		$sql = "SELECT block_id ".
				"FROM {bbs_block} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $blockID,
			"bbs_id" => $this->_request->getParameter("bbs_id")
		);

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($blockIDs) &&
				$actionName == "bbs_action_edit_current") {
			if (!$this->_db->updateExecute("bbs_block", $params, "block_id", true)) {
				return false;
			}

			return true;
		}

		if ($actionName == "bbs_action_edit_current") {
			$bbsView =& $container->getComponent("bbsView");
			$bbs = $bbsView->getDefaultBbs();
		}
		if ($actionName == "bbs_action_edit_entry") {
			$this->_request->setParameter("bbs_id", null);
			$bbs = $this->_request->getParameter("bbs");
		}
		if (!empty($bbs)) {
			$this->_request->setParameter("display", $bbs["display"]);
			$this->_request->setParameter("expand", $bbs["expand"]);
			$this->_request->setParameter("visible_row", $bbs["visible_row"]);
		}

		$params["display"] = intval($this->_request->getParameter("display"));
		$params["expand"] =  intval($this->_request->getParameter("expand"));
		$params["visible_row"] = intval($this->_request->getParameter("visible_row"));

		if (!empty($blockIDs)) {
			$result = $this->_db->updateExecute("bbs_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("bbs_block", $params, true);
		}
        if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * 根記事データを削除する
	 *
	 * @param	stirng	$topic_id	根記事ID
 	 * @return boolean	true or false
	 * @access	public
	 */
	function deleteTopic($topic_id)
	{
		$params = array("topic_id" => $topic_id);
    	if (!$this->_db->deleteExecute("bbs_topic", $params)) {
    		return false;
    	}

		return true;
	}

	/**
	 * 記事データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setPost()
	{
		// --- 記事テーブルに登録 ---
		$params = array(
			"bbs_id" => $this->_request->getParameter("bbs_id"),
			"subject" => $this->_request->getParameter("subject")
		);
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$mobile_flag = $session->getParameter('_mobile_flag');
		if ($mobile_flag == _OFF) {
			$params['icon_name'] = $this->_request->getParameter('icon_name');
		}

		$container =& DIContainerFactory::getContainer();
		$bbsView =& $container->getComponent("bbsView");
		$postID = $this->_request->getParameter("post_id");
		$bbs = $this->_request->getParameter("bbs");

		$insertFlag = false;
		if (empty($postID)) {
			$postID = $this->_db->nextSeq("bbs_post");

			$parentID = $this->_request->getParameter("parent_id");
			if (empty($parentID)) {
				$params["topic_id"] = $postID;
				$params["parent_id"] = 0;
			} else {
				$params["parent_id"] = $parentID;
				$params["topic_id"] = $bbsView->getTopicID($parentID);
				if ($params["topic_id"] === false) {
					return false;
				}
			}

			$insertFlag = true;
			$this->_request->setParameter("post_id", $postID);
		} else {
			$params["topic_id"] = $bbsView->getTopicID($postID);
		}
		$params["post_id"] = $postID;

		$temporary = intval($this->_request->getParameter("temporary"));

		if (!$insertFlag) {
			$post = $this->_request->getParameter("post");
			if (empty($post)) {
				return false;
			}
			if ($temporary == _ON &&
					$post["status"] != BBS_STATUS_BEFORE_RELEASED_VALUE) {
				$params["status"] = BBS_STATUS_TEMPORARY_VALUE;
			}
		}
		if (!isset($params["status"])) {
			if ($temporary == _ON) {
				$params["status"] = BBS_STATUS_BEFORE_RELEASED_VALUE;
			} else {
				$params["status"] = BBS_STATUS_RELEASED_VALUE;
			}
		}

		$body = $this->_request->getParameter("body");
		if ($mobile_flag == _ON) {
			$mobile_images = $this->_request->getParameter('bbs_mobile_images');
			$current_mobile_image = $session->getParameter('bbs_current_mobile_image');
			$br = '';
			if (substr(rtrim($body), -6) != '<br />') {
				$br = '<br />';
			}
			if (!empty($mobile_images)) {
				foreach($mobile_images as $image) {
					$body .= $br . $image;
				}
			} elseif (!empty($current_mobile_image)) {
				$body .= $br . $current_mobile_image;
			}
		}
		$params["contained_sign"] = $this->getContainedSignString($body);

		if ($insertFlag) {
			$result = $this->_db->insertExecute("bbs_post", $params, true);
		} else {
			if ($params["status"] == BBS_STATUS_RELEASED_VALUE && $post["status"] == BBS_STATUS_BEFORE_RELEASED_VALUE) {
				$params["insert_time"] = timezone_date();
				$post["insert_time"] = $params["insert_time"];
			}
			$result = $this->_db->updateExecute("bbs_post", $params, "post_id", true);
		}
		if ($result === false) {
			return false;
		}

		// --- 本文テーブルに登録 ---
		$status = $params["status"];
		$topicID = $params["topic_id"];
		$subject = $params["subject"];
		$room_id = $this->_request->getParameter("room_id");

		$params = array(
			"post_id" => $postID,
			"body" => $body,
			"room_id" => $room_id
		);
		if ($insertFlag) {
			$result = $this->_db->insertExecute("bbs_post_body", $params);
		} else {
			$result = $this->_db->updateExecute("bbs_post_body", $params, "post_id");
		}
		if ($result === false) {
			return false;
		}

		// --- 根記事テーブルに登録 ---
		$child_flag = ($topicID == $postID) ? _OFF : _ON;
		if ($topicID == $postID &&
				$insertFlag) {
			$params = array(
				"topic_id" => $postID,
				"newest_time" => timezone_date(),
				"child_num" => "0",
				"room_id" => $room_id
			);
			$result = $this->_db->insertExecute("bbs_topic", $params);
		} else {
			$countSQL = "";
			if ($topicID != $postID) {
				$count = 0;
				if ($status == BBS_STATUS_TEMPORARY_VALUE
				 		&& $post["status"] == BBS_STATUS_RELEASED_VALUE) {
					$count = -1;
				} elseif ($status == BBS_STATUS_RELEASED_VALUE
								&& (!isset($post) || $post["status"] != BBS_STATUS_RELEASED_VALUE)) {
					 $count = 1;
				}
				$countSQL = ", child_num = child_num + (". $count. ") ";
			}

			$params = array(
				timezone_date(),
				$topicID
			);
			$sql = "UPDATE {bbs_topic} ".
					"SET newest_time = ? ".
					$countSQL.
					"WHERE topic_id = ?";
			$result = $this->_db->execute($sql, $params);
		}
		if ($result === false) {
			return false;
		}

		// --- メール送信データ登録 ---
		if ($bbs["mail_send"] == _ON &&
				$status == BBS_STATUS_RELEASED_VALUE &&
				($insertFlag ||
					$post["status"] == BBS_STATUS_BEFORE_RELEASED_VALUE)) {
			$session->setParameter("bbs_mail_post_id", $postID);
		}

		//--URL短縮形関連 Start--
		if ($insertFlag) {
			$container =& DIContainerFactory::getContainer();
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
			$result = $abbreviateurlAction->setAbbreviateUrl($this->_request->getParameter("bbs_id"), $postID);
			if ($result === false) {
				return false;
			}
		}
		//--URL短縮形関連 End--

		//--新着情報関連 Start--
		$whatsnewAction =& $container->getComponent("whatsnewAction");
		if ($temporary != _ON) {
			$whatsnew = array(
				"unique_id" => $postID,
				"title" => $subject,
				"description" => $body,
				"action_name" => "bbs_view_main_post",
				"parameters" => "post_id=". $postID,
				"child_flag" => $child_flag
			);
			if (!empty($post)) {
				$whatsnew["insert_time"] = $post["insert_time"];
				$whatsnew["insert_user_id"] = $post["insert_user_id"];
				$whatsnew["insert_user_name"] = $post["insert_user_name"];
			}
			$result = $whatsnewAction->auto($whatsnew);
		} else {
			$result = $whatsnewAction->delete($postID);
		}
		if ($result === false) {
			return false;
		}
		//--新着情報関連 End--

		// --- 投稿回数更新 ---
		if ($status == BBS_STATUS_RELEASED_VALUE
				&& ($insertFlag
					|| $post["status"] == BBS_STATUS_BEFORE_RELEASED_VALUE)) {
			$monthlynumberAction =& $container->getComponent("monthlynumberAction");
			
			if ($insertFlag) {
				$params = array(
					"user_id" => $session->getParameter("_user_id")
				);
			} else {
				$params = array(
					"user_id" => $post["insert_user_id"]
				);
			}
			if (!$monthlynumberAction->incrementMonthlynumber($params)) {
				return false;
			}
		}

		$session->removeParameter('bbs_current_mobile_image');

		if (!$insertFlag) {
			return true;
		}

		// --- 既読、投票テーブルに登録 ---
		if (!$this->read($postID)) {
			return false;
		}

		return true;
	}

	/**
	 * 記事データを削除する
	 *
	 * @param	stirng	$postID	記事ID
 	 * @return boolean	true or false
	 * @access	public
	 */
	function deletePost($postID)
	{
		$params = array("post_id" => $postID);

	   	if (!$this->_db->deleteExecute("bbs_post", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("bbs_post_body", $params)) {
			return false;
		}

    	if (!$this->_db->deleteExecute("bbs_user_post", $params)) {
    		return false;
    	}

		//--新着情報関連 Start--
		$container =& DIContainerFactory::getContainer();
		$whatsnewAction =& $container->getComponent("whatsnewAction");
		$result = $whatsnewAction->delete($postID);
    	if ($result === false) {
			return false;
		}
		//--新着情報関連 End--

		//--URL短縮形関連 Start--
		$container =& DIContainerFactory::getContainer();
		$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
		$result = $abbreviateurlAction->deleteUrl($postID);
		if ($result === false) {
			return false;
		}
		//--URL短縮形関連 End--

		// 子記事IDを取得
		$sql = "SELECT post_id ".
				"FROM {bbs_post} ".
				"WHERE parent_id = ?";
		$posts = $this->_db->execute($sql, $params);
		if ($posts === false) {
        	$this->_db->addError();
			return $posts;
		}

		// 子記事を再帰的に削除
		foreach ($posts as $post) {
			if (!$this->deletePost($post["post_id"])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 子記事数を更新する
	 *
	 * @param	stirng	$topicID	根記事ID
     * @return boolean	true or false
	 * @access	public
	 */
	function updateChildNum($topicID)
	{
		$params = array(
			$topicID,
			BBS_STATUS_RELEASED_VALUE
		);
		$sql = "SELECT COUNT(post_id) FROM {bbs_post} " .
				"WHERE topic_id = ? ".
				"AND status = ? ".
				"AND parent_id != 0";
		$counts = $this->_db->execute($sql, $params, null, null, false);
		if ($counts === false) {
        	$this->_db->addError();
			return false;
		}

		$params = array(
			"topic_id" => $topicID,
			"child_num" => $counts[0][0]
		);
		if (!$this->_db->updateExecute("bbs_topic", $params, "topic_id")) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 既読データを登録する
	 *
	 * @param	stirng	$postID	記事ID
     * @return boolean	true or false
	 * @access	public
	 */
	function read($postID)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$userID = $session->getParameter("_user_id");
		$room_id = $this->_request->getParameter("room_id");
		if (empty($userID)) {
        	return true;
		}

		$params = array(
			"user_id" => $userID,
			"post_id" => $postID
		);
		$sql = "SELECT post_id ".
				"FROM {bbs_user_post} ".
				"WHERE user_id = ? ".
				"AND post_id = ?";
		$postIDs = $this->_db->execute($sql, $params);
		if ($postIDs === false) {
			$this->_db->addError();
			return false;
		}
		if (!empty($postIDs)) {
			return true;
		}

		$params["read_flag"] = _ON;
		$params["room_id"] = $room_id;
        if (!$this->_db->insertExecute("bbs_user_post", $params)) {
			return false;
		}

		return true;
	}

	/**
	 * 投票データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function vote()
	{
        $postID = $this->_request->getParameter("post_id");

        $container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$userID = $session->getParameter("_user_id");
		if (empty($userID)) {
			$votes = $session->getParameter("bbs_votes");
			$votes[] = $postID;
			$session->setParameter("bbs_votes", $votes);
 		} else {
			$where = array(
				"user_id" => $userID,
				"post_id" => $postID
			);
			$params = array("vote_flag" => _ON);

			$result = $this->_db->updateExecute("bbs_user_post", $params, $where);
	        if ($result === false) {
				return false;
			}
		}

		$params = array($postID);
		$sql = "UPDATE {bbs_post} ".
				"SET vote_num = vote_num + 1 ".
				"WHERE post_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 包含表示データの取得
	 *
	 * @param	string	$body		対象文字列
	 * @return	string	ON、OFFを示す|区切り文字列（メール、URL、画像、添付）
	 **/
	function &getContainedSignString($body)
	{
		$containedSign = array();

		$pattern = "/<a [^<>]*href=([\"'])mailto:[^\"'<>]*\\1[^<>]*>.*<\/a>/sU";
		$mailCont = preg_match_all($pattern, $body, $matches);

		$pattern = "/<a [^<>]*href=([\"'])[^\"'<>]*\?action=common_download_main(&|&amp;)upload_id=[\d]+[^\"'<>]*\\1[^<>]*>.*<\/a>/sU";
		$uploadCont = preg_match_all($pattern, $body, $matches);

		$pattern = "/<a [^<>]*href=([\"'])[^\"'<>]*\\1[^<>]*>.*<\/a>/sU";
		$urlCont = preg_match_all($pattern, $body, $matches);

		$pattern = "/<img [^<>]*src=([\"'])[^\"'<>]*\\1[^<>]*>/sU";
		$imageCont = preg_match_all($pattern, $body, $matches);

		$pattern = "/<img [^<>]*src=([\"'])\.\/images\/comp\/textarea\/[^\"'<>]*\\1[^<>]*>/sU";
		$iconCont = preg_match_all($pattern, $body, $matches);


		$containedSign[] = intval($mailCont > 0);
		$containedSign[] = intval(($mailCont + $uploadCont) < $urlCont);
		$containedSign[] = intval($imageCont > $iconCont);
		$containedSign[] =  intval($uploadCont > 0);

		$containedSignString = implode("|", $containedSign);

		return $containedSignString;
	}
}
?>