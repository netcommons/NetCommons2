<?php

/**
 * 回覧作成画面表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Create extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;
	var $circular_id = null;
	var $list_type = null;
	var $reply_type = null;

	// フィルターによりセット
	var $treeRooms = null;
	var $flatRooms = null;

	// 使用コンポーネントを受け取るため
	var $circularView = null;

	// 値をセットするため
	var $circular_info = null;
	var $circular_user_list = null;
	var $room_list_array = null;
	var $group_member_list = null;
	var $choices = null;
	var $choice_count = null;
	var $group_user_list = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$receiveUserIdList = array();
		if($this->circular_id != null) {
			$circularInfo = $this->circularView->getCircularInfo();
			if ($circularInfo === false) {
				return 'error';
			}
			$this->circular_user_list = $this->circularView->getCircularUsers($this->list_type, false);
			foreach ($this->circular_user_list as $circularUser) {
				$receiveUserIdList[] = $circularUser['receive_user_id'];
			}

			if ($circularInfo['period']) {
				$circularInfo['period_flag'] = _ON;
			}
			$this->circular_info = $circularInfo;
		}

		if (isset($this->circular_info['reply_type'])
			&& ($this->circular_info['reply_type'] == CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE 
			|| $this->circular_info['reply_type'] == CIRCULAR_REPLY_TYPE_RADIO_VALUE)) {
			$choiceLists = $this->circularView->getCircularChoice();
			if ($choiceLists === false) {
				return 'error';
			}
			$choiceCount = count($choiceLists);
		} else {
			$choiceCount = CIRCULAR_REPLY_CHOICE_COUNT;
			$choiceLabel = CIRCULAR_REPLY_CHOICE_LABEL;
			$choiceLists = array();
			for ($i = 0; $i < $choiceCount; $i++) {
				if (!empty($choiceLabel)) {
					$choiceLabels = explode("|", $choiceLabel);
					$choice["choice_sequence"] = $i;
					$choice["label"] = $choiceLabels[$i % count($choiceLabels)];
				}
				$choiceLists[$i] = $choice;
			}
		}
		$this->choices = $choiceLists;
		$this->choice_count = $choiceCount;

		$this->room_list_array = $this->circularView->getRoomsForCircular($this->treeRooms, $this->flatRooms);

		$groupMemberList = $this->circularView->getGroupMemberList();
		if ($groupMemberList === false) {
			return 'error';
		}
		$this->group_member_list = $groupMemberList;

		$result = $this->circularView->getGroupUserInfo($this->room_id, $receiveUserIdList);
		if ($result === false) {
			return 'error';
		}
		$this->group_user_list = $result;

		return 'success';
	}
}
?>
