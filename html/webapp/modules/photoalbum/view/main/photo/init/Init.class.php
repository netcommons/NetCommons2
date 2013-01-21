<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 写真画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Photo_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;
    
    // 使用コンポーネントを受け取るため
    var $photoalbumView = null;
	var $configView = null;
    
    // validatorから受け取るため
	var $photoalbum = null;
	var $album = null;
	
	// 値をセットするため
    var $photos = null;
    var $minWidth = null;
    var $dialog_name = null;
    
    /**
     * 写真画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->photos = $this->photoalbumView->getPhotos();
    	if ($this->photos === false) {
			return "error";
		}
		
		if ($this->photoalbum["size_flag"] != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "width");
			if ($config === false) {
	        	return "error";
	        }
	        
	        $this->minWidth = $config["conf_value"];
		}

		$this->dialog_name = $this->album["album_name"];
		
		return "success";
    }
}
?>