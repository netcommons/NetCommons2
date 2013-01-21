<?php

/**
 * 権限の値チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_CircularSightAuth extends Validator
{
	/**
	 * validate実行
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
		$db =& $container->getComponent('DbObject');
		$session =& $container->getComponent('Session');

		if ($session->getParameter('_user_auth_id') >= CIRCULAR_ALL_VIEW_AUTH) {
			return;
		}

		$request =& $container->getComponent('Request');

		$room_id = $request->getParameter('room_id');
		$circular_id = $attributes;
		$user_id = $session->getParameter('_user_id');

		$whereParams = array(
			'room_id'=> $room_id,
			'circular_id'=> $circular_id,
			'receive_user_id'=> $user_id
		);
		$circularUser = $db->countExecute('circular_user', $whereParams);

		$whereParams = array(
			'room_id'=> $room_id,
			'circular_id'=> $circular_id,
			'post_user_id'=> $user_id
		);
		$circular = $db->countExecute('circular', $whereParams);
		if($circular == 0 && $circularUser == 0) {
			return $errStr;
		}

		return;
	}
}
?>