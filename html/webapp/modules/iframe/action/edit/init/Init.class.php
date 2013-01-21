<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * iframe登録処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Iframe_Action_Edit_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $url = null;
    var $frame_width = null;
    var $frame_height = null;
    var $scrollbar_show = null;
    var $scrollframe_show = null;

    // 使用コンポーネントを受け取るため
    var $iframeView = null;
    var $iframeAction = null;
     var $request = null;

    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		$params = array(
			"block_id" =>$this->block_id,
			"url" =>$this->url,
			"frame_width" =>$this->frame_width,
			"frame_height" =>$this->frame_height,
			"scrollbar_show" =>$this->scrollbar_show,
			"scrollframe_show" => $this->scrollframe_show
		);
		
		$iframe_obj = $this->iframeView->getIframeById($this->block_id);
		if($iframe_obj) {
			//update
			if($this->iframeAction->updIframe($params)) {
				// 初期化
				$this->request->removeParameters();
				return 'success';
			} else {
				return 'error';
			}
		} else {
			//insert
			if($this->iframeAction->insIframe($params)) {
				// 初期化
				$this->request->removeParameters();
				return 'success';
			} else {
				return 'error';
			}
		}
    }
}
?>
