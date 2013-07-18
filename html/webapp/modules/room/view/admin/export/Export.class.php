<?php

/**
 * ルーム管理>>ルーム一覧>>参加者修正>>エクスポート
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Room_View_Admin_Export extends Action
{
	// リクエストパラメータをセットするため
	
	// 使用コンポーネントを受け取るため
    var $db = null;
	var $csvMain = null;
    var $session = null;
	var $configView = null;
	var $pagesView = null;
	var $usersView = null;

    var $actionChain = null;

	// validatorでセット
	
    // 値をセットするため
	var $page = null;
	var $parent_page = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return false;
		$default_entry_role_auth_public = $config['default_entry_role_auth_public']['conf_value'];
    	$default_entry_role_auth_group = $config['default_entry_role_auth_group']['conf_value'];
    	$default_entry_role_auth_private = $config['default_entry_role_auth_private']['conf_value'];


		$edit_current_page_id =& $this->session->getParameter(array('room', 'export', 'current_page_id'));
		$edit_current_page_name =& $this->session->getParameter(array('room', 'export', 'current_page_name'));

       	$this->page =& $this->pagesView->getPageById($edit_current_page_id);
       	$this->parent_page =& $this->pagesView->getPageById($edit_current_page_name);

		$select_str   =& $this->session->getParameter(array('room', $edit_current_page_id, 'selected_select_str'));
		$from_str     =& $this->session->getParameter(array('room', $edit_current_page_id, 'selected_from_str'));
		$add_from_str =& $this->session->getParameter(array('room', $edit_current_page_id, 'selected_add_from_str'));
		$where_str    =& $this->session->getParameter(array('room', $edit_current_page_id, 'selected_where_str'));
		$add_where_str =& $this->session->getParameter(array('room', $edit_current_page_id,'selected_add_where_str'));
		$export_add_where_str = ' AND {authorities}.user_authority_id != ?';
		$params       =& $this->session->getParameter(array('room', $edit_current_page_id, 'selected_params'));
		$from_params  =& $this->session->getParameter(array('room', $edit_current_page_id, 'selected_from_params'));
		$where_params =& $this->session->getParameter(array('room', $edit_current_page_id, 'selected_where_params'));
		if(!is_array($from_params)) $from_params = array();
		if(!is_array($where_params)) $where_params = array();

		$export_where_params = array(_AUTH_ADMIN);
		
    	$sql_params = array_merge((array)$params, (array)$from_params, (array)$where_params, (array)$export_where_params);

		$order_params = array("system_flag"=>"DESC", "hierarchy"=>"DESC" , "user_authority_id"=>"DESC" , "handle"=>"ASC");
		$order_str = $this->db->getOrderSQL($order_params);

		$sql = $select_str.$from_str.$add_from_str.$where_str.$add_where_str.$export_add_where_str.$order_str;

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

		// ヘッダ
    	// handle の項目名と
		// 権限の項目名->[参加者権限]固定
		$handle_item = $this->usersView->getItemById($this->usersView->getItemIdByTagName('handle'));
		if(defined($handle_item['item_name'])) {
			$name = constant($handle_item['item_name']);
		}
		else {
			$name = $handle_item['item_name'];
		}
		$header_name = array($name, ROOM_EXPORT_AUTHORITY_NAME);
   		$this->csvMain->add($header_name);


		// DBからデータを取得
		$data_limit = 1000;
		for ($data_offset=0; $data_offset<$data_count; $data_offset=$data_offset+$data_limit) {

			$datas =& $this->db->execute($sql, $sql_params, intval($data_limit), intval($data_offset), true, array($this, "_fetchcallback"), array($default_entry_role_auth_public, $default_entry_role_auth_group));

	    	if (isset($datas) && is_array($datas)) {
	    		foreach($datas as $data) {
		    		$this->csvMain->add($data);
	    		}
	    	}

		}
		if($data_count > 0)  {
            //
            // ファイル名決定 ルームID＋ルーム名＋YYYYMMDD
            //
            $formatfilename = $edit_current_page_id . '_' . $edit_current_page_name . '_'  . timezone_date(null,false,'Ymd');

    		$this->csvMain->download($formatfilename);
		}

		$this->session->removeParameter(array('room','export'));
    	exit;
//		return 'success';
	}
	function &_fetchcallback($result, $func_params) {

		$default_entry_flag = $this->page['default_entry_flag'];
		$default_entry_role_auth_public = $func_params[0];
		$default_entry_role_auth_group = $func_params[1];


		$ret = array();
		while ($row = $result->fetchRow()) {

			// default参加のルームかどうかで判断が変わる
			if(is_null($row['authority_id'])) {
				if($default_entry_flag == _ON) {
					if($this->page['private_flag'] == _ON) {
						$auth_id = strval(_AUTH_OTHER);	// ありえない
					}
					else if($this->page['space_type'] == _SPACE_TYPE_GROUP) {
						$auth_id = $default_entry_role_auth_group;
					}
					else {
						$auth_id = $default_entry_role_auth_public;
					}
				}
				else {
					$auth_id = strval(_AUTH_OTHER);
				}
			}
			else {
				$auth_id = $row['authority_id'];
			}

			$ret[] = array('handle'=>$row['handle'], 'authority_id'=>$auth_id);
		}
		return $ret;
	}
}
?>