<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム情報表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_Inf extends Action
{
	// リクエストパラメータを受け取るため
	var $edit_current_page_id = null;
	var $parent_page_id = null;
	
	// バリデートによりセット
	var $parent_page = null;
	var $page = null;
	
	// コンポーネントを使用するため
	var $db = null;
	var $monthlynumberView = null;
	
	//値をセットするため
	var $rowspan_list = null;
	var $monthly_list = null;
	var $pages_list = null;
	var $monthly_login_list = null;
	
	//var $page_id = null;
	
	var $count = 0;
	var $show_room_id = null;
	var $show_parent_id = null;
	var $show_thread_num = null;
	
    /**
     * ルーム情報表示
     *
     * @access  public
     */
    function execute()
    {
    	$this->edit_current_page_id = ($this->edit_current_page_id == null) ? 0 : intval($this->edit_current_page_id);
    	$this->parent_page_id = ($this->parent_page_id == null) ? 0 : intval($this->parent_page_id);
    	
    	// 現状、固定値
    	$this->year = null;
    	$this->month = null;
    	list($this->month, $this->monthly_list, $this->pages_list, $this->monthly_row_exists, $this->rowspan_list, $this->monthly_login_list) = $this->monthlynumberView->get($this->year, $this->month, $this->edit_current_page_id);
        $this->show_room_id = $this->edit_current_page_id;
        $this->show_parent_id = $this->page['parent_id'];
        $this->show_thread_num = $this->page['thread_num'];
        
        // 参加人数取得
        if($this->page['space_type'] == _SPACE_TYPE_PUBLIC) {
        	// パブリックスペースならばすべての会員数
        	$this->count = $this->db->countExecute("users");
			if($this->count === false) {
				return 'error';	
			}
        } else {
        	//グループルペース
        	if($this->page['default_entry_flag'] == _OFF) {
        		$this->count = $this->db->countExecute("pages_users_link", array("room_id" => $this->edit_current_page_id));
				if($this->count === false) {
					return 'error';	
				}
        	} else {
        		// 禁止者を省く
        		$ban_count = $this->db->countExecute("pages_users_link", array("room_id" => $this->edit_current_page_id, "role_authority_id" => _ROLE_AUTH_OTHER));
				if($ban_count === false) {
					return 'error';	
				}
				$all_count = $this->db->countExecute("users");
				if($all_count === false) {
					return 'error';	
				}
				$this->count = $all_count - $ban_count;
        	}
        }
    	return 'success';
    }
}
?>
