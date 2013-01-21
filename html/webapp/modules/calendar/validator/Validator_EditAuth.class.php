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
class Calendar_Validator_EditAuth extends Validator
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
 		$request =& $container->getComponent("Request");
 		$session =& $container->getComponent("Session");

		$pagesView =& $container->getComponent("pagesView");
		$room_id_arr = $request->getParameter("room_id_arr");

		$_user_auth_id = $session->getParameter("_user_auth_id");
    	if ($_user_auth_id == _AUTH_ADMIN) {
	    	$room_id_arr[] = CALENDAR_ALL_MEMBERS_ID;
    	}

		$add_authority_arr = array_keys($attributes["add_authority"]);

		$diff_arr = array_diff($add_authority_arr, $room_id_arr);
		if (count($diff_arr) > 0) {
			return $errStr;
		}

		foreach ($room_id_arr as $i=>$room_id) {
			if (isset($attributes["add_authority"][$room_id])) {
				$add_authority = $attributes["add_authority"][$room_id];
			} else {
				if ($room_id == CALENDAR_ALL_MEMBERS_ID) {
					$add_authority = _AUTH_ADMIN;
				} else {
					$add_authority = _AUTH_CHIEF;
				}
			}
			$attributes["add_authority"][$room_id] = $add_authority;
		}
		$request->setParameter("add_authority", $attributes["add_authority"]);
    }
}
?>
