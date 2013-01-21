<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 写真削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Main_Photo_Delete extends Action
{
	// コンポーネントを受け取るため
	var $photoalbumAction = null;

    /**
     * 写真削除アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->photoalbumAction->deletePhoto()) {
        	return "error";
        }

        return "success";
    }
}
?>