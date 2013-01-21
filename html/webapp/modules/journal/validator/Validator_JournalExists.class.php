<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日誌存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Validator_JournalExists extends Validator
{
    /**
     * 日誌存在チェックバリデータ
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

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();		
		if (empty($attributes["journal_id"]) &&
				($actionName == "journal_view_edit_create" ||
					$actionName == "journal_action_edit_create")) {
			return;
		}

        $journalView =& $container->getComponent("journalView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["journal_id"])) {
        	$attributes["journal_id"] = $journalView->getCurrentJournalId();
        	$request->setParameter("journal_id", $attributes["journal_id"]);
		}

		if (empty($attributes["journal_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $journalView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$journalView->journalExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>