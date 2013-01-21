<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 掲示板定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
 
// 表示方法
define("BBS_DISPLAY_TOPIC_VALUE", 0);	// 根記事一覧
define("BBS_DISPLAY_NEWEST_VALUE", 1);	// 最新記事スレッド
define("BBS_DISPLAY_OLD_VALUE", 2);	// 過去記事一覧
define("BBS_DISPLAY_ALL_VALUE", 3);	// 全件一覧

// 展開方法
define("BBS_EXPAND_THREAD_VALUE", 0);	// スレッド
define("BBS_EXPAND_FLAT_VALUE", 1);		// フラット

// 記事状態
define("BBS_STATUS_RELEASED_VALUE", 0);		// 公開中
define("BBS_STATUS_TEMPORARY_VALUE", 1);		// 一時保存中
define("BBS_STATUS_BEFORE_RELEASED_VALUE", 2);	// 公開前

//携帯の記事タイトル
define("BBS_MOBILE_POST_BODY_LEN", 32);

define("BBS_PREFIX_REFERENCE", "bbs_reference");

define("BBS_SUBJECT_TRUNCATE_LENGTH", 32);
?>