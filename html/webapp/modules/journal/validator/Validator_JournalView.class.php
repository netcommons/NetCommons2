<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日誌照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Validator_JournalView extends Validator
{
    /**
     * 日誌参照権限チェックバリデータ
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
		$auth_id = $session->getParameter("_auth_id");
		
		$request =& $container->getComponent("Request");
		$prefix_id_name = $request->getParameter("prefix_id_name");
		
		if ($auth_id < _AUTH_CHIEF &&
				$prefix_id_name == JOURNAL_REFERENCE_PREFIX_NAME.$attributes['journal_id']) {
			return $errStr;
		}
		
        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($prefix_id_name) &&	($actionName == "journal_view_main_init" || $actionName == "journal_view_main_detail")) {
			$request->setParameter("theme_name", "system");
		}

        $journalView =& $container->getComponent("journalView");
		if (empty($attributes['journal_id'])) {
			$journal_obj = $journalView->getDefaultJournal();
		} elseif ($prefix_id_name == JOURNAL_REFERENCE_PREFIX_NAME.$attributes['journal_id'] 
					|| $actionName == "journal_view_edit_modify"
					|| $actionName == "journal_view_edit_category"
					|| $actionName == 'journal_action_edit_create') {
			$journal_obj = $journalView->getJournal();
		} else {
			$journal_obj = $journalView->getCurrentJournal();
		}

		if (empty($journal_obj)) {
        	return $errStr;
        }

		$request->setParameter("journal_obj", $journal_obj);
 
        return;
    }
}
?>
