<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Main_Comment extends Action
{
    // 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    
    // validatorから受け取るため
	var $album = null;
	var $photo = null;
	
	// 値をセットするため
    var $comments = null;
    var $commentCount = null;
    
    /**
     * コメント表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->comments = $this->photoalbumView->getComments();
    	if ($this->comments === false) {
			return "error";
		}
		$this->commentCount = count($this->comments);
		
		return "success";
    }
}
?>
