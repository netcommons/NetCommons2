<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Main_Filepassword extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $metadata_id = null;
	var $insert_user_id = null;
	
	// バリデートによりセット
	var $file = null;

    // 使用コンポーネントを受け取るため
	var $filterChain = null;
 
    // 値をセットするため
	var $dialog_name = null;
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		$smartyAssign =& $this->filterChain->getFilterByName("SmartyAssign");
    	$this->dialog_name = $smartyAssign->getLang("mdb_file_password_popup_name");
    	return 'success';
    }
}
?>
