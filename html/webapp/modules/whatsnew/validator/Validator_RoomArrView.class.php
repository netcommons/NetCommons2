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
class Whatsnew_Validator_RoomArrView extends Validator
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
    	$whatsnewView =& $container->getComponent("whatsnewView");

		if (isset($attributes["display_type"])) {
			$display_type = intval($attributes["display_type"]);
		} else {
	    	$display_type = $session->getParameter(array("whatsnew", $attributes["block_id"], "display_type"));
		}
		if (isset($attributes["display_days"])) {
			$display_days = intval($attributes["display_days"]);
		} else {
	    	$display_days = $session->getParameter(array("whatsnew", $attributes["block_id"], "display_days"));
		}
		if (isset($attributes["display_number"])) {
			$display_number = intval($attributes["display_number"]);
		} else {
	    	$display_number = $session->getParameter(array("whatsnew", $attributes["block_id"], "display_number"));
		}

		if($attributes['block_id'] != 0) {
    		$whatsnew_obj = $whatsnewView->getBlock($attributes["block_id"], $display_type, $display_days, $display_number);
		} else if($session->getParameter('_mobile_flag') == _ON) {
			$whatsnew_obj = $whatsnewView->getMobileBlock($request->getParameter('module_id'));
		} else {
			$whatsnew_obj = $whatsnewView->getDefaultBlock($request->getParameter('module_id'));
		}
		if (isset($whatsnew_obj["select_room"]) && $whatsnew_obj["select_room"] == _ON) {
    		$room_arr_flat = array();
			foreach ($attributes["room_arr_flat"] as $room_id=>$room) {
				if ($room["private_flag"] == _ON) {
					if ($whatsnew_obj["myroom_flag"] == _ON) {
						$room_arr_flat[$room_id] = $attributes["room_arr_flat"][$room_id];
						$whatsnew_obj["select_room_list"][] = $room_id;
					}
				} elseif (!empty($whatsnew_obj["select_room_list"]) && in_array($room_id, $whatsnew_obj["select_room_list"])) {
					$room_arr_flat[$room_id] = $attributes["room_arr_flat"][$room_id];
				}
			}
			$request->setParameter("room_arr_flat", $room_arr_flat);
    	}

		$request->setParameter("whatsnew_obj", $whatsnew_obj);
    }
}
?>
