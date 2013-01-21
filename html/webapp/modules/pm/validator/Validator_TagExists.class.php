<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タグ名チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_TagExists extends Validator
{
    /**
     * タグ名チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$tag_edit_id = $attributes["tag_id"];
		$tag_name = trim($attributes["tag_name"], "\t\n \r\0\x0B");
		
		$container =& DIContainerFactory::getContainer();
        $pmView =& $container->getComponent("pmView");
		$tag_id = $pmView->getTagByName($tag_name);
		if (!empty($tag_id) && ($tag_id != $tag_edit_id)) {
				return $errStr;
		}

        return;
    }
}
?>
