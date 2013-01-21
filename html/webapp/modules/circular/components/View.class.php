<?php

/**
 * 回覧板ビューコンポーネント
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Components_View
{
	/**
	 * @var	DIコンテナ
	 *
	 * @access private
	 */
	var $_container = null;

	/**
	 * @var	Requestオブジェクト
	 *
	 * @access private
	 */
	var $_request = null;

	/**
	 * @var	Sessionオブジェクト
	 *
	 * @access private
	 */
	var $_session = null;

	/**
	 * @var	DBオブジェクト
	 *
	 * @access private
	 */
	var $_db = null;

	/**
	 * @var	DBオブジェクト
	 *
	 * @access private
	 */
	var $_user_id = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Circular_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_request =& $this->_container->getComponent('Request');
		$this->_session =& $this->_container->getComponent('Session');
		$this->_db =& $this->_container->getComponent('DbObject');
		$this->_user_id = $this->_session->getParameter('_user_id');
	}

	/**
	 * ブロック情報取得
	 * @return	mixed	(正常時：ブロック情報配列／異常時;false)
	 * @access public
	 */
	function getBlock()
	{
		$params = array(
			'room_id'=>$this->_request->getParameter('room_id'),
			'block_id'=>$this->_request->getParameter('block_id')
		);
		$result = $this->_db->selectExecute('circular_block', $params);
		if ($result === false || !isset($result[0])) {
			$this->_db->addError();
			return false;
		}
		return $result[0];
	}

	/**
	 * 設定情報を取得
	 * @return	mixed	(正常時：ルーム設定情報配列／異常時;false)
	 * @access public
	 */
	function getConfig()
	{
		$params = array(
			'room_id'=>$this->_request->getParameter('room_id')
		);
		$result = $this->_db->selectExecute('circular_config', $params);
		if ($result === false || !isset($result[0])) {
			$this->_db->addError();
			return false;
		}
		return $result[0];
	}

	/**
	 * メール送信時のユーザ情報を取得
	 *
	 * @param int $circularId  回覧板ID
	 * @return	mixed	(正常時：メール送信先情報配列／異常時;false)
	 * @access public
	 */
	function getToUsersInfo($circularId)
	{
		$params = array(
			'U.circular_id'=>$circularId
		);
		$sql = "";
		$sql .= "SELECT ";
		$sql .= 	"U.receive_user_id ";
		$sql .= "FROM ";
		$sql .= 	"{circular_user} U ";
		$sql .= "WHERE U.circular_id = ? ";
		$users = $this->_db->execute($sql, $params);

		$userIdStr = "'".$this->_user_id."',";
		foreach ($users as $user) {
			$userIdStr .= "'".$user["receive_user_id"]."',";
		}
		$userIdStr = substr($userIdStr, 0, -1);
		$whereParams = array(
			"{users}.user_id IN ( ".$userIdStr.")"=>null
		);
		$usersView =& $this->_container->getComponent('usersView');
		$toUsers = $usersView->getSendMailUsers(null, null, null, $whereParams);

		return $toUsers;
	}

	/**
	 * 回覧一覧情報を取得
	 *
	 * @param	int	$listType	表示種別
	 * @param	int	$dispCnt	取得件数
	 * @param	int	$begin	開始行
	 * @param	string	$orderType	並び順種別
	 * @return	mixed	(正常時：回覧一覧情報配列／異常時;false)
	 * @access	public
	 */
	function getCircularList($listType, $dispCnt, $begin, $orderType)
	{
		$roomId = intval($this->_request->getParameter('room_id'));

		$params = array();
		$sql = "";
		$sql .= "SELECT ";
		$sql .= 	"c.room_id, ";
		$sql .= 	"c.circular_id, c.circular_subject, ";
		$sql .= 	"c.icon_name, c.period, c.reply_type, ";
		if ($listType === CIRCULAR_LIST_TYPE_UNSEEN || $listType === CIRCULAR_LIST_TYPE_SEEN) {
			$sql .= 	"u.reply_flag, ";
		} else {
			$sql .= 	"c.status, ";
		}
		$sql .= 	"c.seen_option, ";
		$sql .= 	"c.insert_user_id, ";
		$sql .= 	"c.insert_time, ";
		$sql .= 	"c.update_time, ";
		$sql .= 	"users.handle ";
		$sql .= "FROM ";
		$sql .= 	"{circular} c ";
		if ($listType === CIRCULAR_LIST_TYPE_UNSEEN || $listType === CIRCULAR_LIST_TYPE_SEEN) {
			$sql .= "INNER JOIN {circular_user} u ";
			$sql .= 	"ON u.room_id = c.room_id ";
			$sql .= 	"AND u.circular_id = c.circular_id ";
		}
		$sql .= "INNER JOIN {users} users ";
		$sql .= 	"ON users.user_id = c.post_user_id ";
		$sql .= "WHERE c.room_id = ? ";
		$params['c.room_id'] = $roomId;

		if ($listType === CIRCULAR_LIST_TYPE_UNSEEN) {
			$sql .= 	"AND u.reply_flag = ? ";
			$sql .= 	"AND u.receive_user_id = ? ";
			$params['u.reply_flag'] = CIRCULAR_REPLY_FLAG_UNSEEN;
			$params['u.receive_user_id'] = $this->_user_id;

		} else if ($listType === CIRCULAR_LIST_TYPE_SEEN) {
			$sql .= 	"AND u.reply_flag = ? ";
			$sql .= 	"AND u.receive_user_id = ? ";
			$params['u.reply_flag'] = CIRCULAR_REPLY_FLAG_SEEN;
			$params['u.receive_user_id'] = $this->_user_id;

		} else if ($listType === CIRCULAR_LIST_TYPE_CIRCULATING || $listType === CIRCULAR_LIST_TYPE_CIRCULATED) {
			$sql .= 	"AND c.status IN (".CIRCULAR_STATUS_CIRCULATING.",".CIRCULAR_STATUS_CIRCULATED.") ";
			$sql .= 	"AND c.post_user_id = ? ";
			$params['c.post_user_id'] = $this->_user_id;
		}
		$sql .= "ORDER BY c.insert_time ".$orderType;

		$result = $this->_db->execute($sql, $params ,$dispCnt, $begin, true, array($this, '_fetchCircularList'), array('list_type'=>$listType));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * 回覧板データ配列を生成
	 *
	 * @param object $recordSet ADORecordSet
	 * @return array データ配列
	 * @access private
	 */
	function &_fetchCircularList(&$recordSet, $params)
	{
		$format = "YmdHis";
		list($today,$soonDate) = $this->_getSoonPeriod($format);
		$mobile = false;
		if ($this->_session->getParameter('_mobile_flag') == true) {
			$mobile = true;
		}

		$circulars = array();
		while ($circular = $recordSet->fetchRow()) {
			if ($params['list_type'] === CIRCULAR_LIST_TYPE_CIRCULATING ||
				$params['list_type'] === CIRCULAR_LIST_TYPE_ALL) {
				$whereParams = array(
					'room_id'=>$circular['room_id'],
					'circular_id'=>$circular['circular_id']
				);
				$circular['total_count'] = $this->_db->countExecute('circular_user', $whereParams);
				$whereParams['reply_flag'] = _ON;
				$circular['seen_count'] = $this->_db->countExecute('circular_user', $whereParams);
			}

			if ($mobile) {
				$result = $this->_db->countExecute('circular_user', array("receive_user_id"=>$this->_user_id));
				if (empty($result)) {
					$circular["has_reply_auth"] = false;
				} else {
					$circular["has_reply_auth"] = true;
				}
	
				$date = timezone_date($circular["insert_time"], false, "Ymd");
			}
			if (empty($circular['period'])) {
				if ($mobile) {
					$circulars[$date][] = $circular;
				} else {
					$circulars[] = $circular;
				}

				continue;
			}

			list($circular['displayPeriodDate'], $ymdPeriodDate) = $this->getDisplayPeriodDate($circular['period']);

			if ($today > $ymdPeriodDate) {
				$circular['periodClassName'] = CIRCULAR_PERIOD_OVER;
				if ($mobile) {
					$circular["has_reply_auth"] = false;
				}
			} else if ($soonDate >= $ymdPeriodDate) {
				$circular['periodClassName'] = CIRCULAR_PERIOD_SOON;
			}

			if ($mobile) {
				$circulars[$date][] = $circular;
			} else {
				$circulars[] = $circular;
			}
		}

		return $circulars;
	}

	/**
	 * 表示する期限を取得
	 *
	 * @param $period データベースに登録されている期限
	 * @param $format 表示する日付フォーマット
	 * @return string 表示する期限
	 * @access private
	 */
	function getDisplayPeriodDate($period, $format = _DATE_FORMAT)
	{
		$displayPeriodDate = timezone_date_format($period, null);
		if (substr($period, 8) == '000000') {
			$previousDay = -1;
			$format = str_replace('H', '24', $format);
		} else {
			$previousDay = 0;
		}

		$date = mktime(intval(substr($period, 8, 2)),
						intval(substr($period, 10, 2)),
						intval(substr($period, 12, 2)),
						intval(substr($period, 4, 2)),
						intval(substr($period, 6, 2)) + $previousDay,
						intval(substr($period, 0, 4)));
		$displayPeriodDate = date($format, $date);
		$ymdPeriodDate = date('YmdHis', $date);

		return array($displayPeriodDate, $ymdPeriodDate);
	}

	/**
	 * 期限を取得
	 *
	 * @param $format	日付フォーマット
	 * @return array	($today,$soonDate)
	 * @access private
	 */
	function _getSoonPeriod($format)
	{
		$configView =& $this->_container->getComponent('configView');
		$moduleID = $this->_request->getParameter('module_id');
		$config = $configView->getConfigByConfname($moduleID, 'soon_period');
		if ($config === false) {
			return $config;
		}
		$soonPeriod = $config['conf_value'];

		$today = timezone_date_format(null, null);
		$soonDate = mktime(0, 0, 0,
							intval(substr($today, 4, 2)),
							intval(substr($today, 6, 2)) + $soonPeriod,
							intval(substr($today, 0, 4)));
		$format = "YmdHis";
		$today = timezone_date_format(null, $format);
		$soonDate = date($format, $soonDate);

		return array($today,$soonDate);
	}

	/**
	 * 回覧数を取得
	 *
	 * @param $listType	表示種別
	 * @return mixed	(正常時：回覧数／異常時;false)
	 * @access public
	 */
	function getCircularCount($listType)
	{
		$roomId = intval($this->_request->getParameter('room_id'));
		$listType = intval($listType);

		$sql = "";
		$sql .= "SELECT ";
		$sql .= 	"COUNT(c.circular_id) AS count ";
		$sql .= "FROM ";
		$sql .= 	"{circular} c ";

		if ($listType === CIRCULAR_LIST_TYPE_UNSEEN || $listType === CIRCULAR_LIST_TYPE_SEEN) {
			$sql .= "INNER JOIN ";
			$sql .= 	"{circular_user} u ";
			$sql .= "ON ";
			$sql .= 	"(c.circular_id = u.circular_id) ";
		}
		$sql .= "WHERE c.room_id  = ? ";
		$params['c.room_id'] = $roomId;

		if ($listType === CIRCULAR_LIST_TYPE_UNSEEN) {

			$sql .= 	"AND u.receive_user_id = ? ";
			$sql .= 	"AND u.reply_flag = ? ";
			$params['u.receive_user_id'] = $this->_user_id;
			$params['u.reply_flag'] = CIRCULAR_REPLY_FLAG_UNSEEN;

		} else if ($listType === CIRCULAR_LIST_TYPE_SEEN) {

			$sql .= 	"AND u.receive_user_id = ? ";
			$sql .= 	"AND u.reply_flag = ? ";
			$params['u.receive_user_id'] = $this->_user_id;
			$params['u.reply_flag'] = CIRCULAR_REPLY_FLAG_SEEN;

		} else if ($listType === CIRCULAR_LIST_TYPE_CIRCULATING || $listType === CIRCULAR_LIST_TYPE_CIRCULATED) {

			$sql .= 	"AND c.post_user_id = ? ";
			$params['c.post_user_id'] = $this->_user_id;

		}

		$result = $this->_db->execute($sql, $params);
		if ( $result === false ) {
			$this->_db->addError();
			return false;
		}

		return $result[0]['count'];
	}

	/**
	 * 回覧詳細情報を取得
	 * @param $circularId	回覧板ID
	 * @return mixed	(正常時：回覧情報配列／異常時;false)
	 * @access public
	 */
	function getCircularInfo($circularId=null)
	{
		if (!$circularId) {
			$circularId = $this->_request->getParameter('circular_id');
		}
		$params = array(
			'circular_id'=>$circularId
		);
		$sql = "";
		$sql .= "SELECT ";
		$sql .= 	"c.circular_subject, ";
		$sql .= 	"c.circular_body, ";
		$sql .= 	"c.post_user_id, ";
		$sql .= 	"c.period, ";
		$sql .= 	"c.icon_name, ";
		$sql .= 	"c.reply_type, ";
		$sql .= 	"c.insert_time, ";
		$sql .= 	"c.update_time, ";
		$sql .= 	"users.handle ";
		$sql .= "FROM ";
		$sql .= 	"{circular} c ";
		$sql .= "INNER JOIN {users} users ";
		$sql .= 	"ON c.post_user_id = users.user_id ";
		$sql .= "WHERE c.circular_id = ? ";

		$result = $this->_db->execute($sql, $params, null, null, true, array($this, '_fetchCircularInfo'));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		$replyType = $result['reply_type'];
		if ($replyType == CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE || $replyType == CIRCULAR_REPLY_TYPE_RADIO_VALUE) {
			$choices = $this->getCircularChoice();
			if ($choices === false) {
				$this->_db->addError();
				return false;
			}
			$result['choices'] = $choices;
		}
		$postscripts = $this->getPostscript();
		if ($postscripts === false) {
			$this->_db->addError();
			return false;
		}
		$result['postscripts'] = $postscripts;

		return $result;
	}

	/**
	 * 回覧板データ配列を生成
	 *
	 * @param object $recordSet ADORecordSet
	 * @return array データ配列
	 * @access private
	 */
	function &_fetchCircularInfo(&$recordSet)
	{
		$circular = $recordSet->fetchRow();
		if (empty($circular['period'])) {
			return $circular;
		}

		list($circular['period'], $ymdPeriodDate) = $this->getDisplayPeriodDate($circular['period']);
		$today = timezone_date_format(null, 'YmdHis');
		if ($today > $ymdPeriodDate) {
			$circular['periodClassName'] = CIRCULAR_PERIOD_OVER;
		}

		return $circular;
	}

	/**
	 * 回覧選択肢を取得
	 *
	 * @return mixed	(正常時：回覧選択肢情報配列／異常時;false)
	 * @access public
	 */
	function getCircularChoice()
	{
		$params = array(
			'circular_id'=>$this->_request->getParameter('circular_id')
		);
		$result = $this->_db->selectExecute('circular_choice', $params, array('choice_sequence'=>'ASC'),null,null,array($this, '_fetchChoice'));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * 選択肢情報のデータ配列を生成
	 *
	 * @param object $recordSet ADORecordSet
	 * @return array データ配列
	 * @access private
	 */
	function &_fetchChoice(&$recordSet)
	{
		$choiceLabels = explode("|", CIRCULAR_REPLY_CHOICE_LABEL);
		$count = 0;
		$choices = array();
		while ($choice = $recordSet->fetchRow()) {
			$choice["label"] = $choiceLabels[$count % count($choiceLabels)];
			$choices[] = $choice;
			$count++;
		}
		return $choices;
	}

	/**
	 * 回覧先ユーザ情報を取得
	 *
	 * @param $replyType	回答種別
	 * @param $flg			自分を含むかどうかのフラグ
	 * @param $limit
	 * @param $offset
	 * @return mixed	(正常時：回覧先ユーザ情報／異常時;false)
	 * @access public
	 */
	function getCircularUsers($replyType, $flg, $limit=0, $offset=0)
	{
		$sql = "";
		$sql .= "SELECT ";
		$sql .= 	"u.receive_user_id, ";
		$sql .= 	"u.update_time, ";
		if ($replyType == CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE || $replyType == CIRCULAR_REPLY_TYPE_RADIO_VALUE) {
			$sql .= 	"u.reply_choice AS reply, ";
		} else {
			$sql .= 	"u.reply_body AS reply, ";
		}
		$sql .= 	"u.reply_flag, ";
		$sql .= 	"users.handle ";
		$sql .= "FROM ";
		$sql .= 	"{circular_user} u ";
		$sql .= "INNER JOIN {users} users ";
		$sql .= 	"ON u.receive_user_id = users.user_id ";

		$whereParams = array(
			'room_id'=>$this->_request->getParameter('room_id'),
			'circular_id'=>$this->_request->getParameter('circular_id')
		);
		$sql .= $this->_db->getWhereSQL($params, $whereParams);

		$myself = false;
		$_user_auth_id = $this->_session->getParameter('_user_auth_id');
		if ($flg && $offset === 0) {
			$sql1 = $sql . " AND u.receive_user_id = ? ";
			$params1 = $params + array('receive_user_id'=>$this->_user_id);
			$myself = $this->_db->execute($sql1, $params1);
			if ($myself === false) {
				return false;
			}
			if ($_user_auth_id < CIRCULAR_ALL_VIEW_AUTH) {
				$limit--;
			}
		} else {
			if ($_user_auth_id < CIRCULAR_ALL_VIEW_AUTH) {
				$offset--;
			}
		}

		$sql .= " AND u.receive_user_id <> '".$this->_user_id."'";

		$sql .= " ORDER BY users.login_id ASC ";

		$others = $this->_db->execute($sql, $params, $limit, $offset, true);
		if ($others === false) {
			$this->_db->addError();
			return false;
		}

		if ($myself) {
			$result = array_merge($myself,$others);
		} else {
			$result = $others;
		}

		return $result;
	}

	/**
	 * グループに所属するユーザ情報を取得
	 *
	 * @param $roomId	ルームID
	 * @param $receiveUserIdList
	 * @return mixed	(正常時：グループに所属するユーザ情報／異常時;false)
	 * @access public
	 */
	function getGroupUserInfo($roomId, $receiveUserIdList=array())
	{
		$pagesInfo = $this->_db->selectExecute('pages', array('page_id'=>$roomId));
		if (empty($pagesInfo)) {
			return false;
		}

		$params = array(
			$roomId,
			$this->_user_id,
			_USER_ACTIVE_FLAG_ON
		);

		$defaultEntry = $pagesInfo[0]['default_entry_flag'];
		if ($pagesInfo[0]['space_type'] == _SPACE_TYPE_PUBLIC) {
			$defaultEntryAuthority = $this->_session->getParameter('_default_entry_auth_public');
		}
		if ($pagesInfo[0]['space_type'] == _SPACE_TYPE_GROUP
			&& $pagesInfo[0]['private_flag'] == _OFF) {
			$defaultEntryAuthority = $this->_session->getParameter('_default_entry_auth_group');
		}
		if ($pagesInfo[0]['space_type'] == _SPACE_TYPE_GROUP
			&& $pagesInfo[0]['private_flag'] == _ON) {
			$defaultEntryAuthority = $this->_session->getParameter('_default_entry_auth_private');
		}

		if ($defaultEntry == _ON
			&& $defaultEntryAuthority != _AUTH_GUEST) {
			$whereSql = 'U.role_authority_id != ? '
						. 'AND (A.user_authority_id > ? '
							. 'OR P.role_authority_id IS NULL) ';
			$params[] = _ROLE_AUTH_GUEST;
		} else {
			$whereSql = 'A.user_authority_id > ? ';
		}
		$params[] = _AUTH_GUEST;

		if (count($receiveUserIdList) > 0) {
			$whereSql .= "AND U.user_id NOT IN ('" . implode("','", $receiveUserIdList) . "') ";
		}

		if (!CIRCULAR_ALLOW_ADMINISTRATOR) {
			$whereSql .= "AND UA.user_authority_id != ? ";
			$params[] = _AUTH_ADMIN;
		}
		
		$sql = "SELECT U.user_id, "
					. "U.handle"
			. " FROM "
			. " {users} U "
			. "LEFT JOIN {pages_users_link} P "
				. "ON U.user_id = P.user_id "
				. "AND P.room_id = ? "
			. "LEFT JOIN {authorities} A "
				. "ON P.role_authority_id = A.role_authority_id "
			. "LEFT JOIN {authorities} UA "
				. "ON U.role_authority_id = UA.role_authority_id "
				. "WHERE U.user_id != ? "
			. "AND U.active_flag = ? "
			. "AND " . $whereSql
			. "ORDER BY login_id ASC";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * 回答を取得
	 *
	 * @return text	(回答タイプ-選択/択一式：選択肢／記述式:コメント)
	 * @access public
	 */
	function getReplyComment()
	{
		$params = array(
			'room_id'=>$this->_request->getParameter('room_id'),
			'circular_id'=>$this->_request->getParameter('circular_id'),
			'receive_user_id'=>$this->_user_id
		);
		$result = $this->_db->selectExecute('circular_user', $params);
		if ($result === false || !isset($result[0])) {
			$this->_db->addError();
			return false;
		}
		$replyType = $this->_request->getParameter('reply_type');
		if ($replyType == CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE || $replyType == CIRCULAR_REPLY_TYPE_RADIO_VALUE) {
			return $result[0]['reply_choice'];
		} else {
			return $result[0]['reply_body'];
		}
	}

	/**
	 * 回覧作成者追記情報を取得
	 *
	 * @return 追記情報
	 * @access public
	 */
	function getPostscript()
	{
		$param = array(
			'circular_id' => $this->_request->getParameter('circular_id')
		);
		$result = $this->_db->selectExecute('circular_postscript', $param, array('postscript_sequence'=>'ASC'));
		if ($result === false) {
			return false;
		}
		return $result;
	}

	/**
	 * お気に入りグループ一覧を取得
	 *
	 * @return mixed	(正常時：お気に入りグループ情報／異常時;false)
	 * @access public
	 */
	function getGroupMemberList()
	{
		$result = $this->_db->selectExecute('circular_group', array('user_id'=>$this->_user_id));
		if ($result === false) {
			return false;
		}
		return $result;
	}

	/**
	 * お気に入りグループ情報を取得
	 *
	 * @param $groupId グループID
	 * @param $receiveUserIdList 回覧者ユーザID一覧
	 * @return mixed	(正常時：お気に入りグループ情報／異常時;false)
	 * @access public
	 */
	function getGroupInfo($groupId=0, $receiveUserIdList=array())
	{
		if ($groupId == 0) {
			$groupId = $this->_request->getParameter('group_id');
		}

		$groupUsers = $this->_db->selectExecute('circular_group', array('group_id'=>$groupId), null, null, null, array($this, '_fetchGroupInfo'), $receiveUserIdList);
		if ($groupUsers === false) {
			return false;
		}
		$roomId = $this->_request->getParameter('room_id');
		$roomUsers = $this->getGroupUserInfo($roomId);
		if ($roomUsers === false) {
			return false;
		}
		$arr = array();
		foreach ($roomUsers as $roomUser) {
			$arr[$roomUser['user_id']] = $roomUser;
		}
		$members = array();
		foreach ($groupUsers['group_member'] as $member) {
			if (isset($arr[$member['user_id']])) {
				$members[] = $member;
			}
		}
		$groupUsers['group_member'] = $members;
		return $groupUsers;
	}

	/**
	 * グループメンバー情報のデータ配列を生成
	 *
	 * @param object $recordSet ADORecordSet
	 * @return array データ配列
	 * @access private
	 */
	function &_fetchGroupInfo(&$recordSet, $receiveUserIdList)
	{
		$group = $recordSet->fetchRow();
		$groupMembers = explode(',', $group['group_member']);
		$group['group_member'] = "";
		$i = 0;
		$flag = false;
		foreach ($groupMembers as $groupMember) {
			foreach ($receiveUserIdList as $receiveUserId) {
				if ($groupMember == $receiveUserId) {
					$flag = true;
					break;
				}
			}
			if ($flag) {
				$flag = false;
				continue;
			}
			$result = $this->_db->selectExecute('users', array('user_id'=>$groupMember));
			if ($result === false) {
				return false;
			}
			$member[$i]['user_id'] = $result[0]['user_id'];
			$member[$i]['handle'] = $result[0]['handle'];
			$group['group_member'] = $member;
			$i++;
		}
		return $group;
	}

	/**
	 * 回覧先プルダウン用ルーム情報を取得
	 *
	 * @param $treeRooms 参加ルームツリー配列
	 * @param $flatRooms 参加ルーム配列
	 * @return array 表示対象参加ルームツリー配列
	 * @access public
	 */
	function getRoomsForCircular($treeRooms, $flatRooms)
	{
		$roomId = $this->_request->getParameter('room_id');
		$currentRoom = $flatRooms[$roomId];

		$displayRooms = array();
		$displayRoomIds = array();
		if ($currentRoom['space_type'] == _SPACE_TYPE_PUBLIC
			&& $currentRoom['thread_num'] > 0) {
			$topThreadNumber = 0;
		}
		if ($currentRoom['space_type'] == _SPACE_TYPE_GROUP
			&& $currentRoom['private_flag'] == _OFF
			&& $currentRoom['thread_num'] > 1) {
			$topThreadNumber = 1;
		}

		if (isset($topThreadNumber)) {
			$displayRoomIds[] = $currentRoom['room_id'];
			$parentId = $currentRoom['parent_id'];
			do {
				$room = $flatRooms[$parentId];
				$displayRoomIds[] = $room['room_id'];
				$parentId = $room['parent_id'];
			} while ($room['thread_num'] != $topThreadNumber);

			$displayRoomIds = array_reverse($displayRoomIds);
			foreach ($displayRoomIds as $roomId) {
				$displayRoom = $flatRooms[$roomId];
				if ($displayRoom['room_id'] != $currentRoom['room_id']) {
					$displayRoom['disabled'] = true;
				}
				$displayRooms[] = $displayRoom;
			}
		} else {
			$displayRooms[] = $currentRoom;
		}

		$threadNumber = $currentRoom['thread_num'] + 1;
		$parentId = $currentRoom['room_id'];
		$childRooms = $this->_getChildRooms($treeRooms, $threadNumber, $parentId);
		$displayRooms = array_merge($displayRooms, $childRooms);

		return $displayRooms;
	}

	/**
	 * 子ルームツリー配列情報を取得
	 *
	 * @param $treeRooms 参加ルームツリー配列
	 * @param $threadNumber 対象子ルームのスレッド番号
	 * @param $parentId 対象子ルームの親ルームID
	 * @return array 子ルームツリー配列
	 * @access public
	 */
	function &_getChildRooms($treeRooms, $threadNumber, $parentId)
	{
		$childRooms = array();
		if (empty($treeRooms[$threadNumber][$parentId])) {
			return $childRooms;
		}

		foreach ($treeRooms[$threadNumber][$parentId] as $childRoom) {
			$childRooms[] = $childRoom;
			$childThreadNumber = $childRoom['thread_num'] + 1;
			$childParentId = $childRoom['room_id'];

			$grandchildRooms = $this->_getChildRooms($treeRooms, $childThreadNumber, $childParentId);
			$childRooms = array_merge($childRooms, $grandchildRooms);
		}

		return $childRooms;
	}

	/**
	 * お気に入りグループ選択プルダウン用にルーム情報を整形
	 *
	 * @param $treeRooms 参加ルームツリー配列
	 * @return array 表示対象参加ルームツリー配列
	 * @access public
	 */
	function getRoomsForFavorite($treeRooms)
	{
		list(,$displayRoom) = each($treeRooms[0][0]);
		$displayRooms[] = $displayRoom;
		$threadNumber = $displayRoom['thread_num'] + 1;
		$parentId = $displayRoom['room_id'];
		$childRooms = $this->_getChildRooms($treeRooms, $threadNumber, $parentId);
		$displayRooms = array_merge($displayRooms, $childRooms);

		list(,$displayRoom) = each($treeRooms[0][0]);
		$threadNumber = $displayRoom['thread_num'] + 1;
		$parentId = $displayRoom['room_id'];
		$childRooms = $this->_getChildRooms($treeRooms, $threadNumber, $parentId);
		$displayRooms = array_merge($displayRooms, $childRooms);

		return $displayRooms;
	}

	/**
	 * 回覧の操作/閲覧権限の有無を取得
	 *
	 * @param $circularId	回覧板ID
	 * @return bool	操作権限の有無
	 * @access	public
	 */
	function hasAuthority($circularId)
	{
		$params = array($circularId);
		$sql = "SELECT C.* ".
				"FROM {circular} C ".
				$this->_getAuthorityFromSQL().
				"WHERE C.circular_id = ? ".
				$this->_getAuthorityWhereSQL($params);
		$result = $this->_db->execute($sql, $params);
		if (empty($result)) {
			return false;
		}

		return true;
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
					"ON C.insert_user_id = PU.user_id ".
					"AND C.room_id = PU.room_id ".
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

		$sql .= "AND (A.hierarchy < ? OR C.insert_user_id = ?";
		
		$defaultEntry = $this->_session->getParameter("_default_entry_flag");
		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($defaultEntry == _ON && $hierarchy > $this->_session->getParameter("_default_entry_hierarchy")) {
			$sql .= " OR A.hierarchy IS NULL) ";
		} else {
			$sql .= ") ";
		}

		$params[] = $hierarchy;
		$params[] = $this->_session->getParameter("_user_id");

		return $sql;
	}

	/**
	 * ポータル(自分が作成者 または 回覧者の回覧を一覧表示する)画面用のデータを取得
	 *
	 * @access public
	 */
	function getPortalCircular()
	{
		$moduleId = $this->_request->getParameter('module_id');

		$fromStatement = "FROM {circular} C "
						. "LEFT JOIN {circular_user} U "
							. "ON C.circular_id = U.circular_id "
							. "AND U.receive_user_id = ? "
							. "AND U.reply_flag = ? "
						. "INNER JOIN {pages} P "
							. "ON C.room_id = P.room_id "
						. "INNER JOIN {blocks} B "
							. "ON P.page_id = B.page_id "
							. "AND B.module_id = ? "
						. "WHERE (C.post_user_id = ? " 
								. "OR U.receive_user_id = ?) " 
							. "AND (C.period = '' "
								. "OR C.period >= ? ) ";
		$sql = "SELECT MIN(B.block_id) AS block_id "
				. $fromStatement
				. "GROUP BY C.room_id";
		$params = array(
			$this->_user_id,
			CIRCULAR_LIST_TYPE_UNSEEN,
			$moduleId,
			$this->_user_id,
			$this->_user_id,
			timezone_date()
		);
		$blockIdString = $this->_db->execute($sql, $params, null, null, false, array($this, '_makeImplodeString'));
		if ($blockIdString === false) {
			$this->_db->addError();
			return false;
		}
		if (empty($blockIdString)) {
			return array();
		}

		$sql = "SELECT C.circular_id, "
					. "C.circular_subject, "
					. "C.icon_name, "
					. "C.period, "
					. "C.insert_user_name, "
					. "C.update_time, "
					. "B.block_id "
				. $fromStatement
				. "AND B.block_id IN (" . $blockIdString . ") "
				. "ORDER BY C.update_time DESC ";
		$result = $this->_db->execute($sql, $params, CIRCULAR_PORTAL_LIST_COUNT, null, true, array($this,"_callbackGetPortalCircular"));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * ADORecordSetの1カラム目を指定文字区切りの文字列にする
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	array	$glue		区切り文字
	 * @return array	指定文字区切りの文字列
	 * @access	private
	 */
	function &_makeImplodeString(&$recordSet, $glue = ',') 
	{
		$string = '';
		while ($row = $recordSet->fetchRow()) {
			$string .= $row[0]. $glue;
		}
		if (!empty($string) && !empty($glue)) {
			$string = substr($string, 0, strlen($glue) * -1);
		}

		return 	$string;	
	}

	/**
	 * ポータル用回覧情報のデータ配列を生成
	 *
	 * @return array データ配列
	 * @access	private
	 */
	function &_callbackGetPortalCircular(&$recordSet)
	{
		$ret = array();
		$format = "m/d";
		while ($row = $recordSet->fetchRow()) {
			$updateTime = timezone_date($row["update_time"], false, "YmdHis");
			$updateTime = mktime(substr($updateTime,8,2),substr($updateTime,10,2),substr($updateTime,12,2),
									substr($updateTime,4,2),substr($updateTime,6,2),substr($updateTime,0,4));

			$row["update_time"] = date($format, $updateTime);

			if (!empty($row["period"])) {
				list($row['period']) = $this->getDisplayPeriodDate($row['period'], $format);
			}
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * ページに関する設定
	 *
	 * @param array $pager ページ情報配列
	 * @param int $data_cnt データ数
	 * @param int disp_cnt 1ページ当り表示件数
	 * @param int now_page 現ページ
	 */
	function setPageInfo(&$pager, $data_cnt, $disp_cnt, $now_page = NULL)
	{
		$pager['data_cnt']	= 0;
		$pager['total_page']  = 0;
		$pager['next_link']   = FALSE;
		$pager['prev_link']   = FALSE;
		$pager['disp_begin']  = 0;
		$pager['disp_end']	= 0;
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
		// link array
		if(($pager['now_page'] - CIRCULAR_FRONT_AND_BEHIND_LINK_CNT) > 0){
			$start = $pager['now_page'] - CIRCULAR_FRONT_AND_BEHIND_LINK_CNT;
		}else{
			$start = 1;
		}
		if(($pager['now_page'] + CIRCULAR_FRONT_AND_BEHIND_LINK_CNT) >= $pager['total_page']){
			$end = $pager['total_page'];
		}else{
			$end = $pager['now_page'] + CIRCULAR_FRONT_AND_BEHIND_LINK_CNT;
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