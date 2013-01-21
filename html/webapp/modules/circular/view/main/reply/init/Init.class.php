<?php

/**
 * 回覧回答ポップアップ表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Reply_Init extends Action
{
	// 使用コンポーネントを受け取るため
	var $circularView = null;

	// 値をセットするため
	var $circular_id = null;
	var $circular_info = null;
	var $reply = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$circularInfo = $this->circularView->getCircularInfo();
		if ($circularInfo === false) {
			return 'error';
		}
		$this->circular_info = $circularInfo; 
		$reply = $this->circularView->getReplyComment();
		if($reply === false) {
			return 'error';
		}

		if ($circularInfo['reply_type'] == CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE || $circularInfo['reply_type'] == CIRCULAR_REPLY_TYPE_RADIO_VALUE) {
			$choices = $this->circularView->getCircularChoice();
			if ($choices === false) {
				$this->_db->addError();
				return false;
			}
			$this->choices = $choices;

			$reply = explode(" , ", $reply);
		}

		$this->reply = $reply;

		return 'success';
	}
}
?>
