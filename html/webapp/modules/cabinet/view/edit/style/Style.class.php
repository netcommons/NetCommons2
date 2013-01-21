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
class Cabinet_View_Edit_Style extends Action
{
	// validatorから受け取るため
    var $cabinet = null;

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
