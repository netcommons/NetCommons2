<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページ表示順変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Action_Edit_Chgseq extends Action
{
    // リクエストパラメータを受け取るため
    var $drag_page_id = null;
    var $drop_page_id = null;
    var $position = null;
    var $move_page = null;
    var $page = null;
    
    // 使用コンポーネントを受け取るため
    //var $session = null;
    var $actionChain = null;
    var $pagesView = null;
    var $pagesAction = null;
    var $db = null;
    var $session = null;
    
    /**
     * ページ表示順変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	$page =& $this->page;
    	$move_page =& $this->move_page;
    	$lang_dirname = '';
    	if($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['display_position'] == _DISPLAY_POSITION_CENTER && $page['thread_num'] != 0) {
    		$lang_dirname = $this->session->getParameter('_lang');
    	}
    	
    	if($this->position == "inside") {
			$display_sequence = $this->pagesView->getMaxChildPage($page['page_id'], $lang_dirname) + 1;
			$thread_num = $page['thread_num']+1;
			if(!$this->pagesAction->updDisplaySequence($move_page['page_id'], $page['page_id'], $thread_num, $display_sequence)) {
				return 'error';
			}
			
		} else if($this->position == "top") {
			//移動先インクリメント処理
			if(!$this->pagesAction->incrementDisplaySeq($page['parent_id'], $page['display_sequence'], $lang_dirname)) {
				return 'error';
			}
			
			if($page['display_sequence'] == 0) {
				$display_sequence = $page['display_sequence'] - 1;
			} else {
				$display_sequence = $page['display_sequence'];	
			}
			$thread_num = $page['thread_num'];
			if(!$this->pagesAction->updDisplaySequence($move_page['page_id'], $page['parent_id'], $page['thread_num'], $display_sequence)) {
				return 'error';
			}
		} else {
			//移動先インクリメント処理
			if(!$this->pagesAction->incrementDisplaySeq($page['parent_id'], $page['display_sequence']+1, $lang_dirname)) {
				return 'error';
			}
			$display_sequence = $page['display_sequence'] + 1;
			$thread_num = $page['thread_num'];
			if(!$this->pagesAction->updDisplaySequence($move_page['page_id'], $page['parent_id'], $thread_num, $display_sequence)) {
				return 'error';
			}
		}
		//プライベートスペースの移動は、すべてのトッププライベートスペースを対象とする
		if($move_page['space_type'] == _SPACE_TYPE_GROUP && $move_page['private_flag'] == _ON &&
			$move_page['thread_num'] == 0) {
			if(!$this->pagesAction->updPrivateDisplaySeq($display_sequence)) {
				return 'error';
			}	
		}
		
		//移動元前詰め処理
		if(!$this->pagesAction->decrementDisplaySeq($move_page['parent_id'], $move_page['display_sequence']+1, $lang_dirname)) {
			return 'error';	
		}
		//移動元がカテゴリならば、深さ更新
		if($move_page['node_flag']) {
			if(!$this->_updThreadNum($move_page['page_id'], $thread_num+1)) {
				return 'error';
			}
		}
		
		// 固定リンク修正
		$move_page =& $this->pagesView->getPageById($this->drag_page_id);	// 再取得
		if($move_page === false || !isset($move_page['page_id'])) {
			return 'error';
		}
		$parent_page =& $this->pagesView->getPageById($move_page['parent_id']);
		if($parent_page === false || !isset($parent_page['page_id'])) {
			return 'error';
		}
		// パブリックスペースでトップページならばpages_meta_infテーブルのtitleを削除
		// permalinkを空にする。
		if($move_page['space_type'] == _SPACE_TYPE_PUBLIC && $move_page['thread_num'] == 1 &&
			$move_page['display_sequence'] == 1) {
			// 現在トップページの固定リンクを元に戻す
			$result = $this->pagesAction->updPermaLink($page, $page['page_name']);
			if ($result === false) {
				return 'error';
			}
			
			$update_permalink = "";
			$result = $this->db->updateExecute("pages_meta_inf", array("title" => null), array("page_id" => $move_page['page_id']));
			if($result === false) return 'error';
		} else {
			$permalink_arr = explode('/', $move_page['permalink']);
			$update_permalink = $permalink_arr[count($permalink_arr)-1];
			if($update_permalink == "") {
				$update_permalink = $move_page['page_name'];
			}
		}
		
		$result = $this->pagesAction->updPermaLink($move_page, $update_permalink, $parent_page);
		if ($result === false) {
			return 'error';
		}
		
    	return 'success';
    }
     /**
     * 深さ更新処理アクション
     *
     * @access  public
     */
    function _updThreadNum($parent_id, $thread_num) {
    	if(!$this->pagesAction->updThreadNum($parent_id, $thread_num)) {
			return false;	
		}
		$params = array(
			"parent_id" => $parent_id,
			"node_flag" => _ON
		);
		$child_pages =& $this->pagesView->getPages($params);
		if(isset($child_pages[0])) {
			foreach($child_pages as $child_page) {
				if($child_page['node_flag']) {
					if(!$this->_updThreadNum($child_page['page_id'], $thread_num+1)) {
						return false;
					}
				}
			}	
		}
		return true;
    }
}
?>
