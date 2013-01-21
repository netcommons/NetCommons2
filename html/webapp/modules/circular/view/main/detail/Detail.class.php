<?php

/**
 * 回覧詳細画面表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Detail extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $circularAction = null;
	var $circularCommon = null;
	var $circularView = null;

	// 値をセットするため
	var $circular_id = null;
	var $period_class_name = null;
	var $circular_info = null;
	var $circular_users = null;
	var $count = null;
	var $postscripts = null;
	var $now_page = null;
	var $has_auth = null;
	var $has_create_auth = null;
	var $visible_row = null;
	var $visible_row_map = null;

	//ページ
    var $pager = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$circularInfo = $this->circularView->getCircularInfo();
		$this->circular_info = $circularInfo;

		$result = $this->circularAction->updateUserSeen('visit');
		if ($result === false) {
			return 'error';
		}

		if ($this->visible_row === null) {
			$this->visible_row = CIRCULAR_DEFAULT_VISIBLE_ROW;
		}

		$whereParams = array(
			'room_id'=>$this->room_id,
			'circular_id'=>$this->circular_id
		);
		$this->count = $this->db->countExecute('circular_user', $whereParams);
		$this->circularView->setPageInfo($this->pager, $this->count, $this->visible_row, $this->now_page);

		$circularUser = $this->circularView->getCircularUsers($circularInfo['reply_type'], true, $this->visible_row, $this->pager['disp_begin']);
		if ($circularUser === false) {
			return 'error';
		}
		$this->circular_users = $circularUser;

		$config = $this->circularView->getConfig();
		if ($config === false) {
			return 'error';
		}
		$authId = $this->session->getParameter('_auth_id');
		if ($authId >= $config['create_authority']) {
			$this->has_create_auth = _ON;
		} else {
			$this->has_create_auth = _OFF;
		}
		$this->has_auth = $this->circularView->hasAuthority($this->circular_id);

		$this->visible_row_map =& $this->circularCommon->getMap(CIRCULAR_VISIBLE_ROW);

		return 'success';
	}
}
?>
