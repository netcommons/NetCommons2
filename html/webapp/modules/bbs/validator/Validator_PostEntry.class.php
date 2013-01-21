<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 根記事投稿権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Validator_PostEntry extends Validator
{
    /**
     * 根記事投稿権限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["post_id"]) &&
				empty($attributes["parent_id"]) &&
				$attributes["bbs"]["topic_authority"]) {
        	return;
		}
		
		if (!empty($attributes["post_id"])) {
			$postID = $attributes["post_id"];
		} elseif (!empty($attributes["parent_id"])) {
			$postID = $attributes["parent_id"];
		}
				
		$container =& DIContainerFactory::getContainer();
        $bbsView =& $container->getComponent("bbsView");
		if (!$bbsView->postExists($postID)) {
        	return $errStr;
		}
		
		$post = $bbsView->getPost($postID);
		if (empty($post)) {
        	return $errStr;
		}
		
		if (!empty($attributes["parent_id"])) {
			if (!$post["reply_authority"]) {
	        	return $errStr;
			}
			
			return;
		}
		
		if (!$post["edit_authority"]) {
			return $errStr;
		}
		
		if (!empty($attributes["temporary"]) && 
				$attributes["temporary"] == _ON &&
				!$post["temporary_authority"]) {
			return $errStr;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("post", $post);
		
		return;	
    }
}
?>
