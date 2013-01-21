<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 言語モジュール
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Language_View_Edit_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $lang = null;
	
	// バリデートによりセット
	var $lang_obj = null;

    // 使用コンポーネントを受け取るため
    var $languageView = null;

    // 値をセットするため
    var $lang_list = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->lang_list = $this->languageView->getDisplayLanguage();
    	return 'success';
    }
}
?>