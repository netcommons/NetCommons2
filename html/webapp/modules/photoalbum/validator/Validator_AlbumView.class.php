<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アルバム参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_AlbumView extends Validator
{
    /**
     * アルバム参照権限チェックバリデータ
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

		if (empty($attributes["album_id"])) {
			$album = $photoalbumView->getDefaultAlbum();
		} else {
			$album = $photoalbumView->getAlbum();
		}
		if (empty($album)) {
        	return $errStr;
        }

        if (!empty($attributes["album_id"])
        		&& $album["photoalbum_id"] != $attributes["photoalbum_id"]) {
        	return $errStr;
        }
		        
		$request =& $container->getComponent("Request");
    	$request->setParameter("album", $album);

        return;
    }
}
?>