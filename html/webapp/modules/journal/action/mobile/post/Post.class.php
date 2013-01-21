<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメントの登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Mobile_Post extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $post_id = null;
	var $temporary = null;
	var $regist = null;
	var $cancel = null;
	var $content = null;

	// コンポーネントを使用するため
	var $request = null;
	var $uploadsAction = null;

	//AllowIdListのパラメータを受け取るため

	// 値をセットするため

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->post_id = intval($this->post_id);
		if (isset($this->temporary)) {
			$this->request->setParameter("temp_flag", _ON);
		}
		if ($this->post_id > 0) {
			$this->request->setParameter("edit_flag", _ON);
		} else {
			$this->request->setParameter("edit_flag", _OFF);
		}
		
    	if (isset($this->regist) || isset($this->temporary)) {

			// ファイル取り込み
			$filelist = $this->uploadsAction->uploads(_OFF, "");

			$journal_mobile_images = array();
			foreach( $filelist as $key=>$val ) {
				$journal_mobile_images[] = '?action=' . $val['action_name']
											. '&upload_id=' . $val['upload_id'];
			}
			$this->request->setParameter('journal_mobile_images', $journal_mobile_images);

			return 'regist';
    	} else {
	    	return 'cancel';
    	}
	}
}
?>
