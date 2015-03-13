<?php

/**
 * 回覧先チェックバリデータクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Validator_ReceiveUser extends Validator
{
	/**
	 * 回覧先チェックバリデータ
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
		$actionChain =& $container->getComponent('ActionChain');
		$actionName = $actionChain->getCurActionName();

		if (empty($attributes)) {
			if ($actionName === 'circular_view_main_users') {
				return;
			}
			return $errStr;
		}
		$db =& $container->getComponent('DbObject');

		$params = explode(',', $attributes);
		$inClauseValue = str_repeat(",?", count($params));
		$sql = "SELECT user_id" .
				" FROM {users}" .
				" WHERE user_id IN (" . substr($inClauseValue, 1) . ")";
		$users = $db->execute($sql, $params);
		if ($users === false) {
			return _INVALID_INPUT;
		}
		$receiveUserIdArr = array();
		foreach ($users as $user) {
			$receiveUserIdArr[] = $user['user_id'];
		}
		$request =& $container->getComponent('Request');
		$request->setParameter('receive_user_ids', $receiveUserIdArr);

		return;
	}
}
?>
