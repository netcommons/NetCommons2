<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 対象ルームのチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_LocationSelectRoom extends Validator
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
		if (empty($attributes["allroom_flag"])) {
			return;
		}

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		
		if (empty($select_room)) {
			$select_room = array();
			$request->setParameter("select_room", $select_room);
			return;
		}
		foreach ($select_room as $i=>$room_id) {
			if (!in_array($room_id, $attributes["room_id_arr"])) {
				return $errStr;
			}
		}
		$request->setParameter("select_room", $select_room);
    }
}
?>
