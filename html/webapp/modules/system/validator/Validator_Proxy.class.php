<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * サーバProxyチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_Validator_Proxy extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値(emai, code_date)
     *                  
     * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$proxy_mode = $attributes[0];
    	$proxy_host = $attributes[1];
    	$proxy_port = $attributes[2];
    	
    	if(isset($proxy_mode) && intval($proxy_mode) == _ON) {
    		// ONならば必須チェック
    		if (strlen($proxy_host) == 0) {
    			return sprintf(_REQUIRED, SYSTEM_PROXY_HOST_NAME);
            }
            if (strlen($proxy_port) == 0) {
    			return sprintf(_REQUIRED, SYSTEM_PROXY_PORT_NUM);
            }
            if(intval($proxy_port) < SYSTEM_PROXY_PORT_NUM_MIN || intval($proxy_port) > SYSTEM_PROXY_PORT_NUM_MAX) {
            	return sprintf(_NUMBER_ERROR, SYSTEM_PROXY_PORT_NUM, SYSTEM_PROXY_PORT_NUM_MIN, SYSTEM_PROXY_PORT_NUM_MAX);
            }
    	}
    	return;
    }
}
?>
