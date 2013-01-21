<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Main_Comment_Entry extends Action
{
	// 使用コンポーネントを受け取るため
	var $photoalbumAction = null;

	// コンポーネントを使用するため
	var $session = null;

	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $album_id = null;
	var $photo_id = null;
	var $seq = null;
	var $regist = null;
	var $cancel = null;

	/**
	 * コメント登録アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$_mobile_flag = $this->session->getParameter('_mobile_flag');
		if ($_mobile_flag && $this->cancel) {
			return 'cancel';
		}
		if (!$this->photoalbumAction->setComment()) {
			return 'error';
		}

		return 'success';
	}
}
?>
