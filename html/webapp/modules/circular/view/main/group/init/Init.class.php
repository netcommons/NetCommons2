<?php

/**
 * 回覧先グループポップアップ表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Group_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;
	var $group_id = null;

	// 使用コンポーネントを受け取るため
	var $circularView = null;

	// フィルターによりセット
	var $treeRooms = null;
	
	// 値をセットするため
	var $group_info = null;
	var $room_list_array = null;
	var $group_user_list = null;
	var $group_member_list = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$this->room_list_array = $this->circularView->getRoomsForFavorite($this->treeRooms);
		if ($this->room_list_array === false) {
			return 'error';
		}
		$result = $this->circularView->getGroupUserInfo($this->room_id);
		if ($result === false) {
			return 'error';
		}
		$this->group_user_list = $result;

		if ($this->group_id) {
			$groupInfo = $this->circularView->getGroupInfo();
			if ($groupInfo === false) {
				return 'error';
			}
			$this->group_info = $groupInfo;
		}

		$groupMemberList = $this->circularView->getGroupMemberList();
		if ($groupMemberList === false) {
			return 'error';
		}
		$this->group_member_list = $groupMemberList;

		return 'success';
	}
}
?>
