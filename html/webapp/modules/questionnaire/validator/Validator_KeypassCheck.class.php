<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート期限切れチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_KeypassCheck extends Validator
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
        // 使う？
		if (empty($attributes['questionnaire']['keypass_use_flag'])) {
			return;
		}

        // セッションに「チェックしろ！」というフラグが立ってる？
        $container =& DIContainerFactory::getContainer();
        $session =& $container->getComponent('Session');
        $chk_flg = $session->getParameter('questionnaire_keypass_check_flag'. $attributes['block_id']);
        if (empty($chk_flg)) {  // たってない
            return;
        }


        // 使う設定ならば
        // 入力されてる？
		if (empty($attributes['keypass_phrase'])) {
			return $errStr;
        }

        // 入力されてるなら
        // 一致してる？
		if ($attributes['questionnaire']['keypass_phrase'] != $attributes['keypass_phrase']) {
			return $errStr;
        }
        // チェックOKだったのでフラグ削除
        $session->removeParameter('questionnaire_keypass_check_flag'. $attributes['block_id']);
        return;
    }
}
?>
