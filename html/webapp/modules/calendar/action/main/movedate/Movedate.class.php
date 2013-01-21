<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日付移動の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Main_Movedate extends Action
{
    // リクエストパラメータを受け取るため
	var $date = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
        return 'success';
    }
}
?>
