<?php

/**
 * 回覧板定数定義
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */

define('CIRCULAR_PLAN_PUBLIC',0);
define('CIRCULAR_PLAN_MEMBERS',1);
define('CIRCULAR_PLAN_GROUP',2);
define('CIRCULAR_PLAN_PRIVATE',3);

define('CIRCULAR_ITEM_CD_EMAIL', 5);// アイテムコード(メールアドレス)

define('CIRCULAR_LIST_TYPE_UNSEEN', 0);// 未読
define('CIRCULAR_LIST_TYPE_SEEN', 1);// 既読
define('CIRCULAR_LIST_TYPE_CIRCULATING', 2);// 回覧中
define('CIRCULAR_LIST_TYPE_CIRCULATED', 3);// 回覧済
define('CIRCULAR_LIST_TYPE_ALL', 99);// すべての回覧

define('CIRCULAR_REPLY_FLAG_UNSEEN', 0);// 未読
define('CIRCULAR_REPLY_FLAG_SEEN', 1);// 既読
define('CIRCULAR_STATUS_CIRCULATING', 0);// 回覧中
define('CIRCULAR_STATUS_CIRCULATED', 1);// 回覧済

define('CIRCULAR_FRONT_AND_BEHIND_LINK_CNT', 5);

define('CIRCULAR_PERIOD_OVER', 'circular_period_over');
define('CIRCULAR_PERIOD_SOON', 'circular_period_soon');

define('CIRCULAR_DEFAULT_VISIBLE_ROW', 10);

define('CIRCULAR_SEEN_OPTION_REPLY', 0);
define('CIRCULAR_SEEN_OPTION_VISIT', 1);

define('CIRCULAR_BLOCK_TYPE_NORMAL', 0);
define('CIRCULAR_BLOCK_TYPE_PORTAL', 1);

define('CIRCULAR_PORTAL_FORMAT', 'active_action=circular_view_main_detail&circular_id=%s&block_id=%s#_%s');
define('CIRCULAR_PORTAL_LIST_COUNT', 5);

define('CIRCULAR_LIMIT_REPLY_COUNT', 300);

define('CIRCULAR_REPLY_TYPE_TEXTAREA_VALUE', 0);
define('CIRCULAR_REPLY_TYPE_CHECKBOX_VALUE', 1);
define('CIRCULAR_REPLY_TYPE_RADIO_VALUE', 2);

define('CIRCULAR_REPLY_CHOICE_COUNT', 3);
define('CIRCULAR_REPLY_CHOICE_LABEL', "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z");

// 「全回覧」を表示する権限
define('CIRCULAR_ALL_VIEW_AUTH', 4);//主担以上に表示させる場合
//define('CIRCULAR_ALL_VIEW_AUTH', 5);//管理者に表示させる場合

// 回覧対象に管理者を含める
define('CIRCULAR_ALLOW_ADMINISTRATOR', true);	// 管理者権限ユーザを回覧対象リストに表示する。
//define('CIRCULAR_ALLOW_ADMINISTRATOR', false);	// 管理者権限ユーザを回覧対象リストに表示しない。


?>