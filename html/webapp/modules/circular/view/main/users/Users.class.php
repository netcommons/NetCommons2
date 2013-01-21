<?php

/**
 * 回覧先ルームユーザ表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Users extends Action
{
	// リクエストパラメータを受け取るため
	var $receive_user_ids = null;
	var $selected_room_id = null;
	var $selected_group_id = null;

	// 使用コンポーネントを受け取るため
	var $circularView = null;

	// 値をセットするため
	var $group_user_list = null;

	/**
	 * execute処理
	 *
	 * @return string アクション返り値
	 * @access  public
	 */
	function execute()
	{
		$users = explode(',', $this->receive_user_ids);
		if (intval($this->selected_room_id) == 0) {
			$result = $this->circularView->getGroupInfo($this->selected_group_id, $users);
			if ($result === false) {
				return 'error';
			}
			$this->group_user_list = $result['group_member'];
		} else {
			$result = $this->circularView->getGroupUserInfo($this->selected_room_id, $users);
			if ($result === false) {
				return 'error';
			}
			$this->group_user_list = $result;
		}

		return 'success';
	}
}
?>