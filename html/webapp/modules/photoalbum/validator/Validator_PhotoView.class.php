<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 写真参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_PhotoView extends Validator
{
    /**
     * 写真参照権限チェックバリデータ
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
        $photoalbumView =& $container->getComponent("photoalbumView");

		$photo = $photoalbumView->getPhoto();
		if (empty($photo)) {
        	return $errStr;
        }

        if ($photo["photoalbum_id"] != $attributes["photoalbum_id"]) {
        	return $errStr;
        }
		        
        if ($photo["album_id"] != $attributes["album_id"]) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
    	$request->setParameter("photo", $photo);

        return;
    }
}
?>