<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 投票アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Main_Photo_Vote extends Action
{
    // 使用コンポーネントを受け取るため
    var $photoalbumAction = null;

	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $album_id = null;
	var $photo_id = null;
	var $seq = null;

    /**
     * 投票アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->photoalbumAction->vote()) {
        	return "error";
        }

        return "success";
    }
}
?>
