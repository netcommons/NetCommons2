<?php

/**
 * プレフィックスマネージャー
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Security_View_Main_Prefixmanager extends Action
{
	// リクエストパラメータを受け取るため
	
	// 使用コンポーネントを受け取るため
    var $db = null;
    var $SearchTables = null;
    var $actionChain = null;
    
    
    // フィルタによりセット
    
    // 値をセットするため
	var $dbtables = null;
	var $useprefix = null;
	var $errorList = null;

    /**
     * DBテーブル名リストを取得
     *
     * @access  public
     */
	function execute()
	{
	    $this->errorList =& $this->actionChain->getCurErrorList();
	
        // DBテーブル名リスト
		$this->dbtables = $this->SearchTables->SearchTables();
		if ($this->dbtables == false) {
			$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_DISPLAYTABLE, "search table"));
			return 'error';
		}
		
		// 使用中のプレフィックス
		$this->useprefix = $this->db->getPrefix();

		return 'success';
	}
}
?>
