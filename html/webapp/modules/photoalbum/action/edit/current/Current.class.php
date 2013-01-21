<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレントフォトアルバム更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Edit_Current extends Action
{
    // 使用コンポーネントを受け取るため
    var $photoalbumAction = null;

    /**
     * カレントフォトアルバム更新アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->photoalbumAction->setBlock()) {
        	return "error";
        }

		return "success";
    }
}
?>
