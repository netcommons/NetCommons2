<?php


/**
 * 登録時、権限チェック
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Validator_Regist extends Validator
{
    /**
     * 登録時、権限チェック
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$main_page_id = $attributes;

    	$container =& DIContainerFactory::getContainer();
    	$pagesView =& $container->getComponent("pagesView");
    	$session =& $container->getComponent("Session");

    	$main_page =& $pagesView->getPageById($main_page_id);
    	if($main_page === false || !isset($main_page['page_id'])) {
			return $errStr;
		}
		if($main_page['thread_num'] == 0 && $main_page['private_flag'] == _ON) {
			// プライベートスペースならば、主担以上ならばOK
			if($main_page['authority_id'] < _AUTH_CHIEF) {
				return $errStr;
			}
		} else if($main_page['thread_num'] == 0 && (!isset($params[0]) || $params[0] != "add")) {
			// 深さ０ならば、管理者のみ変更を許す
			if($session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
				return $errStr;
			}
		} else if($main_page['page_id'] == $main_page['room_id']) {
			// その他のルーム
			if($main_page['authority_id'] < _AUTH_CHIEF) {
				return $errStr;
			}
			// 親のルームの権限が主担ならば許す
			//$parent_page =& $pagesView->getPageById($main_page['parent_id']);
			//if($parent_page === false || !isset($parent_page['authority_id']) || $parent_page['authority_id'] < _AUTH_CHIEF) {
			//	return $errStr;
			//}
		} else {
			// ページ-カテゴリ
			$parent_page =& $pagesView->getPageById($main_page['room_id']);
			if($parent_page === false || !isset($parent_page['authority_id']) || $parent_page['authority_id'] < _AUTH_CHIEF) {
				return $errStr;
			}
		}

		if($main_page['authority_id'] < _AUTH_CHIEF && $session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
			return $errStr;
		}
    }
}
?>