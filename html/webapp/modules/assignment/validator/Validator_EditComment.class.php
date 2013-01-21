<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント編集バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_EditComment extends Validator
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
		if (empty($attributes["assignment"])) {
			return $errStr;
		}

		if ($attributes["report"]["status"] != ASSIGNMENT_STATUS_REREASED) {
			return $errStr;
		}

		if (empty($attributes["comment_id"])) {
			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
        $assignmentView =& $container->getComponent("assignmentView");
		$comment = $assignmentView->getComment();
        if (empty($comment)) {
        	return $errStr;
        }

		if (!$comment["hasCommentEditAuthority"]) {
			return $errStr;
		}

        return;
    }
}
?>
