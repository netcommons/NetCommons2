<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Admin_Search extends Action
{
	// リクエストを受け取るため
	var $block_id = null;
	var $limit = null;
	var $offset = null;
	var $only_count = null;

	// WHERE句用パラメータ
	var $params = null;
	var $sqlwhere = null;
	
	// 値をセットするため
	var $count = 0;
	var $results = null;
	
	// Filterによりセット
	var $block_id_arr = null; 
	var $room_id_arr = null; 
	
	// コンポーネントを受け取るため
	var $db = null;

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		if ($this->block_id_arr) {
			$sqlwhere = " WHERE block.block_id IN (".implode(",", $this->block_id_arr).")";
		} else {
			return 'success';
		}
		$sqlwhere .= " AND contents.temporary_flag = ".MULTIDATABASE_STATUS_RELEASED_VALUE;
		$sqlwhere .= $this->sqlwhere;
		$sqlwhere .= " GROUP BY contents.content_id ";
		$sql = "SELECT COUNT(*)" .
				" FROM {multidatabase} mdb" .
				" INNER JOIN {multidatabase_block} block ON (mdb.multidatabase_id=block.multidatabase_id)".
				" INNER JOIN {multidatabase_content} contents ON (mdb.multidatabase_id=contents.multidatabase_id)".  
				" INNER JOIN {multidatabase_metadata_content} datas ON (contents.content_id=datas.content_id)" .
				
				$sqlwhere;
		$result = $this->db->execute($sql, $this->params, null ,null, false);
		if ($result !== false) {
			$this->count = count($result);
		} else {
			$this->count = 0;
		}
		if ($this->only_count == _ON) {
			return 'count';
		}

		if ($this->count > 0) {
			$sql = "SELECT block.block_id, mdb.multidatabase_id, mdb.title_metadata_id, title.content as title, datas.content as content, contents.*, file.file_name" .
				" FROM {multidatabase} mdb" .
				" INNER JOIN {multidatabase_block} block ON (mdb.multidatabase_id=block.multidatabase_id)".
				" INNER JOIN {multidatabase_content} contents ON (mdb.multidatabase_id=contents.multidatabase_id)".  
				" INNER JOIN {multidatabase_metadata_content} datas ON (contents.content_id=datas.content_id)" .
				" LEFT JOIN {multidatabase_metadata_content} title ON (mdb.title_metadata_id=title.metadata_id AND datas.content_id=title.content_id)" .
				" LEFT JOIN {multidatabase_file} file ON (title.metadata_content_id=file.metadata_content_id)" .
				
				$sqlwhere.
					" ORDER BY contents.insert_time DESC";
			$this->results =& $this->db->execute($sql ,$this->params, $this->limit, $this->offset, true, array($this, '_fetchcallback'));
			$metadata_id_arr = array();
			foreach ($this->results as $result) {
				$metadata_id_arr[] = $result["metadata_id"];
			}
			$where_params = array(
				"metadata_id IN (" . implode(',', $metadata_id_arr) . ") " => null
			);
			$metadatas = $this->db->selectExecute("multidatabase_metadata", $where_params, null, null, null, array($this, '_fetchMetadataCallback'));
			foreach ($this->results as $key=>$result) {
				$metadata = $metadatas[$result["metadata_id"]];
				switch ($metadata["type"]) {
					case MULTIDATABASE_META_TYPE_FILE:
					case MULTIDATABASE_META_TYPE_IMAGE:
						if (empty($result["file_name"])) {
							$title = MULTIDATABASE_NOTITLE;
						} else {
							$title = $result["file_name"];
						}
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_WYSIWYG:
						$container =& DIContainerFactory::getContainer();
						$convertHtml =& $container->getComponent("convertHtml");
			    		$title = $convertHtml->convertHtmlToText($result["title"]);
			    		$title = preg_replace("/\\\n/", " ", $title);
						$title = mb_substr($title, 0, _SEARCH_CONTENTS_LEN + 1, INTERNAL_CODE);
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_AUTONUM:
						$title = intval($result["title"]);
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_DATE:
						if (empty($result["title"])) {
							$title = MULTIDATABASE_NOTITLE;
						} else {
							$title = timezone_date_format($result["title"], _DATE_FORMAT);
						}
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_INSERT_TIME:
						$title = timezone_date_format($result["pubDate"], _FULL_DATE_FORMAT);
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_UPDATE_TIME:
						$title = timezone_date_format($result["update_time"], _FULL_DATE_FORMAT);
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_MULTIPLE:
						if (empty($result["title"])) {
							$title = MULTIDATABASE_NOTITLE;
						} else {
							$multipleArr = explode("|",$result["title"]);
							$title = $multipleArr[0];
						}
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
						break;
					default:
						if (empty($result["title"])) {
							$title = MULTIDATABASE_NOTITLE;
						} else {
							$title = $result["title"];
						}
						$this->results[$key]["title"] = $title;
						$this->results[$key]["description"] = $title;
				}
				unset($this->results[$key]["title_metadata_id"]);
				unset($this->results[$key]["file_name"]);
				unset($this->results[$key]["update_time"]);
			}
		}
		return 'success';
	}

	/**
	 * fetch時コールバックメソッド(blocks)
	 * 
	 * @param result adodb object
	 * @access	private
	 */
	function _fetchcallback($result) 
	{
		$ret = array();
		$i = 0;
		while ($row = $result->fetchRow()) {
			$ret[$i] = array();
			$ret[$i]['block_id'] =  $row['block_id'];
			$ret[$i]['pubDate'] = $row['insert_time'];
			$ret[$i]['title'] = $row['title'];
    		$ret[$i]['url'] = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION.
								"&block_id=".$row['block_id'].
								"&active_action=multidatabase_view_main_detail".
								"&content_id=".$row['content_id'].
								"&multidatabase_id=". $row['multidatabase_id'].
								"&block_id=". $row['block_id'].
								"#_".$row['block_id'];
			//$ret[$i]['description'] = $row['content'];
			$ret[$i]['description'] = $row['title'];
			$ret[$i]['user_id'] = $row['insert_user_id'];
			$ret[$i]['user_name'] = $row['insert_user_name'];
			$ret[$i]['guid'] = "content_id=".$row['content_id'];

			$ret[$i]['metadata_id'] = $row['title_metadata_id'];
			$ret[$i]['file_name'] = $row['file_name'];
			$ret[$i]['update_time'] = $row['update_time'];
			$i++;
		}
		return $ret;
	}
	/**
	 * fetch時コールバックメソッド(blocks)
	 * 
	 * @param result adodb object
	 * @access	private
	 */
	function _fetchMetadataCallback($result) 
	{
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row["metadata_id"]] = $row;
		}
		return $ret;
	}
}
?>