<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */



/**
 * 権限のチェック
 * <=_AUTH_CHIEF等をパラメータに設定
 *
 * @package     NetCommons.validator
 * @author      Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Validator_Authcheck extends Validator
{
    /**
     * 権限が一定以下かどうかのチェック
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     string session_name + operator + authority_id 
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
        $container =& DIContainerFactory::getContainer();
        //$session =& $container->getComponent("Session");
        $common =& $container->getComponent("commonMain");
        
        $page_id = null;
        $user_id = null;
        if($attributes != "") {
        	if(is_array($attributes)) {
        		//page_id, user_id等の指定あり
        		//page_id,user_idの順で指定すること
        		$page_id = intval($attributes[0]);
        		$user_id = $attributes[1];
        	} else {
        		//page_id指定あり
        		$page_id = intval($attributes);
        	}
        	if ($page_id == 0 || $page_id == "") {
        		//page_id指定がない場合、スルー
        		return;
        	}
        }
        
        foreach($params as $param) {
        	if(!$common->isResultByOperatorString($param, $page_id, $user_id)) {
        		return $errStr;		
        	}
        }
        return;
    }
}
?>
