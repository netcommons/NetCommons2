<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アルバム入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Album_Entry extends Action
{
    // validatorから受け取るため
    var $photoalbum = null;
    var $album = null;
    
    // 使用コンポーネントを受け取るため
    var $fileView = null;
    var $photoalbumView = null;
    
    // 値をセットするため
    var $albumJacketSamples = array();
    var $albumNumber = array();
    
    /**
     * アルバム入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->albumJacketSamples = $this->fileView->getCurrentFiles(PHOTOALBUM_SAMPLR_JACKET_PATH);
		
		if (!empty($this->album["album_id"])) {
			return "success";
		}

		$this->albumNumber = $this->photoalbumView->getAlbumCount();
		if ($this->albumNumber === false) {
        	return "error";
        }
        $this->albumNumber++;
        
		return "success";
    }
}
?>
