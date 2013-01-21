<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録フォーム定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
 
define("REGISTRATION_RCPT_TO_SEPARATOR", ",");

define("REGISTRATION_TYPE_TEXT", "1");
define("REGISTRATION_TYPE_CHECKBOX", "2");
define("REGISTRATION_TYPE_RADIO", "3");
define("REGISTRATION_TYPE_SELECT", "4");
define("REGISTRATION_TYPE_TEXTAREA", "5");
define("REGISTRATION_TYPE_EMAIL", "6");
define("REGISTRATION_TYPE_FILE", "7");

define("REGISTRATION_OPTION_SEPARATOR", "|");
define("REGISTRATION_ERROR_SEPARATOR", ":");

define('REGISTRATION_ALBUM_SORT_DESCEND', 'descend');	// 新着順
define('REGISTRATION_ALBUM_SORT_ASCEND', 'ascend');		// 登録順

define('REGISTRATION_MAX_LIMIT_NUMBER', 100000);
?>