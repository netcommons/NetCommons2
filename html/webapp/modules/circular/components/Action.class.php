<?php

/**
 * 回覧板アクションコンポーネント
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Components_Action
{
	/**
	 * @var DIコンテナ
	 *
	 * @access  private
	 */
	var $_container = null;

	/**
	 * @var DBオブジェクト
	 *
	 * @access  private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクト
	 *
	 * @access  private
	 */
	var $_request = null;

	/**
	 * @var Sessionオブジェクト
	 *
	 * @access  private
	 */
	var $_session = null;

	/**
	 * @var	DBオブジェクト
	 *
	 * @access private
	 */
	var $_user_id = null;

	/**
	 * コンストラクタ
	 *
	 * @access public
	 */
	function Circular_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent('DbObject');
		$this->_request =& $this->_container->getComponent('Request');
		$this->_session =& $this->_container->getComponent('Session');
		$this->_user_id = $this->_session->getParameter('_user_id');
	}

	/**
	 * ブロック情報を設定する
	 *
	 * @return  boolean (true:正常／false:異常)
	 * @access  public
	 */
	function setupBlock()
	{
		$whereParams = array(
			'room_id'=>$this->_request->getParameter('room_id'),
			'block_id'=>$this->_request->getParameter('block_id')
		);
		$updParams = array(
			'visible_row'=>$this->_request->getParameter('visible_row'),
			'block_type'=>$this->_request->getParameter('block_type')
		);
		$result = $this->_db->updateExecute('circular_block', $updParams, $whereParams, true);
		if($result === false) {
			$this->_db->addError();
			return false;
		}
		return true;
	}

	/**
	 * ルーム設定情報を設定する
	 *
	 * @return  boolean (true:正常／false:異常)
	 * @access  public
	 */
	function setupConfig()
	{
		$room_id = $this->_request->getParameter('room_id');
		$createAuth = $this->_request->getParameter('create_authority');
		$mailSubject = $this->_request->getParameter('mail_subject');
		$mailBody = $this->_request->getParameter('mail_body');

		$mailSubject = (empty($mailSubject) ? CIRCULAR_MAIL_SUBJECT : $mailSubject);
		$mailBody = (empty($mailBody) ? CIRCULAR_MAIL_BODY : $mailBody);

		$whereParams = array(
			'room_id'=>$room_id
		);
		$updParams = array(
			'create_authority' => $createAuth,
			'mail_subject' => $mailSubject,
			'mail_body' => $mailBody
		);
		$result = $this->_db->updateExecute('circular_config', $updParams, $whereParams, true);
		if($result === false) {
			$this->_db->addError();
			return false;
		}
		return true;
	}

	/**
	 * 回覧を登録する
	 *
	 * @return int   回覧ID
	 * @access public
	 */
	function registCircular()
	{
		$roomId = $this->_request->getParameter('room_id');
		$circularSubject = $this->_request->getParameter('circular_subject');
		$circularBody = $this->_request->getParameter('circular_body');
		$iconName = $this->_request->getParameter('icon_name');

		$insertParams = array(
			'room_id' => $roomId,
			'circular_subject' => $circularSubject,
			'circular_body' => $circularBody,
			'icon_name' => $iconName,
			'post_user_id' => $this->_user_id,
			'period' => $this->_request->getParameter('period'),
			'status' => CIRCULAR_STATUS_CIRCULATING,
			'reply_type' => $this->_request->getParameter('reply_type'),
			'seen_option' => $this->_request->getParameter('seen_option')
		);
		$circularId = $this->_db->insertExecute('circular', $insertParams, true, 'circular_id');
		if ($circularId === false) {
			$this->_db->addError();
			return false;
		}
		$circularId = intval($circularId);

		$receiveUserIds = $this->_request->getParameter('receive_user_ids');
		$receiveUserIds = explode(",", $receiveUserIds);
		foreach ($receiveUserIds as $userID) {
			$insertParams = array(
				'room_id' => $roomId,
				'circular_id' => $circularId,
				'receive_user_id' => $userID,
				'reply_flag' => CIRCULAR_REPLY_FLAG_UNSEEN
			);
			$result = $this->_db->insertExecute('circular_user', $insertParams, true);
			if ($result === false) {
				$this->_db->addError();
				return false;
			}
		}
		$replyType = $this->_request->getParameter('reply_type');
		if ($replyType == CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE || $replyType == CIRCULAR_REPLY_TYPE_RADIO_VALUE) {
			$choiceValues = $this->_request->getParameter('choice_value');
			$sequence = 1;
			foreach ($choiceValues as $choiceValue) {
				$insertParams = array(
					'circular_id' => $circularId,
					'choice_sequence' => $sequence,
					'choice_value' => $choiceValue
				);
				$result = $this->_db->insertExecute('circular_choice', $insertParams, true, 'choice_id');
				if ($result === false) {
					$this->_db->addError();
					return false;
				}
				$sequence++;
			}
		}

		$blockId = $this->_request->getParameter('block_id');
		$this->_session->setParameter(array('circular_id', $blockId), $circularId);

		return $circularId;
	}

	/**
	 * 回覧を削除する
	 *
	 * @return  boolean (true:正常／false:異常)
	 * @access  public
	 */
	function deleteCircular()
	{
		$circularId = $this->_request->getParameter('circular_id');
		if (!$circularId) {
			return false;
		}
		$deleteParams = array(
			'room_id'=>$this->_request->getParameter('room_id'),
			'circular_id'=>$circularId
		);

		$result = $this->_db->deleteExecute('circular_user', $deleteParams);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$result = $this->_db->deleteExecute('circular', $deleteParams);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$result = $this->_db->deleteExecute('circular_choice', array("circular_id"=>$circularId));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$result = $this->_db->deleteExecute('circular_postscript', array("circular_id"=>$circularId));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$result = $whatsnewAction->delete($circularId);
		if($result === false) {
			return false;
		}
		$this->_request->setParameter("visible_row",null);

		return true;
	}

	/**
	 * 回覧に回答する
	 *
	 * @return  boolean (true:正常／false:異常)
	 * @access  public
	 */
	function replyCircular()
	{
		$roomId = $this->_request->getParameter('room_id');
		$circularId = $this->_request->getParameter('circular_id');
		$replyType = $this->_request->getParameter('reply_type');

		$updParams = array();
		if ($replyType == CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE || $replyType == CIRCULAR_REPLY_TYPE_RADIO_VALUE) {
			$choices = $this->_request->getParameter('choices');
			$choicesStr = '';
			if (!empty($choices)) {
				foreach ($choices as $choice) {
					$choicesStr .= $choice . ' , ';
				}
				$choicesStr = substr($choicesStr, 0, -3);
			}
			$updParams['reply_choice'] = $choicesStr;
		} else {
			$updParams['reply_body'] = trim($this->_request->getParameter('reply_body'));
		}

		$whereParams = array(
			'room_id' => $roomId,
			'circular_id' => $circularId,
			'receive_user_id' => $this->_user_id
		);
		$result = $this->_db->updateExecute('circular_user', $updParams, $whereParams, true);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		$result = $this->updateUserSeen('reply');
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 回覧作成者追記情報を登録する
	 *
	 * @return  boolean (true:正常／false:異常)
	 * @access	public
	 */
	function addPostscript()
	{
		$sequence = $this->_db->countExecute('circular_postscript', array('circular_id' => $this->_request->getParameter('circular_id')));
		if ($sequence === false) {
			return false;
		}
		if (empty($sequence)) {
			$sequence = 1;
		} else {
			$sequence = $sequence + 1;
		}
		$circularId = $this->_request->getParameter('circular_id');
		$insertParams = array(
			'circular_id'=>$circularId,
			'postscript_sequence'=>$sequence,
			'postscript_value'=>$this->_request->getParameter('postscript_body')
		);
		$postscriptId = $this->_db->insertExecute('circular_postscript', $insertParams, true, 'postscript_id');
		if ($postscriptId === false) {
			return false;
		}
		$result = $this->_db->updateExecute('circular_user', array('reply_flag'=>CIRCULAR_LIST_TYPE_UNSEEN), array('circular_id'=>$circularId));
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * 回覧先グループを登録する
	 *
	 * @return  boolean (true:正常／false:異常)
	 * @access	public
	 */
	function entryGroupMember()
	{
		$groupId = $this->_request->getParameter('group_id');
		$groupName = $this->_request->getParameter('group_name');
		$groupMember = $this->_request->getParameter('group_member');
		$returnVal = "";
		if ($groupId) {
			$setParams = array(
				'group_name' => $groupName,
				'group_member' => $groupMember,
			);
			$whereParams = array(
				'user_id' => $this->_user_id,
				'group_id' => $groupId
			);
			$result = $this->_db->updateExecute('circular_group', $setParams, $whereParams, true);
			if ($result === false) {
				$this->_db->addError();
				return false;
			}
			return true;
		} else {
			$myRoomPage = $this->_session->getParameter('_self_myroom_page');
			$insertParams = array(
				'user_id' => $this->_user_id,
				'group_name' => $groupName,
				'group_member' => $groupMember,
				'room_id' => $myRoomPage['page_id']
			);
			$groupId = $this->_db->insertExecute('circular_group', $insertParams, true, 'group_id');
			if ($groupId === false) {
				$this->_db->addError();
				return false;
			}
			return $groupId;
		}
	}

	/**
	 * 回覧先グループを削除する
	 *
	 * @return  boolean (true:正常／false:異常)
	 * @access	public
	 */
	function deleteGroup()
	{
		$groupId = $this->_request->getParameter('group_id');
		if ($groupId) {
			$result = $this->_db->deleteExecute('circular_group', array('group_id'=>$groupId));
			if ($result === false) {
				$this->_db->addError();
				return false;
			}
		}
		return $groupId;
	}

	/**
	 * 回覧状況を既読にする
	 *
	 * @param   string  $type	既読設定タイプ
	 * @return  boolean (true:正常／false:異常)
	 * @access  public
	 */
	function updateUserSeen($type)
	{
		$roomId = $this->_request->getParameter('room_id');
		$circularId = $this->_request->getParameter('circular_id');

		$whereParams = array(
			'room_id' => $roomId,
			'circular_id' => $circularId,
		);
		$sql = "SELECT seen_option FROM {circular} ";
		$sql .= $this->_db->getWhereSQL($params, $whereParams);
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if (!isset($result[0])) {
			return true;
		}
		$option = $result[0]['seen_option'];
		switch ($type) {
			case 'reply':
				if ($option == CIRCULAR_SEEN_OPTION_VISIT) {
					return true;
				}
				break;
			case 'visit':
				if ($option == CIRCULAR_SEEN_OPTION_REPLY) {
					return true;
				}
				$params = array();
				$whereParams += array('receive_user_id'=>$this->_user_id);
				$sql = "SELECT reply_flag FROM {circular_user} ";
				$sql .= $this->_db->getWhereSQL($params, $whereParams);
				$result = $this->_db->execute($sql, $params);
				if ($result === false) {
					return false;
				}
				if (!isset($result[0])) {
					return true;
				}
				$replyFlg = $result[0]['reply_flag'];
				if ($replyFlg == CIRCULAR_LIST_TYPE_SEEN) {
					return true;
				}
				break;
			default:
				return false;
		}

		$whereParams = array(
			'room_id' => $roomId,
			'circular_id' => $circularId,
			'receive_user_id' => $this->_user_id
		);
		$result = $this->_db->updateExecute('circular_user', array('reply_flag'=>CIRCULAR_REPLY_FLAG_SEEN), $whereParams, true);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		$whereParams = array(
			'room_id' => $roomId,
			'circular_id' => $circularId,
			'reply_flag' => CIRCULAR_REPLY_FLAG_UNSEEN
		);
		$count = $this->_db->countExecute('circular_user', $whereParams);
		if ($count === false) {
			$this->_db->addError();
			return false;
		}

		if (intval($count) === 0) {
			$whereParams = array(
				'room_id' => $roomId,
				'circular_id' => $circularId,
				'status'=>CIRCULAR_STATUS_CIRCULATING
			);
			$result = $this->_db->updateExecute('circular', array('status'=>CIRCULAR_STATUS_CIRCULATED), $whereParams, true);
			if ($result === false) {
				$this->_db->addError();
				return false;
			}
		}

		return true;
	}

	/**
	 * 新着情報にセットする
	 *
	 * @param   string  $circularId	回覧ID
	 * @return  boolean (true:正常／false:異常)
	 * @access	public
	 */
	function setWhatsnew($circularId)
	{
		$posts = $this->_db->selectExecute("circular", array("circular_id"=>$circularId));
		if (empty($posts)) {
			return false;
		}

		$pageId = $this->_request->getParameter("page_id");
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");

		$whatsnew = array(
			"unique_id" => $circularId,
			"title" => $posts[0]["circular_subject"],
			"description" => $posts[0]["circular_body"],
			"action_name" => "circular_view_main_detail",
			"parameters" => "circular_id=". $circularId . "&page_id=" . $pageId,
			"insert_time" => $posts[0]["update_time"],
			"update_time" => $posts[0]["update_time"]
		);
		$result = $whatsnewAction->auto($whatsnew);
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * 期限を更新する
	 *
	 * @param   string  $circularId	回覧ID
	 * @return  boolean (true:正常／false:異常)
	 * @access	public
	 */
	function extendPeriod()
	{
		$columns = array(
			'circular_id' => $this->_request->getParameter('circular_id'),
			'period' => $this->_request->getParameter('period')
		);

		if (!$this->_db->updateExecute('circular', $columns, 'circular_id', true)) {
			return false;
		}
		return true;
	}
}
?>