<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Edit_Import_Init extends Action
{
    // リクエストパラメータを受け取るため
    
    // バリデートによりセット
	var $mdb_obj = null;
	
	// コンポーネントを受け取るため
	
	// 値をセットするため
	
    /**
     * 汎用データベース編集画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$this->dialog_name = $this->mdb_obj["multidatabase_name"];
    	return 'success';
    }
}
?>
