<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 写真アップロード画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Photo_Upload extends Action
{
	// 使用コンポーネントを受け取るため
	var $filterChain = null;

    // validatorから受け取るため
	var $album = null;
	
	// 値をセットするため
	var $dialog_name = null;

    /**
     * 写真アップロード画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$smartyAssign =& $this->filterChain->getFilterByName("SmartyAssign");
		$this->dialog_name = sprintf($smartyAssign->getLang("photoalbum_photo_upload_popup_name"), $this->album["album_name"]);

		return "success";
    }
}
?>
