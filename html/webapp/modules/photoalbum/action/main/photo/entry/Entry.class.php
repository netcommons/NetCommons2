<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 写真登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Main_Photo_Entry extends Action
{
	// コンポーネントを受け取るため
	var $photoalbumAction = null;

    /**
     * 写真登録アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->photoalbumAction->setPhoto()) {
        	return "error";
        }

        return "success";
    }
}
?>
