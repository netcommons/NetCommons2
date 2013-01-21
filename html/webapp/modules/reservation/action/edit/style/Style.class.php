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
class Reservation_Action_Edit_Style extends Action
{
    // 使用コンポーネントを受け取るため
	var $reservationAction = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->reservationAction->setBlock();
    	if ($result === false) {
    		return 'error';
    	}
        return 'success';
    }
}
?>