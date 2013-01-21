<?php

/**
 * 回覧メール送信処理
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Action_Main_Mail extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $circularView = null;
	var $mailMain = null;

	/**
	 * execute処理
	 *
	 * @return	string アクション文字列
	 * @access	public
	 */
	function execute()
	{
		$circularId = $this->session->getParameter(array('circular_id', $this->block_id));
		if (empty($circularId)) {
			return "success";
		}
		$mail = $this->circularView->getConfig();
		if ($mail === false) {
			return 'error';
		}

		$this->mailMain->setSubject($mail['mail_subject']);
		$this->mailMain->setBody($mail["mail_body"]);

		$circularInfo = $this->circularView->getCircularInfo($circularId);
		if ($circularInfo === false) {
			return 'error';
		}

		$tags['X-CIRCULAR_SUBJECT'] = htmlspecialchars($circularInfo['circular_subject']);
		$tags['X-CIRCULAR_BODY'] = $circularInfo['circular_body'];
		$tags['X-CIRCULAR_CREATE_DATE'] = timezone_date($circularInfo['insert_time'], false, _FULL_DATE_FORMAT);
		$tags['X-CIRCULAR_URL'] = BASE_URL. INDEX_FILE_NAME .
									'?action=' . DEFAULT_ACTION .
									'&active_action=circular_view_main_detail' .
									'&circular_id=' . $circularId .
									'&block_id=' . $this->block_id .
									'#'. $this->block_id;
		$this->mailMain->assign($tags);

		$toUsers = $this->circularView->getToUsersInfo($circularId);
		$this->mailMain->setToUsers($toUsers);
		$this->mailMain->send();

		return 'success';
	}
}
?>
