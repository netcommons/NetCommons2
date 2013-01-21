<?php

/**
 * 回覧先グループ登録/更新処理
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Action_Main_Group_Entry extends Action
{
	// リクエストパラメータを受け取るため
	var $group_name = null;

	// 使用コンポーネントを受け取るため
	var $circularAction = null;

	// 値をセットするため
	var $group_id = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$result = $this->circularAction->entryGroupMember();
		if ($result === false) {
			return 'error';
		}
		$this->group_id = $result;

		return 'success';
	}
}
?>
