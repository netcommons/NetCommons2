<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [iframe表示]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Iframe_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $iframeView = null;

    // 値をセットするため
    var $iframe_obj = null;
    var $scrolling = null;

    /**
     * [iframe表示]
     *
     * @access  public
     */
    function execute()
    {
		$this->iframe_obj = $this->iframeView->getIframeById($this->block_id);
		if($this->iframe_obj) {
			if($this->iframe_obj['scrollbar_show']) {
				$this->scrolling = "auto";
			} else {
				$this->scrolling = "no";
			}
			return 'success';
		} else {
			return 'nonexistent';
		}
    }
}
?>
