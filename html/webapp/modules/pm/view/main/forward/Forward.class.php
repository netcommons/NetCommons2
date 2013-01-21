<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール転送設定画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Forward extends Action 
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $request = null;
	var $session = null;
	var $usersView = null;
	
	// 値をセットするため	
	var $current_menu = null;
	var $mail_no = null;
	var $mail_yes = null;
	var $no_email_flag = null;
	
    /**
     * メール転送設定画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->current_menu = PM_LEFTMENU_SETTING;
		$this->no_email_flag = true;
		$this->mail_no = _ON;
		$this->mail_yes = _OFF;
		
		$userId = $this->session->getParameter('_user_id');
		$params = array(
			'{users}.user_id' => $userId
		);
		$forwardUsers = $this->usersView->getSendMailUsers(null, null, null, $params);
		if (empty($forwardUsers)) {
			return 'success';
		}

		$this->no_email_flag = false;
		$forwardState = $this->pmView->getForwardState($userId);
		if ($forwardState == _ON) {
			$this->mail_no = _OFF;
			$this->mail_yes = _ON;
		}

		return 'success';
    }
}
?>