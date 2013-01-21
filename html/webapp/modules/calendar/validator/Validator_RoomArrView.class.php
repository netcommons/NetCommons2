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
class Calendar_Validator_RoomArrView extends Validator
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
 		$request =& $container->getComponent("Request");

 		$calendarView =& $container->getComponent("calendarView");
 		if (!isset($attributes["display_type"])) {
 			$attributes["display_type"] = null;
 		}

    	$calendar_block = $calendarView->getBlock($attributes["display_type"]);
    	if ($calendar_block === false) {
    		return $errStr;
    	}
		$request->setParameter("calendar_block", $calendar_block);
		
		$room_arr = $attributes["room_arr"];
    	$user_id = $session->getParameter("_user_id");
    	if (!empty($user_id)) {
	    	$room_arr[0][0][0] = array(
	    		"page_id" => CALENDAR_ALL_MEMBERS_ID,
	    		"parent_id" => 0,
	    		"page_name" => CALENDAR_ALL_MEMBERS_LANG,
	    		"thread_num" => 0,
	    		"space_type" => _SPACE_TYPE_UNDEFINED,
	    		"private_flag" => _OFF,
	    		"authority_id" => $session->getParameter("_user_auth_id")
	    	);
    	}
		$request->setParameter("room_arr", $room_arr);

		if ($calendar_block["select_room"] == _ON) {
	    	$selectLooms = $calendarView->getSelectRoomList();
	    	if ($selectLooms === false) {
	    		return $errStr;
	    	}
			$request->setParameter("enroll_room_arr", $selectLooms["enroll_room_arr"]);
		}
    }
}
?>
