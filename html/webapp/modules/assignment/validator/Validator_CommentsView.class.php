<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメントデータ取得バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_CommentsView extends Validator
{
    /**
     * validate処理
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

		if (empty($attributes["report_id"]) && $actionName == "assignment_view_main_init") {
			return;
		} elseif (empty($attributes["report_id"])) {
			return $errStr;
		}
		$assignmentView =& $container->getComponent("assignmentView");
		$comments = $assignmentView->getComments($attributes["report_id"]);
		if ($comments === false) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("comments", $comments);
		$request->setParameter("commentCount", count($comments));

        return;
    }
}
?>
