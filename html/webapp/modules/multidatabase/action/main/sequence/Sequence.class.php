<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク順序変更アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Sequence extends Action
{
    // 使用コンポーネントを受け取るため
    var $mdbAction = null;

    /**
     * コンテンツ順序変更アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->mdbAction->updateContentSequence()) {
			return 'error';
		}
		
		return 'success';
    }
}
?>