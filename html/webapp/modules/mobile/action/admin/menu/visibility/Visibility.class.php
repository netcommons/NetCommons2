<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  隠しページ変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Action_Admin_Menu_Visibility extends Action
{
	// リクエストパラメータを受け取るため
	var $main_page_id = null;
	var $visibility_flag = null;
	var $block_id = null;
	var $module_id = null;

	// 使用コンポーネントを受け取るため
	var $mobileView = null;
	var $mobileAction = null;
	var $pagesView = null;
	var $session = null;
	var	$db = null;

	var	$flat_flag = null;
	var	$each_room_flag = null;

	/**
	 * 隠しページ変更アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$page_id = intval($this->main_page_id);
		$where_params = array(
			"page_id" => $page_id
		);
		$current_page =& $this->pagesView->getPages($where_params);

		if( $this->mobileView->getMobileMenuDisplayType( $this->module_id ) == "tree" ) {
			$this->flat_flag = false;
		}
		else {
			$this->flat_flag = true;
		}
		if( $this->mobileView->getMobileMenuEachRoomMenu( $this->module_id ) == _ON ) {
			$this->each_room_flag = true;
		}
		else {
			$this->each_room_flag = false;
		}

		$private_flag = _OFF;
		if($this->session->getParameter("_auth_id") >= _AUTH_CHIEF && isset($current_page[0]) && $current_page[0]['private_flag'] == _ON && $current_page[0]['thread_num'] == 0) {
			$private_flag = _ON;
		}
		
		if($private_flag == _ON) {
			$menus = $this->mobileView->getMenuDetail(array("block_id"=>0, "page_id"=>-1));
			$params = array(
				"block_id" => 0,
				"page_id" => -1,
				"visibility_flag" => $this->visibility_flag
			);
		} else {
			$menus = $this->mobileView->getMenuDetail(array("block_id"=>0, "page_id"=>$page_id));
			$params = array(
				"block_id" => 0,
				"page_id" => $page_id,
				"visibility_flag" => $this->visibility_flag
			);
		}

		if(!isset($menus[0])) {

			if($this->visibility_flag == _OFF) {
				//insert
				if(!$this->mobileAction->insMenuDetail($params)) {
					return 'error';
				}
			}
		} else {

			if($this->visibility_flag == _ON) {

				//del
				if(!$this->mobileAction->delMenuDetailById(0, $page_id)) {
					return 'error';
				}
				if($private_flag == _ON) {
					if(!$this->mobileAction->delMenuDetailById(0, -1)) {
						return 'error';
					}
				}
			} else {
				//update
				if(!$this->mobileAction->updMenuDetail($params)) {
					return 'error';
				}
			}
		}

		//
		// SPACE の　ON/OFF時は、それのみを操作し、配下の子供については操作しない
		//
		if( ($current_page[0]['space_type'] == $current_page[0]['room_id'] && $current_page[0]['room_id'] == $current_page[0]['page_id'])
			|| $private_flag == _ON ) {
			return 'success';
		}
			
		// 親の表示状態切り替えに伴う子の自動切り替え
		// フラットのときも何もしない！ 
		// または
		// ルーム別メニューが選択されていて、なおかつ、今クリックされたのがルームだったら
		//
		if( isset($current_page[0]) &&
			( ( $this->flat_flag == false ) || ( $this->each_room_flag == true && $current_page[0]['page_id'] == $current_page[0]['room_id'] ) ) ) {

				$parent_id_arr = array($current_page[0]['page_id']);

				if( $current_page[0]['root_id'] == 0 ) {
					$where_params = array(
						"root_id" => $page_id
					);
				}
				else {
					$where_params = array(
						"root_id" => $current_page[0]['root_id']
					);
				}
				$order_params = array("thread_num"=>"ASC");
				$pages =& $this->pagesView->getPages($where_params, $order_params);

				if(isset($pages[0])) {

					$descendants_pages = array();

					foreach($pages as $page) {
						if(in_array($page['parent_id'],$parent_id_arr)) {
							array_push( $parent_id_arr, $page['page_id']);
							$descendants_pages[$page['parent_id']][$page['page_id']] = $page;
						}
					}
					array_shift ($parent_id_arr);
					if(isset($parent_id_arr[0])) {

/**
						foreach($parent_id_arr as $page_id) {

							if( $this->visibility_flag == _OFF ) {
								$this->mobileAction->insMenuDetailByPageId($page_id,2);
							}
							else {
								$this->mobileAction->delMenuDetailByPageId($page_id,2);
							}
						}
**/
						if( $this->visibility_flag == _OFF ) {
							foreach($parent_id_arr as $page_id) {
								$this->mobileAction->insMenuDetailByPageId($page_id,2);
							}
						}
						else {
							$this->delMenuDetail( $descendants_pages, $current_page[0]['page_id'] );
						}
					}
				}

		}
		return 'success';
	}
	function delMenuDetail( &$pages, $cur_parent_id )
	{
		if( isset( $pages[ $cur_parent_id ] ) ) {
			foreach( $pages[ $cur_parent_id ] as $key=>$page ) {

				$this->mobileAction->delMenuDetailByPageId($page['page_id'],2);

				$count = $this->db->countExecute( 'mobile_menu_detail', array( 'page_id'=>$page['page_id'] ) );
				if( $this->flat_flag == false && $count>0 ) {
					continue;
				}
				if( isset( $pages[ $page['page_id'] ] ) ) {
					$this->delMenuDetail( $pages, $page['page_id'] );
				}
			}
		}

	}
}
?>
