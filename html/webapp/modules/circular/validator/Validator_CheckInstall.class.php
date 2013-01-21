<?php

/**
 * プライベートルーム設置チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_CheckInstall extends Validator
{
	/**
	 * プライベートルーム設置チェックバリデータクラス
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr	 エラー文字列
	 * @param   array   $params	 オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");

		if($session->getParameter("_user_id") == "0") {
			return;
		}

		$_myroomPage = $session->getParameter("_self_myroom_page");
		$_privateRoomId = $_myroomPage["page_id"];

		$request =& $container->getComponent("Request");
		$room_id = $request->getParameter("room_id");
		if ($_privateRoomId == $room_id) {
			return $errStr;
		}

		return;
	}
}
?>
