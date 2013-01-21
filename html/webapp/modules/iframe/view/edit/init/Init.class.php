<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * iframe編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Iframe_View_Edit_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $iframeView = null;

    // 値をセットするため
    var $iframe_obj = null;
    /**

     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		$this->iframe_obj = $this->iframeView->getIframeById($this->block_id);
		if(!$this->iframe_obj) {
			$this->iframe_obj['url'] = "http://";
			$this->iframe_obj['frame_width'] = 600;
			$this->iframe_obj['frame_height'] = 400;
			$this->iframe_obj['scrollbar_show'] = 1;
			$this->iframe_obj['scrollframe_show'] = 0;
		}
		return 'success';
    }
}
?>
