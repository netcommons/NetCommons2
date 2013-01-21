<?php
/**
 * ブロック表示順変更時チェック
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Validator_Chgseq extends Validator
{
    /**
     * ブロック表示順変更時チェック
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$drag_page_id = $attributes['drag_page_id'];
    	$drop_page_id = $attributes['drop_page_id'];
    	$position = $attributes['position'];
    	
    	$container =& DIContainerFactory::getContainer();
    	$pagesView =& $container->getComponent("pagesView");
    	$session =& $container->getComponent("Session");
    	
    	$move_page =& $pagesView->getPageById($drag_page_id);
    	if($move_page === false || !isset($move_page['page_id'])) {
			return $errStr;	
		}
		$page =& $pagesView->getPageById($drop_page_id);
		if($page === false || !isset($page['page_id'])) {
			return $errStr;	
		}
		
		if($position == "inside" && $page['node_flag'] != _ON) {
			//カテゴリの内部に移動したはずなのに、移動先がページである場合
			return $errStr;	
		}
		//ルームの内部移動
		if($move_page['page_id'] == $move_page['room_id'] && $position == "inside") {
			return $errStr;	
		}
		
		//ルーム内への移動
		if($page['page_id'] == $page['room_id'] && $position == "inside") {
			return $errStr;	
		}
		
		//ルートIDが等しくない
		if($move_page['root_id'] != $page['root_id']) {
			return $errStr;	
		}
		//
		// 移動元チェック
		//
		if($move_page['thread_num'] == 0) {
			// 深さ０ならば、管理者のみ変更を許す
			if($session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
				return $errStr."a";	
			}
		} else if($move_page['thread_num'] == 1 && $move_page['space_type'] == _SPACE_TYPE_GROUP && $move_page['private_flag'] == _OFF) {
			// グループルーム
			// 管理者のみ変更を許す
			if($session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
				return $errStr;	
			}
		} else if($move_page['page_id'] == $move_page['room_id']) {
			// その他のルーム	
			// 親のルームの権限が主担ならば許す
			$parent_page =& $pagesView->getPageById($move_page['parent_id']);
			if($parent_page === false || !isset($parent_page['authority_id']) || $parent_page['authority_id'] < _AUTH_CHIEF) {
				return $errStr;	
			}
		} else {
			// ページ-カテゴリ
			$parent_page =& $pagesView->getPageById($move_page['room_id']);
			if($parent_page === false || !isset($parent_page['authority_id']) || $parent_page['authority_id'] < _AUTH_CHIEF) {
				return $errStr;	
			}
			//if($move_page['authority_id'] < _AUTH_CHIEF) {
			//	return $errStr;	
			//}
		}
		
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
    	BeanUtils::setAttributes($action, array("move_page"=>$move_page,"page"=>$page));
    }
}
?>