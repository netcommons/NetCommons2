<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報セッション登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Regist_Users extends Action
{
	// リクエストパラメータを受け取るため
	var $edit_current_page_id = null;
	var $room_authority = null;
	var $room_createroom_flag = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->edit_current_page_id = ($this->edit_current_page_id == null) ? 0 : intval($this->edit_current_page_id);
    	
    	//$confirm_flag = $this->session->getParameter(array("room", $this->edit_current_page_id,"confirm_flag"));
		//if($confirm_flag) {
		//	// session削除
		//	$this->session->removeParameter(array("room", $this->edit_current_page_id,"room_authority"));
		//	$this->session->removeParameter(array("room", $this->edit_current_page_id,"room_createroom_flag"));
		//	$this->session->removeParameter(array("room", $this->edit_current_page_id,"confirm_flag"));
		//}
		foreach($this->room_authority as $user_id => $room_authority_id) {
    		$this->session->setParameter(array("room", $this->edit_current_page_id,"room_authority",$user_id), intval($room_authority_id));
			$createroom_flag = _ON;
			if(!isset($this->room_createroom_flag[$user_id]) || $this->room_createroom_flag[$user_id] == null) {
				$createroom_flag = _OFF;
			}
			$this->session->setParameter(array("room", $this->edit_current_page_id,"room_createroom_flag",$user_id), $createroom_flag);
    	}
		return 'success';
	}
}
?>