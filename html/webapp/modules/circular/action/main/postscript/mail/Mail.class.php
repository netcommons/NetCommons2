<?php

/**
 * 追記メール送信処理
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Action_Main_Postscript_Mail extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $circular_id = null;
	var $mail_subject = null;
	var $mail_body = null;

	// 使用コンポーネントを受け取るため
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
		if (empty($this->circular_id)) {
			return "success";
		}

		$this->mailMain->setSubject($this->mail_subject);
		$this->mailMain->setBody($this->mail_body);

		$circularInfo = $this->circularView->getCircularInfo($this->circular_id);
		if ($circularInfo === false) {
			return 'error';
		}
		$postscripts = $this->circularView->getPostscript();
		if ($postscripts === false) {
			return 'error';
		}
		$sendPostscript = $postscripts[count($postscripts)-1];

		$tags['X-CIRCULAR_SUBJECT'] = htmlspecialchars($circularInfo['circular_subject']);
		$tags['X-CIRCULAR_BODY'] = $circularInfo['circular_body'];
		$tags['X-POSTSCRIPT_BODY'] = $sendPostscript['postscript_value'];
		$tags['X-POSTSCRIPT_DATE'] = timezone_date($sendPostscript['insert_time'], false, _FULL_DATE_FORMAT);
		$tags['X-CIRCULAR_URL'] = BASE_URL. INDEX_FILE_NAME .
									'?action=' . DEFAULT_ACTION .
									'&active_action=circular_view_main_detail' .
									'&circular_id=' . $this->circular_id .
									'&block_id=' . $this->block_id .
									'#'. $this->block_id;
		$this->mailMain->assign($tags);

		$toUsers = $this->circularView->getToUsersInfo($this->circular_id);
		$this->mailMain->setToUsers($toUsers);
		$this->mailMain->send();

		return 'success';
	}
}
?>
