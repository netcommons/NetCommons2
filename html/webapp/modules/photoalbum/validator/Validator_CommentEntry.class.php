<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント登録権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_CommentEntry extends Validator
{
    /**
     * コメント登録権限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
        if (empty($attributes["comment_id"])) {
        	return;
        }

		$container =& DIContainerFactory::getContainer();
        $photoalbumView =& $container->getComponent("photoalbumView");
		$comment = $photoalbumView->getComment();
    	if (empty($comment)) {
    		return $errStr;
    	}

		if (!$comment["edit_authority"]) {
			return $errStr;
		}

        return;
    }
}
?>