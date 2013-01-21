<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページ削除アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Action_Edit_Deletepage extends Action
{
    // リクエストパラメータを受け取るため
    var $main_page_id = null;
    var $block_id = null;
    
    // 使用コンポーネントを受け取るため
    var $session = null;
    var $pagesView = null;
    var $pagesAction = null;
    var $blocksAction = null;
    var $menuAction = null;
    var $blocksView = null;
    
    // 値をセットするため
    var $url = null;
    
    var $del_page_arr = array();
    
    /**
     * ページ追加アクション
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
		if($page['thread_num'] == 0 || $page['room_id'] == $page['page_id']) {
			//深さが0 または、ルーム
			return 'error';
		}
		//ページ削除
		if(!$this->pagesAction->delPageById($page_id)) {
			return 'error';
		}
		//ページスタイル削除
		if(!$this->pagesAction->delPageStyleById($page_id)) {
			return 'error';
		}
		
		//pages_meta_infテーブル削除
		if(!$this->pagesAction->delPageMetaInfById($page_id)) {
			return 'error';
		}
		
		//表示順decrement
		if(!$this->pagesAction->decrementDisplaySeq($page['parent_id'], $page['display_sequence'], $page['lang_dirname'])) {
			return 'error';
		}
		//削除関数を呼び出し
		$blocks =& $this->blocksView->getBlockByPageId($page_id);
		if(isset($blocks[0])) {
			foreach($blocks as $block) {
				$this->blocksAction->delFuncExec($block['block_id']);
			}
		}
		
		//ブロックテーブル削除
		if(!$this->blocksAction->delBlockByPageId($page_id)) {
			return 'error';
		}
		//メニュー詳細テーブル削除
		if(!$this->menuAction->delMenuDetailByPageId($page_id)) {
			return 'error';
		}
		//携帯メニュー詳細テーブル削除
		if(!$this->menuAction->delMobileMenuDetailByPageId($page_id)) {
			return 'error';
		}

		$this->del_page_arr[] = $page_id;
		if($page['node_flag']) {
			//子供を再帰的に削除
			if(!$this->_delPageChildren($page['page_id'])) {
				return 'error';
			}
		}
		if(in_array($this->session->getParameter("_main_page_id"), $this->del_page_arr)) {
			$this->session->setParameter("_editing_block_id", intval($this->block_id));
		
			//再表示URL
			$this->url = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION."&page_id=".$page['room_id'];
		} else {
			$this->url = "true";
		}
		return 'success';
    }
    
    function _delPageChildren($parent_id) {
    	$ret = true;
    	$pages = $this->pagesView->getPages(array("parent_id"=>$parent_id));
    	if(isset($pages[0])) {
	    	foreach($pages as $page) {
	    		if($page['node_flag']) {
					//子供を再帰的に削除
					$this->_delPageChildren($page['page_id']);
	    		}
	    		$this->del_page_arr[] = $page['page_id'];
	    		if(!$this->pagesAction->delPageById($page['page_id'])) {
					$ret = false;
				}
				//ページスタイル削除
				if(!$this->pagesAction->delPageStyleById($page['page_id'])) {
					$ret = false;
				}
				//pages_meta_infテーブル削除
				if(!$this->pagesAction->delPageMetaInfById($page['page_id'])) {
					$ret = false;
				}
				//削除関数を呼び出し
				$blocks =& $this->blocksView->getBlockByPageId($page['page_id']);
				if(isset($blocks[0])) {
					foreach($blocks as $block) {
						$this->blocksAction->delFuncExec($block['block_id']);
					}
				}
				if(!$this->blocksAction->delBlockByPageId($page['page_id'])) {
					$ret = false;
				}
				if(!$this->menuAction->delMenuDetailByPageId($page['page_id'])) {
					$ret = false;
				}
	    	}
    	}
    	return $ret;
    }
}
?>
