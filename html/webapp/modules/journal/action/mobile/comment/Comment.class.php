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
class Journal_Action_Mobile_Comment extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $post_id = null;
	var $regist = null;
	var $cancel = null;

	// コンポーネントを使用するため
	var $request = null;

	//AllowIdListのパラメータを受け取るため

	// 値をセットするため
	var $comment_flag = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if (isset($this->regist)) {
	    	return 'regist';
    	} else {
			$this->comment_flag = _ON;
	    	return 'cancel';
    	}
	}
}
?>
