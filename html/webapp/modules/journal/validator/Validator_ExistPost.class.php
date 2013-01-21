<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Validator_ExistPost extends Validator
{
    /**
     * [[機能説明]]
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$post_id = $attributes;
		$container =& DIContainerFactory::getContainer();
		$journalView =& $container->getComponent("journalView");

		$result = $journalView->getPostDetailData($post_id);
        if (empty($result)) {
	       	return $errStr;
        }

        $request =& $container->getComponent("Request");
        $request->setParameter("post", $result[0]);

        return;
    }
}
?>
