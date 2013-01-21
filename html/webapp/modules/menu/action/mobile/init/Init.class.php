<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メニューの表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Action_Mobile_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $room_id = null;
	var $module_id = null;

	// コンポーネントを使用するため
	var $session = null;

	// 値をセットするため
	
    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->session->setParameter("_mobile_page_id", $this->page_id);
		$this->session->setParameter("_mobile_room_id", $this->room_id);
		$this->session->setParameter("_mobile_module_id", $this->module_id);
		return 'success';
    }
}
?>
