<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * プライベートメッセージの定数
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

define("PM_MAX_PAGE_DISPLAY", "20");
define("PM_FRONT_AND_BEHIND_LINK_CNT", 4);
define("PM_SEARCH_VISIBLE_ITEM_CNT", 12);

define("PM_NO_FLAG", 0);
define("PM_IMPORTANCE_FLAG", 1);

define("PM_READ_STATE", 1);
define("PM_UNREAD_STATE", 0);

define("PM_MESSAGE_STATE_NORMAL", 0);
define("PM_MESSAGE_STATE_TRASH", 1);
define("PM_MESSAGE_STATE_DELETE", 2);

define("PM_ASC_IMG","sort_asc.gif"); // 並べ画像
define("PM_DESC_IMG","sort_desc.gif"); // 並べ画像 

define("PM_LEFTMENU_INBOX", "0");     // 受信トレイ
define("PM_LEFTMENU_OUTBOX", "1");    // 送信済みトレイ
define("PM_LEFTMENU_STOREBOX", "2");  // 下書きトレイ
define("PM_LEFTMENU_TRASHBOX", "3");  // ごみ箱トレイ
define("PM_LEFTMENU_SEARCH", "4");    // メッセージ検索
define("PM_LEFTMENU_SETTING", "5");   // 設定変更

define("PM_ACTION_DELETE", "delete");
define("PM_ACTION_READ", "read");
define("PM_ACTION_UNREAD", "unread");
define("PM_ACTION_ADDFLAG", "addflag");
define("PM_ACTION_REMOVEFLAG", "removeflag");
define("PM_ACTION_NEWTAG", "newtag");
define("PM_ACTION_ADDTAG", "addtag");
define("PM_ACTION_REMOVETAG", "removetag");
define("PM_ACTION_RESTORE", "restore");

define("PM_FILTER_READ", "read");
define("PM_FILTER_UNREAD", "unread");
define("PM_FILTER_HAVE_FLAG", "flag");
define("PM_FILTER_NO_FLAG", "noflag");
define("PM_FILTER_TAG", "tag");
define("PM_SPLIT_CHAR","_");
define("PM_SPLIT_COLON",":");

define("PM_QUOTE_BODY_START","<br class=\"bbs_quote\" /><blockquote class=\"quote\">");
define("PM_QUOTE_BODY_END","<br /></blockquote>");
define("PM_QUOTE_SUBJECT","Re:");

define("PM_SEND_MESSAGE",1);  // 送信
define("PM_STORE_MESSAGE",2); // 下書き保存

define("PM_EDIT_MESSAGE",1);
define("PM_REPLY_MESSAGE",2);
define("PM_NEW_MESSAGE",3);

define("PM_STOREBOX_LOAD_ALL_CC", 0);

define("PM_EMAIL_TYPE", "html");
?>
