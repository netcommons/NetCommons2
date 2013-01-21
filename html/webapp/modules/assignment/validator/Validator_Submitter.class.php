<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答者データ取得バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_Submitter extends Validator
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
		$request =& $container->getComponent("Request");

		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		$userID = $session->getParameter("_user_id");

        $assignmentView =& $container->getComponent("assignmentView");

		$prefix_id_name = $request->getParameter("prefix_id_name");
		if ($prefix_id_name == ASSIGNMENT_REFERENCE_PREFIX_NAME.$attributes["assignment_id"] || 
			$prefix_id_name == ASSIGNMENT_SUMMARY_PREFIX_NAME.$attributes["assignment_id"] || 
			$prefix_id_name == ASSIGNMENT_SUBMITTER_PREFIX_NAME.$attributes["assignment_id"] || 
			$prefix_id_name == ASSIGNMENT_SUBMITTERS_PREFIX_NAME.$attributes["assignment_id"]) {
			
			$request->setParameter("reference", _ON);
		} else {
			$request->setParameter("reference", _OFF);
		}

		$submit_user_id = $assignmentView->getSubmitterID($attributes["submit_id"]);
		if ($submit_user_id === false) {
			return $errStr;
		}

		if (!empty($submit_user_id)) {
			$request->setParameter("submit_user_id", $submit_user_id);
		}

		if (empty($submit_user_id) || $submit_user_id == $userID) {
			return;
		}

		if (!$assignmentView->hasSubmitterView($submit_user_id)) {
			return $errStr;
		}

        return;
    }
}
?>
