<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 課題データ取得バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_AssignmentView extends Validator
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

		$request =& $container->getComponent("Request");
		$prefix_id_name = $request->getParameter("prefix_id_name");

        $assignmentView =& $container->getComponent("assignmentView");

		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");

		if ($authID < _AUTH_CHIEF &&
				($prefix_id_name == ASSIGNMENT_REFERENCE_PREFIX_NAME.$attributes["assignment_id"] || 
				$prefix_id_name == ASSIGNMENT_SUBMITTER_PREFIX_NAME.$attributes["assignment_id"] || 
				$prefix_id_name == ASSIGNMENT_SUBMITTERS_PREFIX_NAME.$attributes["assignment_id"])) {
			return $errStr;
		}

		if ($actionName == "assignment_view_edit_modify" || $actionName == "assignment_action_edit_modify" ||
			$prefix_id_name == ASSIGNMENT_REFERENCE_PREFIX_NAME.$attributes["assignment_id"] || 
			$prefix_id_name == ASSIGNMENT_SUMMARY_PREFIX_NAME.$attributes["assignment_id"] || 
			$prefix_id_name == ASSIGNMENT_SUBMITTER_PREFIX_NAME.$attributes["assignment_id"] || 
			$prefix_id_name == ASSIGNMENT_SUBMITTERS_PREFIX_NAME.$attributes["assignment_id"]) {
			$assignment = $assignmentView->getAssignment();
		} else {
			$assignment = $assignmentView->getCurrentAssignment();
		}

		if (empty($assignment)) {
        	return $errStr;
        }

		$request->setParameter("assignment", $assignment);

		$response = $assignmentView->getResponseLastTime();
		if (empty($response)) {
			$response = false;
			return $response;
		}
		$request->setParameter("response", $response);

		$hasAnswerAuthority = $assignmentView->hasAnswerAuthority();
		$request->setParameter("hasAnswerAuthority", $hasAnswerAuthority);

		$hasSummaryAuthority = $assignmentView->hasSummaryAuthority();
		$request->setParameter("hasSummaryAuthority", $hasSummaryAuthority);

		$hasSubmitListView = $assignmentView->hasSubmitListView();
		$request->setParameter("hasSubmitListView", $hasSubmitListView);

		return;
    }
}
?>
