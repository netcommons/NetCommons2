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
class Multidatabase_View_Main_Search_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $multidatabase_id = null;
    var $sort_section = null;
    var $sort_metadata = null;
    var $status = null;
    var $back = null;

    //バリデートを受け取るため
    var $mdb_obj = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
    var $mdbView = null;
    var $session = null;

    // 値をセットするため
    var $section_metadatas = null;
    var $sort_metadatas = null;
    var $date_from = null;
	var $date_to = null;
	var $result_condition = null;
	var $result_contents = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$today = timezone_date(null, false, "Ymd");
		$timestamp = mktime(0, 0, 0, substr($today,4,2)-1, substr($today,6,2), substr($today,0,4));
		$date_from = date("Ymd", $timestamp);
		$timestamp = mktime(0, 0, 0, substr($date_from,4,2), substr($date_from,6,2), substr($date_from,0,4));
//		$this->date_from = date(_INPUT_DATE_FORMAT, $timestamp);
		$date_to = $today;
		$timestamp = mktime(0, 0, 0, substr($date_to,4,2), substr($date_to,6,2), substr($date_to,0,4));
		$this->date_to = date(_INPUT_DATE_FORMAT, $timestamp);

    	$section_params = array(
    		'multidatabase_id' => intval($this->multidatabase_id),
    		'type IN ('.MULTIDATABASE_META_TYPE_SECTION.','.MULTIDATABASE_META_TYPE_MULTIPLE.')'=>null
    	);
    	$this->section_metadatas = $this->mdbView->getMetadatas($section_params);
    	if($this->section_metadatas === false) {
    		return 'error';
    	}

		$sort_params = array(
    		"multidatabase_id" => intval($this->multidatabase_id),
    		"sort_flag" => _ON
    	);
    	$this->sort_metadatas = $this->mdbView->getMetadatas($sort_params);
    	if($this->sort_metadatas === false) {
    		return 'error';
    	}



    	return 'success';
    }
}
?>
