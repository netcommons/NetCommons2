<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルアップロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Add_File extends Action
{
    // リクエストパラメータを受け取るため
    var $cabinet_id = null;
    var $folder_id = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		return 'success';
    }
}
?>