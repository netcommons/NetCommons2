<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タグデータバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_TagView extends Validator
{
    /**
     * タグデータバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$tag_id = $attributes["tag_id"];
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
        $pmView =& $container->getComponent("pmView");
		
		$request->setParameter("tag_id", $tag_id);
		
		if (!$pmView->getTagCount()) {
			return $errStr;
		}
		
        return;
    }
}
?>
