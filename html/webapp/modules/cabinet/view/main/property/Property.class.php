<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * プロパティを表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Property extends Action
{
    // validatorから受け取るため
    var $cabinet = null;
    var $file = null;

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
