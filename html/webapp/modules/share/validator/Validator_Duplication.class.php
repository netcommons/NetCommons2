<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * URL重複チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Share_Validator_Duplication extends Validator
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
    	$container =& DIContainerFactory::getContainer();
    	
        $sitesView =& $container->getComponent("sitesView");
        $url = $attributes[0];
        $old_url = $attributes[1];
        
        if($url != $old_url) {
        	$site = $sitesView->getSitesByUrl($url);
        	if($site === false) {
        		return $errStr;	
        	}
        	
        	if(isset($site['url'])) {
        		// 既に存在するURL
        		return $errStr;	
        	}
        }
	}
}
?>
