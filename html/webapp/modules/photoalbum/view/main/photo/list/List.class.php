<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 写真一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Photo_List extends Action
{
    // リクエストパラメータを受け取るため
    var $sort = null;

    // 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    var $filterChain = null;
 
    // validatorから受け取るため
	var $photoalbum = null;
	var $album = null;

    // 値をセットするため
	var $photos = null;
	var $dialog_name = null;
    
    /**
     * 写真一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->photos = $this->photoalbumView->getPhotos();
    	if ($this->photos === false) {
			return "error";
		}
		
    	$smartyAssign =& $this->filterChain->getFilterByName("SmartyAssign");
		$this->dialog_name = sprintf($smartyAssign->getLang("photoalbum_photo_list_head"), $this->album["album_name"]);

		if (empty($this->sort)) {
			$this->sort = PHOTOALBUM_PHOTO_SORT_NONE;
		}
		
		return "success";
    }
}
?>