<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 投稿アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Mobile_Post extends Action
{
    // リクエストパラメータを受け取るため
	var $temporary = null;

    // 使用コンポーネントを受け取るため
    var $request = null;
	var $uploadsAction = null;

    /**
     * 投稿アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (isset($this->temporary)) {
			$this->request->setParameter("temporary", _ON);
    	} else {
			$this->request->setParameter("temporary", _OFF);
    	}

		// ファイル取り込み
		$filelist = $this->uploadsAction->uploads(_OFF, "");
		$bbs_mobile_images = array();
		foreach ($filelist as $val) {
			$bbs_mobile_images[] = '<img class="' . MOBILE_IMAGE . '" '
									. 'src=".' . INDEX_FILE_NAME
										. '?action=' . $val['action_name']
										. '&upload_id=' . $val['upload_id'] . '" />';
		}
		$this->request->setParameter('bbs_mobile_images', $bbs_mobile_images);

    	return "success";
    }
}
?>
