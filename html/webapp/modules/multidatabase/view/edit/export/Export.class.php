<?php

/**
 * エクスポート
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Multidatabase_View_Edit_Export extends Action
{
	// リクエストパラメータをセットするため
	var $multidatabase_id = null;
	var $block_id = null;

	// 使用コンポーネントを受け取るため
    var $csvMain = null;
    var $mdbView = null;
    var $actionChain = null;


    // バリデートによりセット
	var $mdb_obj = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$metadatas = $this->mdbView->getMetadatas(array("multidatabase_id" => intval($this->multidatabase_id)));
		if($metadatas === false) {
    		return 'error';
    	}

		$order_params = array(
			"display_sequence" => "ASC"
		);
		$data = array();
		$line = 0;
		$data_contents = array();
		foreach ($metadatas as $metadata) {
			$data[] = $metadata['name'];

			$contents = $this->mdbView->getMDBTitleList(intval($this->multidatabase_id), $metadata['metadata_id'], $order_params);
			if($contents === false) {
				return 'error';
			}
			$i = 0;
			foreach($contents as $content) {
				if($content['title'] != ""
					 && ($metadata['type'] == MULTIDATABASE_META_TYPE_FILE 
							|| $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE)) {
					if ($content['file_name'] != "") {
						$content['title'] = $content['file_name'];
					} else {
						$content['title'] = "";
					}
				} elseif ($content['title'] != "" && $metadata['type'] == MULTIDATABASE_META_TYPE_DATE) {
					$content['title'] = timezone_date_format($content['title'], _DATE_FORMAT);

				} elseif ($content['insert_time'] != "" && $metadata['type'] == MULTIDATABASE_META_TYPE_INSERT_TIME) {
					$content['title'] = timezone_date_format($content['insert_time'], _FULL_DATE_FORMAT);

				} elseif ($content['update_time'] != "" && $metadata['type'] == MULTIDATABASE_META_TYPE_UPDATE_TIME) {
					$content['title'] = timezone_date_format($content['update_time'], _FULL_DATE_FORMAT);

				} elseif ($content['title'] != "" && $metadata['type'] == MULTIDATABASE_META_TYPE_AUTONUM) {
					$content['title'] = intval($content['title']);
				}
				$data_contents[$i][$line] = $content['title'];
				$i++;
			}
			$line++;
		}

		$this->csvMain->add($data);
		foreach($data_contents as $data_content) {
			$this->csvMain->add($data_content);
		}
		$this->csvMain->download($this->mdb_obj['multidatabase_name']);

		exit;
	}
}
?>