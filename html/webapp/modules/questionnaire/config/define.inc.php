<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

// アンケート方式
define("QUESTIONNAIRE_TYPE_LIST_VALUE", "1");
define("QUESTIONNAIRE_TYPE_SEQUENCE_VALUE", "2");
define("QUESTIONNAIRE_TYPE_RANDOM_VALUE", "3");

// アンケートステータスフラグ
define("QUESTIONNAIRE_STATUS_INACTIVE_VALUE", "0");
define("QUESTIONNAIRE_STATUS_ACTIVE_VALUE", "1");
define("QUESTIONNAIRE_STATUS_END_VALUE", "2");

// 質問タイプ
define("QUESTIONNAIRE_QUESTION_TYPE_RADIO_VALUE", "0");
define("QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX_VALUE", "1");
define("QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE", "2");

// 選択肢デフォルト数
define("QUESTIONNAIRE_CHOICE_DEFAULT_NUMBER", 5);

// 未回答、回答済
define("QUESTIONNAIRE_ANSWER_NONE_VALUE", "0");
define("QUESTIONNAIRE_ANSWER_DONE_VALUE", "1");

// 選択肢表示方法用
define("QUESTIONNAIRE_CHOICE_DISPLAY_NORMAL", 0);
define("QUESTIONNAIRE_CHOICE_DISPLAY_REFERENCE", 1);
define("QUESTIONNAIRE_CHOICE_DISPLAY_ANSWER", 2);

//新着情報に載せるタイトルの長さ
define("QUESTIONNAIRE_WHATSNEW_TITLE", 14);

?>