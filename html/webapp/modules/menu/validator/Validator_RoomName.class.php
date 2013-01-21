<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム名称チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Validator_RoomName extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$main_page_id = $attributes[0];
    	$room_name = $attributes[1];
    	$container =& DIContainerFactory::getContainer();
    	$pagesView =& $container->getComponent("pagesView");
    	$main_page =& $pagesView->getPageById($main_page_id);
    	if($main_page === false || !isset($main_page['page_id'])) {
    		return $errStr;	
    	}
    	if($main_page['page_id'] == $main_page['room_id']) {
    		$pages =& $pagesView->getPages(array("parent_id"=>$main_page['parent_id'], "page_id=room_id"=>null, "page_name"=> $room_name, "page_id != ".$main_page_id => null));
	    	if($pages === false || isset($pages[0])) {
	    		return $errStr;	
	    	}	
    	}
    }
}
?>
