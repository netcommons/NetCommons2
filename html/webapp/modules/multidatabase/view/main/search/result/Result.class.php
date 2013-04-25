<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース検索
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Main_Search_Result extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $multidatabase_id = null;
	var $keyword = null;
	var $select_kind = null;
	var $handle = null;
	var $date_from = null;
	var $date_to = null;
	var $status = null;
	var $sort_section = null;
	var $sort_metadata = null;
	var $now_page = null;
	var $back = null;

	// バリデートによりセット
	var $mdb_obj = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	var $session = null;

	// 値をセットするため
	var $section_metadatas = null;
	var $sort_metadatas = null;
	var $title_metadata = null;
	var $result_contents = null;
	var $result_count = null;

	//ページ
	var $data_cnt    = 0;
	var $total_page  = 0;
	var $next_link   = FALSE;
	var $prev_link   = FALSE;
	var $disp_begin  = 0;
	var $disp_end    = 0;
	var $link_array  = NULL;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if ($this->back == _ON) {
			$this->keyword = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'keyword'));
			$this->select_kind = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'select_kind'));
			$this->date_from = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'date_from'));
			$this->date_to = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'date_to'));
			$this->handle = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'handle'));
			$this->status = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'status'));
			$this->sort_section = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'sort_section'));
			$this->sort_metadata = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'sort_metadata'));
			$this->now_page = $this->session->getParameter(array('multidatabase', 'search', $this->block_id, 'now_page'));
		} else {
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'keyword'), $this->keyword);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'select_kind'), $this->select_kind);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'date_from'), $this->date_from);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'date_to'), $this->date_to);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'handle'), $this->handle);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'status'), $this->status);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'sort_section'), $this->sort_section);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'sort_metadata'), $this->sort_metadata);
			$this->session->setParameter(array('multidatabase', 'search', $this->block_id, 'now_page'), $this->now_page);
		}

		$sort_params = array(
			"multidatabase_id" => intval($this->multidatabase_id),
			"sort_flag" => _ON
		);
		$this->sort_metadatas = $this->mdbView->getMetadatas($sort_params);
		if($this->sort_metadatas === false) {
			return 'error';
		}

		$keywords = array();
		if($this->keyword != "") {
			if($this->select_kind == "phrase") {
				$keywords[] = $this->keyword;
			}else {
				$keywords = explode(" ", trim(preg_replace("/[　\s]+/u", " ", $this->keyword)));

				if ($this->select_kind != 'or'
					&& $this->select_kind != 'and') {
					$this->select_kind = 'and';
				}
			}
		}

		$sqlwhere_content = "";
		$metadatas = $this->mdbView->getMetadatas(array("multidatabase_id" => intval($this->multidatabase_id)));
		if($metadatas === false) {
			return 'error';
		}
		$search_metadatas = array();
		$this->section_metadatas = array();
		foreach ($metadatas as $metadata) {
			if ($metadata['search_flag'] == _ON) {
				$search_metadatas[] = $metadata;
			}
			if ($metadata['type'] == MULTIDATABASE_META_TYPE_SECTION
				|| $metadata['type'] == MULTIDATABASE_META_TYPE_MULTIPLE) {
				$this->section_metadatas[$metadata['metadata_id']] = $metadata;
			}
		}
		$this->title_metadata = $metadatas[$this->mdb_obj['title_metadata_id']];

		$keywordBindValues = array();
		foreach ($keywords as $keyword) {
			if (!empty($sqlwhere_content)) {
				$sqlwhere_content .= $this->select_kind . ' ';
			}
			$keywordWhereStatements = array();
			foreach ($search_metadatas as $metadata) {
				$keywordWhereStatements[] = ' m_content' . $metadata['metadata_id'] . '.content LIKE ? ';
				$keywordBindValues[] = '%' . $keyword . '%';
			}
			if (empty($keywordWhereStatements)) {
				break;
			}

			$sqlwhere_content .= '(' .  join('OR', $keywordWhereStatements) . ') ';
		}

		$sqlwhere = "";
		if(!empty($this->date_from)) {
			$fm_insert_date = $this->date_from."000000";
			$sqlwhere .= " AND {multidatabase_content}.insert_time >= ".$fm_insert_date." ";
			$timestamp = mktime(0, 0, 0, substr($this->date_from,4,2), substr($this->date_from,6,2), substr($this->date_from,0,4));
			$this->date_from = date(_DATE_FORMAT, $timestamp);
		}

		if(!empty($this->date_to)) {
			$to_insert_date = $this->date_to."999999";
			$sqlwhere .= " AND {multidatabase_content}.insert_time <= ".$to_insert_date." ";
			$timestamp = mktime(0, 0, 0, substr($this->date_to,4,2), substr($this->date_to,6,2), substr($this->date_to,0,4));
			$this->date_to = date(_DATE_FORMAT, $timestamp);
		}

		if ($this->keyword != "" && empty($search_metadatas)) {
			$this->result_contents = array();
			return 'success';
		}

		if($this->handle != "") {
			$handles = explode(" ", trim(preg_replace("/[　\s]+/u", " ", $this->handle)));
			if(is_array($handles)) {
				foreach (array_keys($handles) as $i) {
					$handlesql[] = " {multidatabase_content}.insert_user_name LIKE '%".$handles[$i]."%' ";
				}
			}
			$sqlwhere .= " AND (".join(" OR ", $handlesql).")";
		}

		if($this->status != "") {
			if($this->status != MULTIDATABASE_STATUS_ALL) {
				$sqlwhere .= " AND {multidatabase_content}.agree_flag=".$this->status." ";
			}
		}

		if (!empty($sqlwhere_content)) {
			$sqlwhere .= " AND (".$sqlwhere_content.") ";
		}

		$where_params = array();
		if(!empty($this->sort_section)) {
			foreach($this->sort_section as $key => $val) {
				$where_params["m_content".$key.".content"]= $val;
			}
		}

		if(empty($this->sort_metadata) || $this->sort_metadata == MULTIDATABASE_DEFAULT_SEQUENCE_SORT) {
			$order_params = array(
				"{multidatabase_content}.display_sequence" => "ASC",
				"{multidatabase_content}.insert_time" => "DESC"
			);
		}else if($this->sort_metadata == MULTIDATABASE_DEFAULT_DATE_SORT) {
			$order_params = array(
				"{multidatabase_content}.insert_time" => "DESC"
			);
		}else if($this->sort_metadata == MULTIDATABASE_DEFAULT_DATE_ASC_SORT) {
			$order_params = array(
				"{multidatabase_content}.insert_time" => "ASC"
			);
		}else if($this->sort_metadata == MULTIDATABASE_DEFAULT_VOTE_SORT) {
			$order_params = array(
				"{multidatabase_content}.vote_count" => "DESC",
				"{multidatabase_content}.insert_time" => "DESC"
			);
		}else if(isset($this->sort_metadatas[$this->sort_metadata]) && ($this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_FILE || $this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_IMAGE)) {
			$order_params = array(
				"F".$this->sort_metadata.".file_name" => "ASC",
				"{multidatabase_content}.insert_time" => "DESC"
			);
		}else if(isset($this->sort_metadatas[$this->sort_metadata]) && $this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_INSERT_TIME) {
			$order_params = array(
				"{multidatabase_content}.insert_time" => "ASC"
			);
		}else if(isset($this->sort_metadatas[$this->sort_metadata]) && $this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_UPDATE_TIME) {
			$order_params = array(
				"{multidatabase_content}.update_time" => "ASC"
			);
		}else {
			$order_params = array(
				"m_content".$this->sort_metadata.".content" => "ASC",
				"{multidatabase_content}.insert_time" => "DESC"
			);
		}

		$this->result_count = $this->mdbView->getMDBListCount($this->multidatabase_id,
																$metadatas,
																$where_params,
																$sqlwhere,
																$keywordBindValues);
		$this->setPageInfo($this->result_count, MULTIDATABASE_SEARCH_RESULT_VISIABLE_NUMBER, $this->now_page);
		$this->result_contents = $this->mdbView->getSearchResult($this->multidatabase_id,
																	$metadatas,
																	$where_params,
																	$order_params,
																	$sqlwhere,
																	MULTIDATABASE_SEARCH_RESULT_VISIABLE_NUMBER,
																	$this->disp_begin,
																	$keywordBindValues);
		if($this->result_contents === false) {
			return 'error';
		}
		return 'success';
	}

	/**
	 * ページに関する設定を行います
	 *
	 * @param int disp_cnt 1ページ当り表示件数
	 * @param int now_page 現ページ
	 */
	function setPageInfo($data_cnt, $disp_cnt, $now_page = NULL){
		$this->data_cnt = $data_cnt;
		// now page
		$this->now_page = (NULL == $now_page) ? 1 : $now_page;
		// total page
		$this->total_page = ceil($this->data_cnt / $disp_cnt);
		if($this->total_page < $this->now_page) {
			$this->now_page = 1;
		}
		// link array {{
		if(($this->now_page - MULTIDATABASE_FRONT_AND_BEHIND_LINK_CNT) > 0){
			$start = $this->now_page - MULTIDATABASE_FRONT_AND_BEHIND_LINK_CNT;
		}else{
			$start = 1;
		}
		if(($this->now_page + MULTIDATABASE_FRONT_AND_BEHIND_LINK_CNT) >= $this->total_page){
			$end = $this->total_page;
		}else{
			$end = $this->now_page + MULTIDATABASE_FRONT_AND_BEHIND_LINK_CNT;
		}
		$i = 0;
		for($i = $start; $i <= $end; $i++){
			$this->link_array[] = $i;
		}
		// next link
		if($disp_cnt < $this->data_cnt){
			if($this->now_page < $this->total_page){
				$this->next_link = TRUE;
			}
		}
		// prev link
		if(1 < $this->now_page){
			$this->prev_link = TRUE;
		}
		// begin disp number
		$this->disp_begin = ($this->now_page - 1) * $disp_cnt;
		// end disp number
		$tmp_cnt = $this->now_page * $disp_cnt;
		$this->disp_end = ($this->data_cnt < $tmp_cnt) ? $this->data_cnt : $tmp_cnt;
	}
}
?>
