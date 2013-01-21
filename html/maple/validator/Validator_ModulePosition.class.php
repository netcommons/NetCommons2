<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール配置チェック(authorities_modules_link or pages_modules_link)
 * ページID(int),モジュールID(int)を引数にとり、そのモジュールがそのページに配置可能かどうかチェックする
 * 
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Validator_ModulePosition extends Validator
{
    /**
     * モジュール配置チェック
     *
     * @param   mixed   $attributes チェックする値(module_id or module_id_array)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$getdata =& $container->getComponent("GetData");
    	$pagesView =& $container->getComponent("pagesView");
    	$authoritiesView =& $container->getComponent("authoritiesView");
    	$session =& $container->getComponent("Session");
    	
    	$page_id = intval($attributes[0]);
        $module_id = intval($attributes[1]);
        if($page_id != 0) {
        	//page_id=0ならばチェックしない
	        $pages = $getdata->getParameter("pages");
	        if(!isset($pages[$page_id])) {
	        	$page =& $pagesView->getPageById($page_id);
	        } else {
	        	$page =& $pages[$page_id];
	        }
	        if(!isset($page) || count($page) == 0) {
	        	return $errStr;	
	        }
	        
	        if($page['private_flag'] == _ON) {
	        	$_role_auth_id = $session->getParameter("_role_auth_id");
	        	$module =& $authoritiesView->getAuthoritiesModulesLink(array("role_authority_id" => $_role_auth_id, "module_id" => $module_id));
	        } else {
	        	$module =& $pagesView->getPageModulesLink(array("room_id" => $page['room_id'], "module_id" => $module_id));
	        }
	    	if(!is_array($module) || count($module) == 0) {
	        	return $errStr;	
	        }
        }
    }
}
?>