<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  ルーム選択メニュー表示切り替えアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Action_Admin_Menu_Room extends Action
{
	// リクエストパラメータを受け取るため
	var $each_room_flag = null;

	// 使用コンポーネントを受け取るため
	var $configAction = null;
	var $pagesView = null;
	var $mobileView = null;
	var $mobileAction = null;

	var $module_id = null;

	/**
	 * ルームごとメニュー形式にするかどうかを切り替える
	 *
	 * @access  public
	 */
	function execute()
	{
		if( $this->configAction->updConfigValue( $this->module_id, MOBILE_MENU_EACH_ROOM, $this->each_room_flag ) != true ) {
			return 'error';
		}
		// ルームごとメニューへの切り替え時は、ルーム親子のVisibilityの関係性の整合性を取る
		if( $this->each_room_flag == _ON  ) {
			$this->allRoomIntegrity(  );
		}
		return 'success';
	}
	function allRoomIntegrity( )
	{
		// 現在のルームの全てを取得できるように
		// スペースルートのものの場合は処理省略（特別扱い）
		$where_params = array(
			'space_type IN (' . _SPACE_TYPE_PUBLIC . ',' . _SPACE_TYPE_GROUP . ')' => null,
			'private_flag' => _OFF,
			'thread_num != 0' => null,
			'page_id = room_id' => null,
			'display_position' => _DISPLAY_POSITION_CENTER
		);
		$order_params = array("thread_num"=>"ASC");
		$pages =& $this->pagesView->getPages($where_params, $order_params);
		// ループで全てを処理
		if(isset($pages[0])) {
			foreach($pages as $page) {

				// もしもルームがOFFに設定されていたら、配下のページツリーを全てOFFに変更
				// このページのVisibilityをチェック
				if( $page['private_flag'] == _ON ) {
					$where_params = array( "page_id"=>-1 );
				}
				else {
					$where_params = array( "page_id"=>$page['page_id'] );
				}
				$chk_result = $this->mobileView->getMenuDetail( $where_params );
				if( isset( $chk_result[0] ) ) {
					$visibility_flag = $chk_result[0]['visibility_flag'];
				}
				else {
					$visibility_flag = _ON;
				}
				if( $visibility_flag != _ON ) {
					$this->allPageIntegrity( $page['page_id'], _OFF );
				}
			}
		}
	}

	function allPageIntegrity( $root_id, $visibility_flag )
	{
		$where_params = array(
						"parent_id" => $root_id,
						"display_position" => _DISPLAY_POSITION_CENTER
		);
		$order_params = array("thread_num"=>"ASC");
		$pages =& $this->pagesView->getPages($where_params, $order_params);

		if(isset($pages[0])) {
			foreach($pages as $page) {


				if( $visibility_flag != _ON ) {
/*20100223
					$this->mobileAction->delMenuDetailByPageId( $page['page_id'] );
					if( !$this->mobileAction->insMenuDetailByPageId( $page['page_id'], _OFF ) ) {
						return 'error';
					}
*/
$this->mobileAction->insMenuDetailByPageId( $page['page_id'], 2 );
				}
				// ノードの場合は
				if( $page['node_flag'] == _ON ) {
					// このページのVisibilityをチェック
					$where_params = array( "page_id"=>$page['page_id'] );
					$chk_result = $this->mobileView->getMenuDetail( $where_params );
					if( isset( $chk_result[0] ) ) {
						$recursive_visibility_flag = $chk_result[0]['visibility_flag'];
					}
					else {
						$recursive_visibility_flag = _ON;
					}

					// 再帰
					$this->allPageIntegrity( $page['page_id'], $recursive_visibility_flag );
				}
			}
		}
	}
}
?>
