<?php


/**
 * レスキューパスワードチェック
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_Validator_Rescue extends Validator
{
    /**
     * レスキューパスワードチェック
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$configView =& $container->getComponent("configView");
    	
    	$passwd_disabling_bip = $configView->getConfigByConfname(_SYS_CONF_MODID, 'passwd_disabling_bip');
    	if($passwd_disabling_bip === false || !isset($passwd_disabling_bip['conf_value'])) return $errStr;
    	if($passwd_disabling_bip['conf_value'] == "") {
    		return PAGES_RESCUE_NONE_EXISTS;	
    	}
    	if($passwd_disabling_bip['conf_value'] != $attributes) {
    		return $errStr;
    	}
    }
}
?>