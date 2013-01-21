<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 配置ブロックがショートカットならばエラー
 *
 * @package     NetCommons.validator
 * @author      Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Validator_ModuleShortcut extends Validator
{
    /**
     * 配置ブロックがショートカットならばエラー
     *
     * @param   mixed   $attributes チェックする値(int block_id)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$block_id = $attributes;
    	$container =& DIContainerFactory::getContainer();
    	$getdata =& $container->getComponent("GetData");
    	$blocksView =& $container->getComponent("blocksView");
    	
    	$blocks = $getdata->getParameter("blocks");
        if(!isset($blocks[$block_id])) {
        	$block =& $blocksView->getBlockById($block_id);
        } else {
        	$block =& $blocks[$block_id];
        }
        
        if(!isset($block)) {
        	//ブロックがなければエラー
        	return $errStr;	
        }
        if($block['shortcut_flag'] == _ON) {
        	//ショートカットされたモジュールならばエラー
        	return $errStr;	
        }
    }
}
?>
