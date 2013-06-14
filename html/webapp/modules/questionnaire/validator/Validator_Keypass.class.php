<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケートキーフレーズチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_Keypass extends Validator
{
    /**
     * アンケートキーフレーズチェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		// キーフレーズの使用フラグとキーフレーズ
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent('Request');

        // 使用しない
		if (empty($attributes['keypass_use_flag'])) {
            // 空にしてノーチェック
			$request->setParameter('keypass_phrase', '');
			return;
		}
		
		if (!isset($attributes['keypass_phrase'])) {
			return $errStr;
		}
		if (is_null($attributes['keypass_phrase'])) {
			return $errStr;
		}
		if ($attributes['keypass_phrase']=='') {
			return $errStr;
		}
		
        return;
    }
}
?>
