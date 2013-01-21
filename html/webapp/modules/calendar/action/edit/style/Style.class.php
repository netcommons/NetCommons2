<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
	var $select_room = null;

    // 使用コンポーネントを受け取るため
	var $calendarAction = null;
	var $session = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if ($this->select_room == _ON) {
	    	$myroom_flag = $this->session->getParameter(array("calendar", "myroom_flag", $this->block_id));
    	} else {
    		$myroom_flag = _OFF;
    	}

		$result = $this->calendarAction->setBlock();
    	if ($result === false) {
    		return 'error';
    	}

		if (isset($myroom_flag)) {
			$result = $this->calendarAction->setSelectRoom();
	    	if ($result === false) {
				return 'error';
			}
		}

        return 'success';
    }
}
?>