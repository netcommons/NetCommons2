<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム選択画面表示
 *
 * @package     NetCommons
 * @author      Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2010 AllCreator Co., Ltd.
 * @project     NC Support Project, provided by AllCreator Co., Ltd.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */

 class Mobile_Action_Admin_Selectroom extends Action
{
    // パラメータを受け取るため
	var $not_enroll_room = null;
	var $enroll_room = null;
	var $myroom_flag = null;

	// 使用コンポーネントを受け取るため
	var $session = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->session->setParameter(array('mobile', 'mobile_whatsnew_enroll_room'), $this->enroll_room);
		$this->session->setParameter(array('mobile', 'mobile_whatsnew_select_myroom'), intval($this->myroom_flag));

		return 'success';
	}
}
?>