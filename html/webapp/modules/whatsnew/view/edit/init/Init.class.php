<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_View_Edit_Init extends Action
{
    // パラメータを受け取るため
	var $block_id = null;

    // 使用コンポーネントを受け取るため
	var $whatsnewView = null;
	var $session = null;

    // 値をセットするため
	var $whatsnew_obj = null;
	var $modules = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->session->removeParameter(array("whatsnew", "not_enroll_room", $this->block_id));
		$this->session->removeParameter(array("whatsnew", "enroll_room", $this->block_id));
		$this->session->removeParameter(array("whatsnew", "myroom_flag", $this->block_id));

    	$this->whatsnew_obj = $this->whatsnewView->getBlock($this->block_id);
    	if (!$this->whatsnew_obj) {
    		return 'error';
    	}
		$this->modules =& $this->whatsnewView->getModules("module_id");
		if ($this->modules === false) {
        	return 'error';
        }
        $this->session->setParameter(array("whatsnew", "enroll_room_arr", $this->block_id), $this->whatsnew_obj["select_room_list"]);
        
		return 'success';
    }
}
?>
