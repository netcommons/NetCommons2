<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * pmテーブル登録用クラス
 */
class Pm_Components_Action
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
	 * @var pmViewを保持
	 *
	 * @access	private
	 */
	var $_pmView = null;

	/**
	 * @var pmFilterOperationを保持
	 *
	 * @access	private
	 */
	var $_pmFilterOperation = null;


	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pm_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_pmView =& $this->_container->getComponent("pmView");
		$this->_pmFilterOperation =& $this->_container->getComponent("pmFilterOperation");
	}

	/**
	 * メッセージデータを送信、下書き保存する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setMessage()
	{
		$email_forwards = array();
		$email_filters = array();

		$block_id = $this->_request->getParameter("block_id");

		// 登録ユーザID
		$send_user_id = $this->_session->getParameter("_user_id");

		// 登録ハンドル
		$send_handle = $this->_session->getParameter("_handle");

		// 新規、編集、返事フラグ
		$flag = $this->_request->getParameter("flag");

		// 全会員に送るFLAG
		$send_all_flag = $this->_request->getParameter("send_all_flag");
        if($send_all_flag == null 
        	|| $this->_session->getParameter("_user_auth_id") < _AUTH_ADMIN){
            $send_all_flag = '0';
        }

		// 送信、下書き保存フラグ
		$sendFlag = $this->_request->getParameter("sendFlag");

		// 宛先
		$receivers = $this->_request->getParameter("receivers");

		// 件名
        $subject = $this->_request->getParameter("subject");
		$subject = trim($subject);

		// 内容
		$body = $this->_request->getParameter("body");
		$body = str_replace("?action=common_download_main&upload_id", "?action=pm_download_main&upload_id", $body);

		// メッセージID
		$message_id = $this->_request->getParameter("message_id");

		// 返事メッセージID
		$reply_top_message_id = $this->_request->getParameter("reply_top_message_id");

		// 直接回复的ID
		$reply_last_message_id = $this->_request->getParameter("reply_last_message_id");

		$containedSign = &$this->getContainedSign($body);

		if(is_array($containedSign)){
			$image_count = intval($containedSign['imageCount']);
			$file_count = intval($containedSign['uploadCount']);
			$upload_ids = $containedSign['uploadIds'];
		}else{
			$image_count = 0;
			$file_count = 0;
			$upload_ids = array();
		}

		$receivers_list = "";
		$tempReceivers = array();

		$usersView = &$this->_container->getComponent("usersView");
		if($send_all_flag == _ON){
			$tempReceivers = $this->_pmView->getForwardReceivers();
		}else{
			$tempReceivers = $this->_pmView->getForwardReceivers($receivers);
			if (!empty($tempReceivers)) {
				foreach($tempReceivers as $receiver) {
					$receivers_list .= $receiver["receiver_handle"]."|".$receiver["receiver_user_id"].',';
				}
				$receivers_list = substr($receivers_list, 0, -1);
			} else {
				return false;
			}
		}
		$receivers = $tempReceivers;

		/*
		if (empty($receivers_list) || empty($subject) || trim($body) == "<br />") {
			$sendFlag = PM_STORE_MESSAGE;
		}
		*/

		// メッセージ情報追加
		$params = array(
			"subject" => $subject,
			"body" => $body,
			"sent_time" => timezone_date(),
			"image_count" => $image_count,
			"file_count" => $file_count,
			"receivers_list" => $receivers_list,
			"send_all_flag" => $send_all_flag
		);

		// 編集メッセージ場合
		if ($flag == PM_EDIT_MESSAGE) {
			$params["message_id"] = $message_id;
			if(!$this->_db->updateExecute("pm_message", $params, "message_id", true)) {
				return false;
			}
		} else {
			// 返事メッセージ場合
			if ($flag == PM_REPLY_MESSAGE) {
				if ($reply_top_message_id > 0) {
					$params["reply_top_message_id"] = $reply_top_message_id;
				} else {
					$params["reply_top_message_id"] = $message_id;
				}
				$params["reply_last_message_id"] = $message_id;
			}

			$result = $this->_db->insertExecute("pm_message", $params, true, "message_id");

			if (!$result) {
				return false;
			}
			$message_id = $result;
		}

		$mailbox = PM_LEFTMENU_OUTBOX;
		// 送信処理
		if ($sendFlag == PM_SEND_MESSAGE) {
			// 受信人メッセージ情報追加
			foreach($receivers as $receiver) {
				$receiverParams = array(
					"message_id" => $message_id,
					"receiver_user_id" => $receiver["receiver_user_id"],
					"receiver_user_name" => $receiver["receiver_handle"],
					"mailbox" => PM_LEFTMENU_INBOX,
					"read_state" => PM_UNREAD_STATE,
					"delete_state" => PM_MESSAGE_STATE_NORMAL,
					"importance_flag" => PM_NO_FLAG
				);

				$receiver_id = $this->_db->insertExecute("pm_message_receiver", $receiverParams, true, "receiver_id");

				if(!$receiver_id) {
					return false;
				}

				$forwardState = $this->_pmView->getForwardState($receiver['receiver_user_id']);
				if ($forwardState == _ON) {
					$params = array(
						'{users}.user_id' => $receiver['receiver_user_id']
					);
					$forwardUsers = $usersView->getSendMailUsers(null, null, null, $params);
					if ($forwardUsers === false) {
						return false;
					}

					foreach ($forwardUsers as $forwardUser) {
						$email_forwards[] = array(
							'receiver_id' => $receiver_id,
							'receiver_user_id' => $receiver['receiver_user_id'],
							'receiver_user_name' => $receiver['receiver_handle'],
							'receiver_auth_id' => $receiver['receiver_auth_id'],
							'email' => $forwardUser['email']
						);
					}
				}

				if(!$send_all_flag){
					$applyReceiverFiltering = $this->applyReceiverFiltering($receiver["receiver_user_id"], $receiver["receiver_handle"], $receiver["receiver_auth_id"], $receiver_id, $send_user_id, $subject, $body);
					if(is_array($applyReceiverFiltering)){
						$email_filters = $applyReceiverFiltering;
					}
				}
			}
		} else if ($sendFlag == PM_STORE_MESSAGE) {
			// 下書き保存処理
			$mailbox = PM_LEFTMENU_STOREBOX;
		}

		// 送信人メッセージ情報追加
		$sendParams = array(
			"message_id" => $message_id,
			"receiver_user_id" => $send_user_id,
			"receiver_user_name" => $send_handle,
			"mailbox" => $mailbox,
			"read_state" => PM_READ_STATE,
			"delete_state" => PM_MESSAGE_STATE_NORMAL,
			"importance_flag" => PM_NO_FLAG
		);

		if ($flag == PM_EDIT_MESSAGE) {
			// 編集メッセージ
			$sendParams["receiver_id"] = $this->_request->getParameter("receiver_id");
			if(!$this->_db->updateExecute("pm_message_receiver", $sendParams, "receiver_id", true)) {
				return false;
			}
		} else {
			if(!$this->_db->insertExecute("pm_message_receiver", $sendParams, true, "receiver_id")) {
				return false;
			}
		}

		if($message_id){
			foreach($upload_ids as $upload_id){
				$params = array(
					"upload_id" => $upload_id,
					"unique_id" => $message_id
				);

				if(!$this->_db->updateExecute("uploads", $params, "upload_id", true)) {
					return false;
				}
			}
		}

		$this->_session->setParameter("pm_mail_filters", $email_filters);
		$this->_session->setParameter("pm_mail_forwards", $email_forwards);
		$this->_session->setParameter("pm_mail_message_id", $message_id);

		return true;
	}

	/**
	 * 受信状態(読済み)に変更
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setMessageReadState(){
		$receiver_id = $this->_request->getParameter("receiver_id");

		$params = array(
			"receiver_id" => $receiver_id,
			"read_state" => 1
		);

		if(!$this->_db->updateExecute("pm_message_receiver", $params, "receiver_id", true)) {
			return false;
		}

		return true;
	}

	/**
	 * メッセージ操作
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function operation()
	{
		$op = $this->_request->getParameter("op");
		$select_all_flag = $this->_request->getParameter("select_all_flag");
		$filter = $this->_request->getParameter("filter");
		$search_flag = $this->_request->getParameter("search_flag");

		if ($select_all_flag == 1) {
			$receivers = $this->_pmView->getAllReceivers($search_flag);
			$this->_request->setParameter("receiver_id", $receivers);
		}

		if (empty($op)) {
			return false;
		}

		// 削除メッセージ
		if ($op == PM_ACTION_DELETE) {
			if(!$this->deleteMessage()) {
				return false;
			}
		} elseif ($op == PM_ACTION_READ) {
		    // 既読にする
			if(!$this->applyReadState(PM_READ_STATE)) {
				return false;
			}
		} elseif ($op == PM_ACTION_UNREAD) {
			// 未読にする
			if(!$this->applyReadState(PM_UNREAD_STATE)) {
				return false;
			}
		} elseif ($op == PM_ACTION_ADDFLAG) {
			// フラグを付ける
			if(!$this->applyFlag(PM_IMPORTANCE_FLAG)) {
				return false;
			}
		} elseif ($op == PM_ACTION_REMOVEFLAG) {
			// フラグをはずす
			if(!$this->applyFlag(PM_NO_FLAG)) {
				return false;
			}
		} elseif ($op == PM_ACTION_RESTORE) {
			// 元のトレイに戻す
			if(!$this->restore()) {
				return false;
			}
		} else {
			// タグ操作
			$tagOperationStr = split('_',$op);
			if (sizeof($tagOperationStr) == 2) {
				$tagOperation = $tagOperationStr[0];
				$tagID = $tagOperationStr[1];
				if ($tagOperation == PM_ACTION_ADDTAG || $tagOperation == PM_ACTION_REMOVETAG) {
					// タグ操作
					if(!$this->applyTag($tagOperation,$tagID)) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * 元のトレイに戻す操作
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function restore()
	{
	    $receiver_id = $this->_request->getParameter("receiver_id");

		if(!is_array($receiver_id)){
			$receivers = explode(" ",$receiver_id);
		} else {
			$receivers = $receiver_id;
		}

		foreach($receivers as $receiver) {
		    $params = array(
				"receiver_id" => $receiver,
				"delete_state" => PM_MESSAGE_STATE_NORMAL
			);

			if(!$this->_db->updateExecute("pm_message_receiver", $params, "receiver_id", true)) {
				return false;
			}
		}
        return true;
	}

	/**
	 * フラグ操作
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function applyFlag($flag)
	{
	    $receiver_id = $this->_request->getParameter("receiver_id");

		if(!is_array($receiver_id)){
			$receivers = explode(" ",$receiver_id);
		} else {
			$receivers = $receiver_id;
		}

		foreach($receivers as $receiver) {
		    $params = array(
				"receiver_id" => $receiver,
				"importance_flag" => $flag
			);

			if(!$this->_db->updateExecute("pm_message_receiver", $params, "receiver_id", true)) {
				return false;
			}
		}
        return true;
	}

	/**
	 * タグ設定操作
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function applyTag($tagOperation,$tagID)
	{
		$receiver_id = $this->_request->getParameter("receiver_id");

		if(!is_array($receiver_id)){
			$receivers = explode(" ",$receiver_id);
		} else {
			$receivers = $receiver_id;
		}

		if(!is_array($receivers)){
			return false;
		}

		foreach($receivers as $receiver_id) {
			if ($tagOperation == PM_ACTION_ADDTAG) {
				// タグを付ける
				$params = array(
					"tag_id" => intval($tagID),
					"receiver_id" => intval($receiver_id)
				);
    			$count = $this->_db->countExecute("pm_message_tag_link", $params);
				if ($count == 0) {
					// タグを付ける
					$params["message_id"] = $this->_pmView->getMessageID($receiver_id);

					$result = $this->_db->insertExecute("pm_message_tag_link", $params);

					if (!$result) {
						return false;
					}
				}
			} elseif ($tagOperation == PM_ACTION_REMOVETAG) {
				// タグをはずす
				$params = array(
					"tag_id" => intval($tagID),
					"receiver_id" => intval($receiver_id),
				);
				$result = $this->_db->deleteExecute("pm_message_tag_link", $params);
				if (!$result) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * 受信状態操作
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function applyReadState($read_state)
	{
	    $receiver_id = $this->_request->getParameter("receiver_id");

		if(!is_array($receiver_id)){
			$receivers = explode(" ",$receiver_id);
		} else {
			$receivers = $receiver_id;
		}

		foreach($receivers as $receiver) {
		    $params = array(
				"receiver_id" => $receiver,
				"read_state" => $read_state
			);

			if(!$this->_db->updateExecute("pm_message_receiver", $params, "receiver_id", true)) {
				return false;
			}
		}
        return true;
	}

	/**
	 * 削除メッセージ操作
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteMessage()
	{
	   	$receiver_id = $this->_request->getParameter("receiver_id");
		if(!is_array($receiver_id)){
			$receivers = explode(" ",$receiver_id);
		} else {
			$receivers = $receiver_id;
		}

		if(!is_array($receivers)){
			return false;
		}

		$dropped_messages = array();

		foreach($receivers as $receiver) {
			$message = $this->_pmView->getMessage($receiver);
			if ($message["delete_state"] == PM_MESSAGE_STATE_TRASH) {
				$delete_state = PM_MESSAGE_STATE_DELETE;

				$message_id = $message["message_id"];

				// メッセージ削除処理
				$deleteParams = array(
					"receiver_id" => $receiver
				);

				// タグ設定情報削除
				if (!$this->_db->deleteExecute("pm_message_tag_link", $deleteParams)) {
					$this->_db->addError();
				}

				// 受信人メッセージ情報削除
				if (!$this->_db->deleteExecute("pm_message_receiver", $deleteParams)) {
					$this->_db->addError();
				}

				if($this->_pmView->isDropedMessage($message_id, false)){
					$dropped_messages[] = $message_id;
				}
			} else {
				$delete_state = PM_MESSAGE_STATE_TRASH;

				// 更新受信人メッセージ情報の受信人メッセージ状態
				$updateParams = array(
					"receiver_id" => $receiver,
					"delete_state" => $delete_state
				);

				if (!$this->_db->updateExecute("pm_message_receiver", $updateParams, "receiver_id", true)) {
					return false;
				}
			}
		}

		if(sizeof($dropped_messages) > 0){
			$pm_delete_upload_ids = array();

			foreach($dropped_messages as $dropped_message_id){
				$deleteParams = array(
					"message_id" => $dropped_message_id
				);

				// メッセージ情報削除
				if (!$this->_db->deleteExecute("pm_message", $deleteParams)) {
					$this->_db->addError();
				}

				// Upload情報削除
				$getdata =& $this->_container->getComponent("GetData");
				$modules = $getdata->getParameter("modules");
				$module_id = $modules["pm"]["module_id"];
				$upload_id = $this->_pmView->getUploadId($module_id, $dropped_message_id);
				if($upload_id != false){
					$pm_delete_upload_ids[] = $upload_id;
				}
			}

			if(sizeof($pm_delete_upload_ids) > 0){
				$this->_session->setParameter("pm_delete_upload_ids", $pm_delete_upload_ids);
			}
		}

        return true;
	}

	/**
	 * タグデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setTag()
	{
		$receiver_list = $this->_request->getParameter("receiver_list");
		$search_flag = $this->_request->getParameter("search_flag");
		$select_all_flag = $this->_request->getParameter("select_all_flag");

		if ($select_all_flag == 1) {
			$receivers = $this->_pmView->getAllReceivers($search_flag);
			$receiver_list = implode(',',$receivers);
		}

		$tag_id = $this->_request->getParameter("tag_id");
        $tag_name = $this->_request->getParameter("tag_name");
		$tag_name = trim($tag_name, "\t\n \r\0\x0B");

		if (empty($tag_id)) {

			// タグ追加
			$params = array(
				"tag_name" => $tag_name
			);
			$result = $this->_db->insertExecute("pm_tag", $params, true, "tag_id");

			// タグ設定
			$tagID = $result;
			if (!empty($receiver_list)) {
				$receivers = split(',', $receiver_list);
				foreach($receivers as $receiver_id) {
					// タグ設定
					$params = array(
						"tag_id" => intval($tagID),
						"receiver_id" => intval($receiver_id),
						"message_id" => $this->_pmView->getMessageID($receiver_id)
					);

					$result = $this->_db->insertExecute("pm_message_tag_link", $params, true);

					if (!$result) {
						return false;
					}
			    }
			}
		} else {
            $params = array(
				"tag_id" => $tag_id
			);
			if (isset($tag_name)) {
				$params["tag_name"] = $tag_name;
			}
			$result = $this->_db->updateExecute("pm_tag", $params, "tag_id", true);
		}

		if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * タグデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	/*
	2009.2.10
	function deleteTag()
	{
		$params = array(
			"tag_id" => $this->_request->getParameter("tag_id")
		);

		if (!$this->_db->deleteExecute("pm_message_tag_link", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("pm_tag", $params)) {
    		return false;
    	}

		return true;
	}
	*/

	function deleteTag()
	{
		$params = array(
			"tag_id" => $this->_request->getParameter("tag_id")
		);

		// タグ設定情報を削除する
		if (!$this->_db->deleteExecute("pm_message_tag_link", $params)) {
    		return false;
    	}

		// フィルタを削除する
		$filters = $this->_pmView->getFilterLinkByTag($this->_request->getParameter("tag_id"));

		foreach ($filters as $filter) {
			$filter_id = $filter["filter_id"];
			$action_id = $filter["action_id"];

			// フィルタ処理内容設定情報を削除する
			$filterLinkParams = array(
				"filter_id" => $filter_id,
				"action_id" => $action_id
			);
			if (!$this->_db->deleteExecute("pm_filter_action_link", $filterLinkParams)) {
    			return false;
    		}

			// フィルタ処理内容設定情報がありません場合、フィルタ情報も削除する
			if (!$this->_pmView->checkFilterLinkExist($filter_id)) {
				$filterParams = array(
					"filter_id" => $filter_id
				);
				if (!$this->_db->deleteExecute("pm_filter", $filterParams)) {
    				return false;
    			}
			}
		}

		// タグ情報を削除する
    	if (!$this->_db->deleteExecute("pm_tag", $params)) {
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
	function &getContainedSign($body)
	{
		$containedSign = array();
		$uploadIds = array();

		$matches = array();
		$pattern = "/<a [^<>]*href=([\"'])[^\"'<>]*\?action=pm_download_main(&|&amp;)upload_id=[\d]+[^\"'<>]*\\1[^<>]*>.*<\/a>/sU";
		$uploadCount = preg_match_all($pattern, $body, $matches);

		$matches = array();
		// $pattern = "/<img [^<>]*src=([\"'])[^\"'<>]*\?action=pm_download_main&upload_id=([\d]+[^\"'<>])*\\1[^<>]*\/>/sU";
		$pattern = "/<img [^<>]*src=([\"'])[^\"'<>]*\\1[^<>]*>/sU";
		$imageCount = preg_match_all($pattern, $body, $matches);

		$matches = array();
		$pattern = "/\?action=pm_download_main(&|&amp;)upload_id=([\d]+)\"/sU";
		$allUploadCount = preg_match_all($pattern, $body, $matches);
		if(is_array($matches) && is_array($matches[count($matches)-1])){
			$uploadIds = array_merge($uploadIds, $matches[count($matches)-1]);
		}

		$imageCount = intval($imageCount);
		$uploadCount = intval($uploadCount);

		$containedSign = array(
			'imageCount' => (empty($imageCount) ? 0 : $imageCount),
			'uploadCount' => (empty($uploadCount) ? 0 : $uploadCount),
			'uploadIds' => $uploadIds
		);

		return $containedSign;
	}

	/**
	 * メッセージ転送設定情報を設定する
	 *
	 * @return boolean	true or false
	 * @access	public
	 */
	function setMailSetting()
	{
		$userId = $this->_session->getParameter('_user_id');
		$existingForwardState = $this->_pmView->getForwardState($userId);
		if ($existingForwardState === false) {
			return false;
		}

		$params = array(
			'forward_state' => $this->_request->getParameter('mail')
		);
		if (!isset($existingForwardState)) {
			$result = $this->_db->insertExecute('pm_forward', $params, true, 'forward_id');
		} else {
			$params['insert_user_id'] = $userId;
			$result = $this->_db->updateExecute('pm_forward', $params, 'insert_user_id', true);
		}

		if (!$result) {
			return false;
		}
		return true;
	 }

	/**
	 * フイルタデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	 function setFilter()
	 {
		$user_id = $this->_session->getParameter("_user_id");

		$filter_id = $this->_request->getParameter("filter_id");
		$senders = $this->_request->getParameter("senders");
		$subject = $this->_request->getParameter("subject");
		$keyword_list = $this->_request->getParameter("keyword_list");
		$apply_inbox_flag = $this->_request->getParameter("apply_inbox_flag");
		$actions = $this->_request->getParameter("filter_actions");
		$filter_actions_params = $this->_request->getParameter("filter_actions_params");

		if(!is_array($actions)){ $actions = array(); }
		if(!is_array($filter_actions_params)){ $filter_actions_params = array(); }

		$actions_params = array();
		foreach($filter_actions_params as $k => $v){
			$actions_params[(int)$k] = $v;
		}

		if(empty($apply_inbox_flag)){ $apply_inbox_flag = 0; }

		$sender_array = array();
		$sender_handles = explode(",", $senders);
		if(!is_array($sender_handles)){ $sender_handles = array(); }
		foreach($sender_handles as $sender_handle){
			$sender_id = $this->_pmView->getUserIdByHandle(trim($sender_handle));
			$sender_array[] = $sender_id . ":" . $sender_handle;
		}

		$params = array(
			"senders" => join(",", $sender_array),
			"subject" => $subject,
			"keyword_list" => $keyword_list,
			"apply_inbox_flag" => $apply_inbox_flag
		);

		if (empty($filter_id)) {
			$result = $this->_db->insertExecute("pm_filter", $params, true, "filter_id");
			if (!$result) {
				return false;
			}
			$filter_id = $result;
		}else{
			$params["filter_id"] = $filter_id;
			$result = $this->_db->updateExecute("pm_filter", $params, "filter_id", true);
			if (!$result) {
				return false;
			}
		}

		if(!empty($filter_id)) {

			if(sizeof($actions) > 0){
				$params = array(
					$filter_id,
					$user_id,
				);
				$actionIdArr = array();
				foreach ($actions as $actionId) {
					$actionIdArr[] = (int)$actionId;
				}

				$sql = "DELETE FROM {pm_filter_action_link} ".
					   "WHERE filter_id = ? AND insert_user_id = ? AND ".
					   "action_id NOT IN (" . join(",", $actionIdArr) . ")";

				if (!$this->_db->execute($sql, $params)) {
					return false;
				}

				foreach($actions as $action_id){
					$params = array(
						$filter_id,
						$action_id,
						$user_id
					);
					$sql = "SELECT count(*) as cnt ".
						   "FROM {pm_filter_action_link} ".
						   "WHERE filter_id = ? AND action_id = ? AND insert_user_id = ?";

					$counts = $this->_db->execute($sql, $params);
					if (!$counts) {
						return false;
					}

					if(isset($actions_params[$action_id])){
						$action_parameters = $actions_params[$action_id];
					}

					if(empty($action_parameters)){
						$action_parameters = '';
					}

					$params = array(
							"filter_id" => $filter_id,
							"action_id" => $action_id,
							"action_parameters" => $action_parameters
					);

					if($counts[0]['cnt']){
						$where_params = array(
							"filter_id" => $filter_id,
							"action_id" => $action_id
						);

						$result = $this->_db->updateExecute("pm_filter_action_link", $params, $where_params, true);
					}else{
						$result = $this->_db->insertExecute("pm_filter_action_link", $params, true);
					}

					if (!$result) {
						return false;
					}
				}
			}
		}

		if($apply_inbox_flag == 1){
			$this->applyFiltering($user_id, $filter_id);
		}

		return true;
    }

	/**
	 * フイルタデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteFilter(){

		$user_id = $this->_session->getParameter("_user_id");
		$filter_id = $this->_request->getParameter("filter_id");

		$params = array(
			"filter_id" => $filter_id,
			"insert_user_id" => $user_id
		);

		if (!$this->_db->deleteExecute("pm_filter_action_link", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("pm_filter", $params)) {
    		return false;
    	}

		return true;
	}

	/**
	 * フイルタ応用する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function applyFiltering($receiver_user_id, $filter_id){
		if(empty($receiver_user_id) || empty($filter_id)){
			return false;
		}

		$this->_request->setParameter("filter_id", $filter_id);
		$filter = $this->_pmView->getFilterInfo();
		$receivers = &$this->_getMatchedFilteringReceivers($receiver_user_id, $filter);

		if(is_array($receivers) && sizeof($receivers) > 0){
			for($i = 0; $i < sizeof($filter["actions"]); $i++){
				if($filter["actions"][$i]["sequence"] < 2){
					$handle_func = $filter["actions"][$i]["func"];
					$this->_pmFilterOperation->$handle_func($receivers, $filter["actions"][$i]["param"]);
				}
			}
		}

		return true;
	}

	/**
	 * 受信情報に、フイルタ応用する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function applyReceiverFiltering($receiver_user_id, $receiver_user_name, $receiver_auth_id, $receiver_id, $sender_id, $subject, $body){
		$this->_request->setParameter("insert_user_id", $receiver_user_id);
		$filters = $this->_pmView->getFiltersInfoByInsertUserId();
		$options = array(
			"receiver_user_id" => $receiver_user_id,
			"receiver_user_name" => $receiver_user_name,
			"receiver_auth_id" => $receiver_auth_id
		);

		$result = array();

		if(is_array($filters) && sizeof($filters) > 0){
			$receivers = array($receiver_id);

			foreach($filters as $filter){
				if($this->_checkFilterMatched($sender_id, $subject, $body, $filter)){
					for($i = 0; $i < sizeof($filter["actions"]); $i++){
						$handle_func = $filter["actions"][$i]["func"];
						$ret = $this->_pmFilterOperation->$handle_func($receivers, $filter["actions"][$i]["param"], $options);
						if(is_array($ret)){
							$result = array_merge($result, $ret);
						}
					}
				}
			}
		}

		if(sizeof($result) > 0){
			return $result;
		}else{
			return true;
		}
	}

	/**
	 * フイルタチェックする
	 *
     * @return boolean	true or false
	 * @access	private
	 */
	function _checkFilterMatched($sender_id, $subject, $body, &$filter){
		$result1 = true;
		$result2 = true;
		$result3 = true;

		if(!empty($filter["senders_id"])){
			if($sender_id != $filter["senders_id"]){
				$result1 = false;
			}
		}

		if(!empty($filter["subject"])){
			// if(strtoupper($subject) != strtoupper($filter["subject"])){
			if(!strstr(strtoupper($subject), strtoupper($filter["subject"]))){
				$result2 = false;
			}
		}

		if(!empty($filter["keyword_list"])){
			$keywords = explode(",", $filter["keyword_list"]);
			$matched_cnt = 0;
			for($i = 0; $i < count($keywords); $i++){
				$keyword = trim($keywords[$i]);
				if(!empty($keyword)){
					if(eregi($keyword, $subject)){
						$matched_cnt++;
					}
					if(eregi($keyword, $body)){
						$matched_cnt++;
					}
				}
			}

			if($matched_cnt <= 0){
				$result3 = false;
			}
		}

		return $result1 && $result2 && $result3;
	}

	/**
	 * 受信人より、フイルタ情報データを取得する
	 *
     * @return array	フイルタ情報データ
	 * @access	private
	 */
	function &_getMatchedFilteringReceivers($receiver_user_id, &$filter){
		$receivers = array();
		if(sizeof($filter) > 0){
			$sender_id = $filter["senders_id"];
			$subject = $filter["subject"];
			$keyword_list = $filter["keyword_list"];
			$the_keywords = explode(",", $keyword_list);
			$keywords = array();
			for($i = 0; $i < count($the_keywords); $i++){
				$the_keyword = trim($the_keywords[$i]);
				if(!empty($the_keyword) && !in_array($the_keyword, $keywords)){
					$keywords[] = $the_keyword;
				}
			}

			$params = array(
				$receiver_user_id,
				PM_LEFTMENU_INBOX
			);

			$sql = "SELECT r.receiver_id FROM {pm_message_receiver} as r, {pm_message} as m ".
				   "WHERE r.message_id = m.message_id ".
				   "AND r.receiver_user_id = ? ".
				   "AND r.mailbox = ? ";

			if(!empty($sender_id)){
				$params[] = $sender_id;
				$sql .= "AND m.insert_user_id = ? ";
			}

			if(!empty($subject)){
				$params[] = '%' . strtoupper($subject) . '%';
				$sql .= "AND upper(m.subject) LIKE ? ";
			}

			$keywords_cnt = count($keywords);
			for($i = 0; $i < $keywords_cnt; $i++){
				if($i == 0){ $sql .= "AND ( "; }
				$params[] = '%' . strtoupper($keywords[$i]) . '%';
				$params[] = '%' . strtoupper($keywords[$i]) . '%';
				$sql .= "(upper(m.subject) LIKE ? OR upper(m.body) LIKE ?) ";

				if($i == ($keywords_cnt - 1)){
					$sql .= ") ";
				}else{
					$sql .= "OR ";
				}
			}
			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->_db->addError();
			}else{
				for($i = 0; $i < count($result); $i++){
					$receivers[] = $result[$i]['receiver_id'];
				}
			}
		}
		return $receivers;
	}
}
?>
