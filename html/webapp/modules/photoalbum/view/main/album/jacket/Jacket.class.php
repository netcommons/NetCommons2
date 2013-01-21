<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アルバムジャケット表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Album_Jacket extends Action
{
	// リクエストパラメータを受け取るため
	var $upload_id = null;
	var $album_jacket = null;

	// 使用コンポーネントを受け取るため
    var $session = null;
    var $photoalbumView = null; 
    
    // validatorから受け取るため
    var $album = null;
    
    /**
     * アルバムジャケット表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->upload_id)
				&& $this->upload_id != $this->session->getParameter("photoalbum_jacket_upload_id")) {
			return "error";
		}
		
		if (empty($this->upload_id)) {
			$imageSize = $this->photoalbumView->getImageSize($this->album_jacket);
		} else {
			$imageSize = $this->photoalbumView->getImageSize($this->upload_id);
			$this->album["upload_id"] = $this->upload_id;
		}
    	
		$this->album["album_jacket"] = $this->album_jacket;
		$this->album["jacket_style"] = $this->photoalbumView->getImageStyle($imageSize[0], $imageSize[1], PHOTOALBUM_JACKET_WIDTH, PHOTOALBUM_JACKET_HEIGHT);;

		return "success";
    }
}
?>
