<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 写真フッター表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Photo_Footer extends Action
{
    // 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    
    // validatorから受け取るため
	var $album = null;
	var $photo = null;
	
	// 値をセットするため
    var $commentCount = null;
    
    /**
     * 写真フッター表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->commentCount = $this->photoalbumView->getCommentCount();
    	if ($this->commentCount === false) {
			return "error";
		}

		return "success";
    }
}
?>
