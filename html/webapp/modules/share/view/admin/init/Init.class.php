<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * サイト共有設定初期画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Share_View_Admin_Init extends Action
{
	// コンポーネントを受け取るため
	var $sitesView = null;
	
	// 値をセットするため
	var $sites = null;
	var $maxNum = null;
	
    /**
     * 初期画面表示
     *
     * @access  public
     */
    function execute()
    {
        $this->sites = $this->sitesView->getSites();
        $this->maxNum = count($this->sites);
        
        return 'success';
    }
}
?>
