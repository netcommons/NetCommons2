<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * スライドショーアクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Slideshow extends Action
{
    // 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    var $request = null;
    
    // validatorから受け取るため
	var $photoalbum = null;
	var $album = null;
	
	// 値をセットするため
    var $photos = null;
    
    /**
     * スライドショーアクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->request->setParameter("_header", _OFF);
    	$this->request->setParameter("_noscript", _OFF);
		
    	$this->photos = $this->photoalbumView->getPhotos();
    	if ($this->photos === false) {
			return "error";
		}

		return "success";
    }
}
?>
