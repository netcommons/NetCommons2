<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示するルームチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_SelectRoom extends Validator
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
 		$session =& $container->getComponent("Session");

    	$user_id = $session->getParameter("_user_id");
    	if (!empty($user_id)) {
	    	$attributes["room_id_arr"][] = CALENDAR_ALL_MEMBERS_ID;
    	}

		if (!empty($attributes["not_enroll_room"])) {
			foreach ($attributes["not_enroll_room"] as $i=>$room_id) {
				if (!in_array($room_id, $attributes["room_id_arr"])) { return _INVALID_INPUT; }
			}
		}
		
		if (!empty($attributes["enroll_room"])) {
			foreach ($attributes["enroll_room"] as $i=>$room_id) {
				if (!in_array($room_id, $attributes["room_id_arr"])) { return _INVALID_INPUT; }
			}
		} elseif (intval($attributes["myroom_flag"]) == _ON) {
		} else {
			return $errStr;
		}

 		$request =& $container->getComponent("Request");
 		$request->setParameter("room_id_arr", $attributes["room_id_arr"]);
    }
}
?>
