<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事編集画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Main_Post_Edit extends Action
{
	// 使用コンポーネントを受け取るため
	var $session = null;

    // validatorから受け取るため
	var $bbs = null;
	var $post = null;

    /**
     * 記事編集画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$mobile_flag = $this->session->getParameter('_mobile_flag');
		if ($mobile_flag == _ON) {
			$this->session->removeParameter('bbs_current_mobile_image');
			if (preg_match('/<img[^>]+class\s*=\s*["\'][^>]*' . MOBILE_IMAGE . '[^>]+>/u', $this->post['body'], $match) > 0) {
				$this->post['mobile_image'] = $match[0];
				$this->session->setParameter('bbs_current_mobile_image', $this->post['mobile_image']);
			}
		}

		return "success";
    }
}
?>
