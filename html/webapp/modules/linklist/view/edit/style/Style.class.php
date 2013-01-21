<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_View_Edit_Style extends Action
{
    // 使用コンポーネントを受け取るため
    var $fileView = null;
    var $filterChain = null;

	// validatorから受け取るため
    var $linklist = null;

	// 値をセットするため
	var $lines = null;
	var $marks = null;
	var $mark_images = null;

    /**
     * 表示方法画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->lines = $this->fileView->getCurrentFiles(LINKLIST_LINE_IMAGE_DIR);
		sort($this->lines);
		
		$smartyAssign =& $this->filterChain->getFilterByName("SmartyAssign");
    	$this->marks =  array(
        	"none" => $smartyAssign->getLang("linklist_mark_none"),
        	"disc" => $smartyAssign->getLang("linklist_mark_disc"),
			"circle" => $smartyAssign->getLang("linklist_mark_circle"),
			"square" => $smartyAssign->getLang("linklist_mark_square"),
			"lower-alpha" => $smartyAssign->getLang("linklist_mark_lower_alpha"),
			"upper-alpha" => $smartyAssign->getLang("linklist_mark_upper_alpha")
		);
    	
    	$this->mark_images = $this->fileView->getCurrentFiles(LINKLIST_MARK_IMAGE_DIR);
		sort($this->mark_images);

		return "success";
    }
}
?>
