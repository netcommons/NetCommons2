<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム参加者インポート時
 * 対象ルームIDチェック
 * 引数として渡ってきていないときは、セッションから取り出しリクエストに入れる
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Validator_ImportRoomIdCheck extends Validator
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

		$parent_page_id = intval($attributes[0]);
		$edit_current_page_id = intval($attributes[1]);
		if($parent_page_id!==false && $edit_current_page_id!=false) {
			return;
		}

		$parent_page_id = $session->getParameter(array('room', 'import', 'parent_page_id'));
		if(is_null($parent_page_id)) {
			return $errStr;
		}
		$edit_current_page_id = $session->getParameter(array('room', 'import', 'edit_current_page_id'));
		if(is_null($edit_current_page_id)) {
			return $errStr;
		}

		$request->setParameter('parent_page_id', $parent_page_id);
		$request->setParameter('edit_current_page_id', $edit_current_page_id);

		return;
    }
}
?>