<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Edit_Entry extends Action
{
	// リクエストパラメータを受け取るため
	var $photoalbum_id = null;

    // 使用コンポーネントを受け取るため
    var $photoalbumAction = null;

    /**
     * フォトアルバム登録アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->photoalbumAction->setPhotoalbum()) {
        	return "error";
        }

		if (empty($this->photoalbum_id)) {
			return "create";
		}
		
		return "modify";
    }
}
?>