<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメントの削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Mobile_Comment_Delete extends Action
{
	// リクエストパラメータを受け取るため

	// コンポーネントを使用するため
	var $request = null;

	//AllowIdListのパラメータを受け取るため

	// 値をセットするため

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->request->setParameter("comment_flag", _ON);
		return 'success';
	}
}
?>
