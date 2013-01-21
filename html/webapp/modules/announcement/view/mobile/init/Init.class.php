<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * お知らせメイン表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Announcement_View_Mobile_Init extends Action
{
	// 値をセットするため
	var $announcements = null;

    /**
     * execute実行
     *
     * @access  public
     */
	function execute()
	{
		return 'success';
	}
}
?>
