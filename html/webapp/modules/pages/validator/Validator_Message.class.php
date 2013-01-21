<?php

/**
 * プライベートメッセージチェックバリデータクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_Validator_Message extends Validator
{
	/**
	 * プライベートメッセージチェックバリデータクラス
	 *   Filterとして行うべきだが、実装していないためValidatorとして実装
	 *
	 * @param   mixed   $attributes チェックする値(配列の場合あり)
	 * @param   string  $errStr     エラー文字列
	 * @param   array   $params     (使用しない)
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent('Session');
		$user_id = $session->getParameter('_user_id');
		if (empty($user_id)) {
			return;
		}

		$modulesView =& $container->getComponent('modulesView');
		$pm_module = $modulesView->getModuleByDirname('pm');
		if(!isset($pm_module['module_id'])) {
			return;
		}

		if ($session->getParameter('_user_auth_id') < _AUTH_GENERAL) {
			return;
		}

		$authoritiesView =& $container->getComponent('authoritiesView');
		$_role_auth_id = $session->getParameter('_role_auth_id');
		$where_params = array(
			'{modules}.module_id' => $pm_module['module_id']
		);
		$authorities = $authoritiesView->getAuthoritiesModulesLinkByAuthorityId($_role_auth_id, $where_params);
		if($authorities === false || !isset($authorities[0]) || $authorities[0]['authority_id'] === null) {
			return;
		}

		$renderer =& SmartyTemplate::getInstance();
		$renderer->assign('_pm_use_flag', _ON);

		$commonMain =& $container->getComponent('commonMain');
		$request =& $container->getComponent('Request');
		require_once WEBAPP_DIR . '/modules/pm/config/define.inc.php';
		$pmView =& $commonMain->registerClass(WEBAPP_DIR . '/modules/pm/components/View.class.php', 'Pm_Components_View', 'pmView');
		$request->setParameter('filter', PM_FILTER_UNREAD);
		$new_message_count = $pmView->getMessageCount();
		$request->setParameter('filter', '');
		$renderer->assign('_pm_new_message_count', $new_message_count);

		return;
	}
}
?>