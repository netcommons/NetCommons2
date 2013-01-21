<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id = null;
	var $result_only = null;
	var $page_id = null;

    // 使用コンポーネントを受け取るため
	var $whatsnewView = null;
	var $session = null;

	// Filterによりセット
	var $room_arr_flat =null;

 	// validatorから受け取るため
	var $whatsnew_obj = null;

    // 値をセットするため
	var $whatsnew_data = null;
	var $display_count = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->session->removeParameter(array("whatsnew", "enroll_room_arr", $this->block_id));

    	if( $this->session->getParameter('_mobile_flag') == true ) {
    		return "mobile_action";
		}

		$this->display_count = intval($this->whatsnew_obj["display_title"]) +
    							intval($this->whatsnew_obj["display_room_name"]) +
    							intval($this->whatsnew_obj["display_module_name"]) +
    							intval($this->whatsnew_obj["display_user_name"]) +
    							intval($this->whatsnew_obj["display_insert_time"]);

    	$this->whatsnew_data = $this->whatsnewView->getResults($this->whatsnew_obj, $this->room_arr_flat);

    	$user_id = $this->session->getParameter("_user_id");
    	if (!empty($user_id)) {
    		$this->room_arr_flat["0"] = array("page_name" => WHATSNEW_NO_PAGE);
    		$this->whatsnew_obj["select_room_list"][] = "0";
    	}
    	if ($this->whatsnew_data === false) {
    		return 'error';
    	}

		if ($this->whatsnew_obj["allow_rss_feed"] == _ON) {
			$meta = $this->session->getParameter("_meta");
			if (empty($meta['rss_alternate'])) {
				$meta['rss_alternate'] = BASE_URL.INDEX_FILE_NAME.
											'?action=whatsnew_view_main_rss' .
											'&page_id='.$this->page_id.
											'&block_id='.$this->block_id.
											($this->whatsnew_obj["display_flag"] == _OFF ? '&display_days='.$this->whatsnew_obj["display_days"] : '&display_number='.$this->whatsnew_obj["display_number"]).
											'&_header='._OFF;
				$this->session->setParameter("_meta", $meta);
			}
		}

    	if ($this->result_only == _ON) {
			return 'result';
    	} else {
			return 'success';
    	}
    }
}
?>