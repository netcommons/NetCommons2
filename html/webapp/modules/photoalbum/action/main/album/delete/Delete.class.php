<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アルバム削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Main_Album_Delete extends Action
{
    // 使用コンポーネントを受け取るため
	var $photoalbumAction = null;

    /**
     * アルバム削除アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->photoalbumAction->deleteAlbum()) {
	        return "error";
        }

    	return "success";
    }
}
?>
