<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事編集権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Validator_PostEditAuth extends Validator
{
	/**
	 * 記事編集権限チェックバリデータ
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr     エラー文字列
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		if (!empty($attributes['comment_id'])) {
			return;
		}

		if (!empty($attributes['trackback_id'])) {
			$post_id = $attributes['trackback_id'];
		} else {
			$post_id = $attributes['post_id'];
		}

		$container =& DIContainerFactory::getContainer();
		$journalView =& $container->getComponent('journalView');

		$result = $journalView->getPostDetail($post_id);
		if (empty($result)) {
			return $errStr;
		}

		$request =& $container->getComponent('Request');
		$request->setParameter('post', $result[0]);

		if(!$result[0]['has_edit_auth']) {
			return $errStr;
		}

		return;
	}
}
?>
