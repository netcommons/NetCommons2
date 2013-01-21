<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 自動スライドデータチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_DisplaySlide extends Validator
{
    /**
     * 自動スライドデータチェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$photoalbum = $attributes["photoalbum"];
		if ($photoalbum["display"] == PHOTOALBUM_DISPLAY_LIST) {
			return;
		}
		
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
 		$errStr = $smartyAssign->getLang("photoalbum_no_album");
 		
		$request =& $container->getComponent("Request");
		$request->setParameter("album_id", $photoalbum["display_album_id"]);
		
 		$photoalbumView =& $container->getComponent("photoalbumView");
		$album = $photoalbumView->getAlbum();
		if (empty($album)) {
			return $errStr;
		}
 		if ($photoalbum["photoalbum_id"] != $album["photoalbum_id"]) {
 			return $errStr;
 		}
 		if ($album["public_flag"] != _ON) {
 			return $errStr;
 		}
 		
		if (!$photoalbumView->photoExists()) {
			$errStr = $smartyAssign->getLang("photoalbum_no_photo");
			return $errStr;
		}

        return;
    }
}
?>