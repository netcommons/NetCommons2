<?php


/**
 * 削除時、権限チェック
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Validator_Delete extends Validator
{
    /**
     * 削除時、権限チェック
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
		if($main_page['thread_num'] == 1 && $main_page['room_id'] == _SELF_TOPPUBLIC_ID) {
			// パブリックには1ページはないとエラーとする
			$where_params = array(
				"page_id !=".$main_page_id => null,
				"room_id" => $main_page['room_id'],
				"room_id != page_id" => null,
				"display_position" => _DISPLAY_POSITION_CENTER,
				"thread_num" => 1
			);
			$pages = $pagesView->getPages($where_params);
			if($pages === false || !isset($pages[0])) {
				// 最後の1ページ
				return $errStr;	
			}
		}
    }
}
?>