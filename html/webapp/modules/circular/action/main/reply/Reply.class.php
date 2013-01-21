<?php

/**
 * 回覧回答処理
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Action_Main_Reply extends Action
{
	// リクエストパラメータを受け取るため
	var $circular_id = null;
	var $reply_body = null;

	// 使用コンポーネントを受け取るため
	var $circularAction = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$result = $this->circularAction->replyCircular();
		if ($result === false) {
			return 'error';
		}
		return 'success';
	}
}
?>