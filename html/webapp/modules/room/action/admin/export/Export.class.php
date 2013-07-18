<?php

/**
 * ルーム管理>>ルーム一覧>>参加者修正>>エクスポート>>準備
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Room_Action_Admin_Export extends Action
{
	// リクエストパラメータをセットするため
	var $parent_page_id = null;
	var $edit_current_page_id = null;

	// VAlidatorでセット
	var $page = null;
	
	// 使用コンポーネントを受け取るため
    var $session = null;
    var $db = null;
    var $actionChain = null;

	
    // 値をセットするため
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->session->setParameter(array('room', 'export', 'current_page_id'), $this->edit_current_page_id);
		$this->session->setParameter(array('room', 'export', 'current_page_name'), $this->page['page_name']);

		$select_str   =& $this->session->getParameter(array('room', $this->edit_current_page_id, 'selected_select_str'));
		$from_str     =& $this->session->getParameter(array('room', $this->edit_current_page_id, 'selected_from_str'));
		$add_from_str =& $this->session->getParameter(array('room', $this->edit_current_page_id, 'selected_add_from_str'));
		$where_str    =& $this->session->getParameter(array('room', $this->edit_current_page_id, 'selected_where_str'));
		$add_where_str =& $this->session->getParameter(array('room', $this->edit_current_page_id,'selected_add_where_str'));
		$export_add_where_str = ' AND {authorities}.user_authority_id != ?';
		$params       =& $this->session->getParameter(array('room', $this->edit_current_page_id, 'selected_params'));
		$from_params  =& $this->session->getParameter(array('room', $this->edit_current_page_id, 'selected_from_params'));
		$where_params =& $this->session->getParameter(array('room', $this->edit_current_page_id, 'selected_where_params'));
		if(!is_array($from_params)) $from_params = array();
		if(!is_array($where_params)) $where_params = array();

		$export_where_params = array(_AUTH_ADMIN);
		
    	$sql_params = array_merge((array)$params, (array)$from_params, (array)$where_params, (array)$export_where_params);

		$sql = $select_str.$from_str.$add_from_str.$where_str.$add_where_str.$export_add_where_str;

		//
		// レコード数取得
		//
		$count_sql = 'SELECT COUNT(*) FROM (' . $select_str.$from_str.$add_from_str.$where_str.$add_where_str.$export_add_where_str . ') AS T';
		$count_result =& $this->db->execute($count_sql, $sql_params, null, null, false);
		if($count_result === false) {
			$this->db->addError();
			return 'error';
		}

		$data_count = intval($count_result[0][0]);
		if($data_count == 0)  {
			$errorList =& $this->actionChain->getCurErrorList();
			$errorList->add(get_class($this), ROOM_EXPORT_NONE);
			return 'error';
		}
		
		return 'success';
	}
}
?>