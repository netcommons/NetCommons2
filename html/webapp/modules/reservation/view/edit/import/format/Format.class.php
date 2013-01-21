<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツのフォーマットアクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Edit_Import_Format extends Action
{
    // 使用コンポーネントを受け取るため
    var $csvMain = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$data = explode("|", RESERVATION_IMPORT_FORMAT);
		$this->csvMain->add($data);
		$this->csvMain->download(RESERVATION_IMPORT_FILENAME);

        return "success";
    }
}
?>
