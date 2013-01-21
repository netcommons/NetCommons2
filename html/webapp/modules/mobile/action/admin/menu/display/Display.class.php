<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  表示形式変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Mobile_Action_Admin_Menu_Display extends Action
{
	// リクエストパラメータを受け取るため
	var $display_type = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $configAction = null;
	var $pagesView = null;
	var $mobileView = null;
	var $mobileAction = null;

	var $module_id = null;

	/**
	 * 表示形式変更アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if( $this->configAction->updConfigValue( $this->module_id, MOBILE_MENU_TYPE, $this->display_type ) != true ) {
			return 'error';
		}
		// ツリー型への切り替え時は、親子のVisibilityの関係性の整合性を取る
		if( $this->display_type == MOBILE_MENU_DISPLAY_TREE  ) {

		$where_params = array(
			'space_type IN (' . _SPACE_TYPE_PUBLIC . ',' . _SPACE_TYPE_GROUP . ')' => null,
			'private_flag' => _OFF,
			'display_position' => _DISPLAY_POSITION_CENTER
		);
		$order_params = array("thread_num"=>"ASC");
		$pages =& $this->pagesView->getPages($where_params, $order_params, 0, 0, array($this,"_fetchGetPageTree"));
			$this->allPageIntegrity( $pages, 0, _ON );
		}
		return 'success';
	}
	function _fetchGetPageTree($result) 
	{
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[ $row['parent_id']][$row['page_id']] = $row;
		}
		return $ret;
	}

	function allPageIntegrity( $pages, $root_id, $visibility_flag )
	{
		if(isset($pages[$root_id])) {
			foreach($pages[$root_id] as $page) {

				if( $visibility_flag != _ON ) {
					$this->mobileAction->insMenuDetailByPageId( $page['page_id'], 2 );
				}
				// ノードの場合は
				if( $page['node_flag'] == _ON ) {
					if( $page['thread_num'] != 0 ) {
						// このページのVisibilityをチェック
						$where_params = array( "page_id"=>$page['page_id'] );
						$chk_result = $this->mobileView->getMenuDetail( $where_params );
						if( isset( $chk_result[0] ) ) {
							$recursive_visibility_flag = $chk_result[0]['visibility_flag'];
						}
						else {
							$recursive_visibility_flag = _ON;
						}
					}
					else { // スペースのときは特別に常にONで考えて流す
						$recursive_visibility_flag = _ON;
					}

					// 再帰
					if( isset( $pages[ $page['page_id'] ] ) ) {

						$this->allPageIntegrity( $pages, $page['page_id'], $recursive_visibility_flag );
					}
				}
			}
		}
	}
}
?>
