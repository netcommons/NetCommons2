<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * サイト共有設定追加、編集画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Share_View_Admin_Regist extends Action
{
	// リクエストパラメータを受け取るため
	var $url = null;
	var $parent_id_name = null;
	
	// コンポーネントを受け取るため
	var $sitesView = null;
	
	// 値をセットするため
	var $site = null;
	
    /**
     * 追加、編集画面表示
     *
     * @access  public
     */
    function execute()
    {
        $this->url = ($this->url == null) ? "" : $this->url;
        
        if($this->url != "") {
        	$this->site = $this->sitesView->getSitesByUrl($this->url);
        	if($this->site === false) {
        		return 'error';
        	}
        }
        
        return 'success';
    }
}
?>
