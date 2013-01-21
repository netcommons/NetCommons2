<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 入力データ削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Action_Edit_Data_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $data_id = null;

    // 使用コンポーネントを受け取るため
    var $registrationAction = null;

    /**
     * 入力データ削除アクション
     *
     * @access  public
     */
    function execute()
    {
        if (!$this->registrationAction->deleteData()) {
        	return "error";
        }

		if (empty($this->data_id)) {
			return "all";
		}
		
		return "one";
    }
}
?>
