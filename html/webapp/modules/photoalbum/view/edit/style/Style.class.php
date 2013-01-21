<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Edit_Style extends Action
{
    // 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    var $enteredFlag = null;

	// validatorから受け取るため
    var $photoalbum = null;

    // 値をセットするため
	var $albums = null;
	
    /**
     * 表示方法画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->albums = $this->photoalbumView->getAlbums();
		if ($this->albums === false) {
        	return "error";
        }
    	
    	if ($this->enteredFlag == _ON) {
    		$this->photoalbum["display"] = PHOTOALBUM_DISPLAY_SLIDE;
    		$this->photoalbum["display_album_id"] = $this->albums[0]["album_id"];
    	}
    	
    	return "success";
    }
}
?>
