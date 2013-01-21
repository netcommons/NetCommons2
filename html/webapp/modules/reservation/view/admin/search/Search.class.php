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
class Reservation_View_Admin_Search extends Action
{
	// リクエストを受け取るため
	var $target_room = null;

	// Search Filterを受け取るため
	var $limit = null;
	var $offset = null;
	var $params = null;
	var $sqlwhere = null;

	// Allow_Id_List Filterから受け取るため
	var $room_id_arr = null;

	// validatorから受け取るため
	var $location_list = null;

	// コンポーネントを受け取るため
	var $reservationView = null;
	var $session = null;

	// 値をセットするため
	var $count = 0;
	var $results = null;

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		//表示するルームなし
		if (empty($this->room_id_arr)) {
			return 'success'; 
		}
		//表示する施設なし
		if (empty($this->location_list)) {
			return 'success'; 
		}
		
		$_user_id = $this->session->getParameter("_user_id");

		//表示するルームなし
		if (empty($this->room_id_arr) && empty($_user_id)) {
			return 'success'; 
		}
		if (!empty($_user_id) && empty($this->target_room)) {
			$this->room_id_arr[] = "0";
		}
		$sqlwhere = " AND reserve.room_id IN (".implode(",", $this->room_id_arr).")";
		$sqlwhere .= $this->sqlwhere;

		$this->count = $this->reservationView->getSearchCount($sqlwhere, $this->params);
		if ($this->count > 0) {
			$this->results = $this->reservationView->getSearchResults($sqlwhere, $this->params, $this->limit, $this->offset);
		}
		return 'success';
	}
}
?>