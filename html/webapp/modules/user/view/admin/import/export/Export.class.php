<?php

/**
 * 会員管理>>会員検索>>エクスポート
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class User_View_Admin_Import_Export extends Action
{
	// リクエストパラメータをセットするため
	var $chk_count_flag = null;
	
	// 使用コンポーネントを受け取るため
    var $db = null;
	var $csvmain = null;
    var $session = null;
    var $usersView = null;
    var $actionChain = null;
	
    // 値をセットするため
	var $formatfilename = "usr_importfile";
	var $sort_col = null;
	var $sort_dir = null;
	var $user_id = null;
	var $user_auth_id = null;
	var $items = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	/* データ */
    	$this->sort_col = ($this->sort_col == null) ? $this->sort_col ="user_authority_id" : $this->sort_col;
		$this->sort_dir = ($this->sort_dir == null) ? $this->sort_dir ="DESC" : $this->sort_dir;
		$order_params = array(
			$this->sort_col => $this->sort_dir,
			"{users}.system_flag" => "DESC",
			"{users}.handle" => "ASC"
		);
		$order_str = " ".$this->db->getOrderSQL($order_params);

		/* 検索条件を取得 */
		$params =& $this->session->getParameter(array("user", "selected_params"));
		$sql = $this->session->getParameter(array("user", "selected_select_str")).
				$this->session->getParameter(array("user", "selected_where_str")) . $order_str;
				
		// レコード数取得
		$count_sql = "SELECT COUNT(*) ";
		$count_sql .= $this->session->getParameter(array("user", "selected_where_str"))
					. " AND {authorities}.user_authority_id <".$this->session->getParameter("_user_auth_id");
		$count_result =& $this->db->execute($count_sql, $params, null, null, false);
		if($count_result === false) {
			$this->db->addError();
			return 'error';
		}
		$data_count = intval($count_result[0][0]);
		if($data_count == 0)  {
			$errorList =& $this->actionChain->getCurErrorList();
			$errorList->add(get_class($this), USER_EXPORT_NONE);
			return 'error';
		}
		if($this->chk_count_flag == _ON) {
			return 'success';	
		}
		/* ヘッダ */
    	/* DBからヘッダを取得 */
    	$header_name = $this->csvmain->make_header();
    	if (isset($header_name) && is_array($header_name)) {
	    	$this->csvmain->add($header_name, $header_name);
    	} else {
			return 'error';
    	}
    	/* DBからデータを取得 */
		$data_limit = 1000;
		for ($data_offset=0; $data_offset<$data_count; $data_offset=$data_offset+$data_limit) {
			$users =& $this->db->execute($sql, $params, intval($data_limit), intval($data_offset), true, null);
	    	$datas = $this->csvmain->make_data($users);
	    	if (isset($datas) && is_array($datas)) {
	    		foreach($datas as $data) {
		    		$this->csvmain->add($header_name, $data);
	    		}
	    	}
		}
		if($data_count > 0)  {
			/* CSVファイル作成 */
    		$this->csvmain->download($this->formatfilename);
		}
    	exit;
		//return 'success';
    }
}
?>