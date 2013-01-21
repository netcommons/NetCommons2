<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバムコメント登録画面携帯版アクションクラス
 *
 * @package     NetCommons
 * @author      Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2010 AllCreator Co., Ltd.
 * @project     NC Support Project, provided by AllCreator Co., Ltd.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */
class Photoalbum_View_Mobile_Comment extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;
	var $block_id = null;
	var $album_id = null;
	var $photo_id = null;
	var $seq = null;
	var $comment_id = null;

	// 使用コンポーネントを受け取るため
	var $photoalbumView = null;

	// 値をセットするため
	var $comment = null;

	/**
	 * フォトアルバム写真コメント表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if($this->comment_id) {
			$this->comment = $this->photoalbumView->getComment();
		}
		return 'success';
	}
}
?>