<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限の存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_Validator_Existence extends Validator
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
		
		//0であれば新規作成なのでエラーとしない
		$role_authority_id = intval($attributes);
		if ($role_authority_id == 0) { return; }

		// 権限管理データ取得
	   	$authoritiesView =& $container->getComponent("authoritiesView");
		$auth =& $authoritiesView->getAuthorityById($role_authority_id);
		if ($auth===false || !isset($auth['role_authority_id'])) {
			return $errStr;	
		}
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
	    BeanUtils::setAttributes($action, array("authority"=>$auth));
    	return;
    }
}
?>
