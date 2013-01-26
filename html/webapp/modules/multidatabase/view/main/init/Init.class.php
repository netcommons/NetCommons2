<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ一覧画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Multidatabase_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $sort_section = null;
	var $sort_metadata = null;
	var $visible_item = null;
	var $now_page = null;
	var $html_flag = null;

	var $content_id = null;

	// バリデートによりセット
	var $mdb_obj = null;
	var $metadatas = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	var $session = null;
	var $mobileView = null;

	// 値をセットするため
	var $section_metadatas = null;
	var $multidatabase_id = null;
	var $metadata_exists = true;
	var $exists = true;
	var $sort_metadatas = null;
	var $mdblist = null;
	var $vote_count = null;
	var $block_num = null;

	//ページ
	var $data_cnt	= 0;
	var $total_page = 0;
	var $next_link = false;
	var $prev_link = false;
	var $disp_begin = 0;
	var $disp_end = 0;
	var $link_array = null;

	/**
	 * コンテンツ一覧画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
			$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
		}

		if(empty($this->metadatas)) {
			$this->metadata_exists = false;
			return 'success';
		}

		$section_params = array(
			"multidatabase_id" => intval($this->multidatabase_id),
			"list_flag" => _ON,
			"type IN (".MULTIDATABASE_META_TYPE_SECTION.",".MULTIDATABASE_META_TYPE_MULTIPLE.") " => null
		);
		$this->section_metadatas = $this->mdbView->getMetadatas($section_params);
		if($this->section_metadatas === false) {
			return 'error';
		}

		$sort_params = array(
			"multidatabase_id" => intval($this->multidatabase_id),
			"sort_flag" => _ON,
			"list_flag" => _ON
		);
		$this->sort_metadatas = $this->mdbView->getMetadatas($sort_params);
		if($this->sort_metadatas === false) {
			return 'error';
		}

		$sort_section = $this->session->getParameter(array("multidatabase", $this->block_id, "sort_section"));
		if(!empty($this->sort_section)) {
			$this->session->setParameter(array("multidatabase", $this->block_id, "sort_section"), $this->sort_section);
			// 初回、セッションの $sort_section は空なので、その判定を追加 by nagahara@opensource-workshop.jp
			if(!empty($sort_section) && $sort_section != $this->sort_section) {
				// カテゴリ変更を行った場合、1ページ目を表示する。
				$this->now_page = 1;
			}
		}else if(!empty($sort_section)) {
			$this->sort_section = $sort_section;
		}
		$where_params = array();
		if(!empty($this->sort_section)) {
			foreach($this->sort_section as $key => $val) {
				$where_params["m_content".$key.".content"] = $val;
			}
		}

		$visible_item = $this->session->getParameter(array("multidatabase", $this->block_id, "visible_item"));
		if($this->visible_item != "") {
			if($visible_item != "" && $this->visible_item != $visible_item) {
				$this->now_page = 1;
			}
			$this->session->setParameter(array("multidatabase", $this->block_id, "visible_item"), $this->visible_item);
		}else if($visible_item != ""){
			$this->visible_item = $visible_item;
		}else {
			$this->visible_item = $this->mdb_obj['visible_item'];
		}

		$sort_metadata = $this->session->getParameter(array("multidatabase", $this->block_id, "sort_metadata"));
		if(!empty($this->sort_metadata)) {
			$this->session->setParameter(array("multidatabase", $this->block_id, "sort_metadata"), $this->sort_metadata);
			// 初回、セッションの $sort_metadata は空なので、その判定を追加 by nagahara@opensource-workshop.jp
			if(!empty($sort_metadata) && $sort_metadata != $this->sort_metadata) {
				// 並べ替えを行った場合、1ページ目を表示する。
				$this->now_page = 1;
			}
		}else if(!empty($sort_metadata)) {
			$this->sort_metadata = $sort_metadata;
		}else {
			$this->sort_metadata = $this->mdb_obj['default_sort'];
		}
   		if($this->sort_metadata != MULTIDATABASE_DEFAULT_DATE_SORT
   			&& $this->sort_metadata != MULTIDATABASE_DEFAULT_DATE_ASC_SORT
			&& $this->sort_metadata != MULTIDATABASE_DEFAULT_VOTE_SORT
			&& $this->sort_metadata != MULTIDATABASE_DEFAULT_SEQUENCE_SORT
			&& !array_key_exists($this->sort_metadata, $this->sort_metadatas)) {
			$this->sort_metadata = MULTIDATABASE_DEFAULT_SEQUENCE_SORT;
		}

		$mdbcount = $this->mdbView->getMDBListCount($this->multidatabase_id, $this->metadatas, $where_params);
		if($mdbcount === false) {
			return 'error';
		}
		if($mdbcount == 0) {
			$this->exists = false;
			return 'success';
		}

		$now_page = $this->session->getParameter(array("multidatabase", $this->block_id, "now_page"));
		if(!empty($this->now_page)) {
			$this->session->setParameter(array("multidatabase", $this->block_id, "now_page"), $this->now_page);
		}else if(!empty($now_page)){
			$this->now_page = $now_page;
		}

		if(!empty($this->visible_item)) {
			$this->setPageInfo($mdbcount, $this->visible_item, $this->now_page);
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
				"{multidatabase_content}.vote_count" => "DESC"
			);
		}else if(isset($this->sort_metadatas[$this->sort_metadata]) && ($this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_FILE || $this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_IMAGE)) {
			$order_params = array(
				"F".$this->sort_metadata.".file_name" => "ASC",
				"{multidatabase_content}.insert_time" => "DESC"
			);
		}else if (isset($this->sort_metadatas[$this->sort_metadata]) && $this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_INSERT_TIME){
			$order_params = array(
				"{multidatabase_content}.insert_time" => "ASC"
			);
		}else if (isset($this->sort_metadatas[$this->sort_metadata]) && $this->sort_metadatas[$this->sort_metadata]["type"] == MULTIDATABASE_META_TYPE_UPDATE_TIME){
			$order_params = array(
				"{multidatabase_content}.update_time" => "ASC"
			);
		}else{
			$order_params = array(
				"m_content".$this->sort_metadata.".content" => "ASC",
				"{multidatabase_content}.insert_time" => "DESC"
			);
		}

		$this->mdblist = $this->mdbView->getMDBList($this->multidatabase_id, $this->metadatas, $where_params, $order_params, $this->visible_item, $this->disp_begin);
		if($this->mdblist === false) {
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