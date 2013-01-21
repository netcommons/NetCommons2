<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム写真表示携帯版アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Mobile_Photo extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;
	var $module_id = null;
	var $block_id = null;
	var $photoalbum_id = null;
	var $album_id = null;
	var $photo_id = null;
	var $pageNumber = null;
	var $seq = null;

	// 使用コンポーネントを受け取るため
	var $photoalbumView = null;
	var $request = null;

	// validatorから受け取るため
	var $album = null;

	// 値をセットするため
	var $photo = null;
	var $comments = null;

	/**
	 * フォトアルバム写真表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->request->setParameter('album_id', $this->album_id);
		$this->photo = $this->photoalbumView->getPhotoForMobile($this->seq, $this->album['photo_count']);
		if ($this->photo === false) {
			return 'error';
		}

		$this->comments = $this->photoalbumView->getComments();

		return 'success';
	}
}
?>