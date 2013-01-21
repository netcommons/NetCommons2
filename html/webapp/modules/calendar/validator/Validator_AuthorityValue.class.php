<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限設定チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_AuthorityValue extends Validator
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
		$request_default_auth = $attributes[0];
		$request_add_auth = $attributes[1];
		
		$add_authority = array();
		if (!empty($request_add_auth)) {
			foreach ($request_add_auth as $room_id=>$auth_arr) {
				$auth_id = intval(min(array_keys($auth_arr)));
				if ($auth_id > 0) {
		 			$add_authority[$room_id] = $auth_id;
				} else {
					return $errStr;
				}
			}
		}
		
    	$container =& DIContainerFactory::getContainer();
 		$request =& $container->getComponent("Request");
		$request->setParameter("add_authority", $add_authority);
    }
}
?>
