<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

include_once MAPLE_DIR.'/includes/pear/File/Archive.php';

/**
 * 圧縮アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Action_Main_Compress extends Action
{
	// 使用コンポーネントを受け取るため
 	var $cabinetAction = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->cabinetAction->compressFile();
    	if ($result === false) {
    		return 'error';
    	}
    	return 'success';
    }
}
?>