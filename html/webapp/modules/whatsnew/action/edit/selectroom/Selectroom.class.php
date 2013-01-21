<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 参加ルーム選択画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Action_Edit_Selectroom extends Action
{
    // パラメータを受け取るため
	var $block_id = null;
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
		$this->session->setParameter(array("whatsnew", "not_enroll_room", $this->block_id), $this->not_enroll_room);
		$this->session->setParameter(array("whatsnew", "enroll_room", $this->block_id), $this->enroll_room);

		$this->session->setParameter(array("whatsnew", "myroom_flag", $this->block_id), intval($this->myroom_flag));

		return 'success';
	}
}
?>