<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページ名称変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Action_Edit_Rename extends Action
{
    // リクエストパラメータを受け取るため
    var $main_page_id = null;
    var $page_name = null;
    var $block_id = null;
    
    // 使用コンポーネントを受け取るため
    var $session = null;
    var $pagesView = null;
    var $pagesAction = null;
    var $db = null;
    
    // 値をセットするため
    var $ins_page = null;

    /**
     * ページ名称変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	$page_id = intval($this->main_page_id);
    	$page =& $this->pagesView->getPageById($page_id);
    	if($page === false || !isset($page['page_id'])) {
			return 'error';
		}
		// ルームでも変更を許すように修正
		//if($page['room_id'] == $page['page_id']) {
		//	//ルーム
		//	return 'error';
		//}
		//$this->session->setParameter("_editing_block_id", intval($this->block_id));
		
    	if($this->page_name == "") {
			$this->page_name = $page['page_name'];
    		return 'cancel';
    	} else if($page['page_name'] == $this->page_name) {
    		return 'cancel';
    	} else if(!$this->pagesAction->updPagename($page_id, $this->page_name)) {
			return 'error';
		}
		
		$permalink = $page['permalink'];
		$permalink_arr = explode('/', $permalink);
		
		$current_page_name = $permalink_arr[count($permalink_arr)-1];
		$replace_page_name = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $page['page_name']);
		$count = 1;
		if(preg_match(_PERMALINK_PROHIBITION_DIR_PATTERN, $replace_page_name)) {
			$replace_page_name = $replace_page_name ."-". $count;
		}
		if($replace_page_name == $current_page_name) {
			$result = $this->pagesAction->updPermaLink($page, $this->page_name);
			if ($result === false) {
				return 'error';
			}
		}
		
		return 'success';
    }
}
?>
