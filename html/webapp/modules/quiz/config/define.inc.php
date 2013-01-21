<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

// 小テスト方式
define("QUIZ_TYPE_LIST_VALUE", "1");
define("QUIZ_TYPE_SEQUENCE_VALUE", "2");
define("QUIZ_TYPE_RANDOM_VALUE", "3");

// 小テストステータスフラグ
define("QUIZ_STATUS_INACTIVE_VALUE", "0");
define("QUIZ_STATUS_ACTIVE_VALUE", "1");
define("QUIZ_STATUS_END_VALUE", "2");

// 問題タイプ
define("QUIZ_QUESTION_TYPE_RADIO_VALUE", "0");
define("QUIZ_QUESTION_TYPE_CHECKBOX_VALUE", "1");
define("QUIZ_QUESTION_TYPE_TEXTAREA_VALUE", "2");
define("QUIZ_QUESTION_TYPE_WORD_VALUE", "3");

// 選択肢デフォルト数
define("QUIZ_CHOICE_DEFAULT_NUMBER", 5);

// 未解答、未採点、採点済、正解、不正解
define("QUIZ_ANSWER_NONE_VALUE", "0");
define("QUIZ_ANSWER_NOT_MARK_VALUE", "1");
define("QUIZ_ANSWER_SCORED_VALUE", "2");
define("QUIZ_ANSWER_CORRECT_VALUE", "3");
define("QUIZ_ANSWER_WRONG_VALUE", "4");

// 選択肢表示方法用
define("QUIZ_CHOICE_DISPLAY_NORMAL", 0);
define("QUIZ_CHOICE_DISPLAY_REFERENCE", 1);
define("QUIZ_CHOICE_DISPLAY_ANSWER", 2);

//新着情報に載せるタイトルの長さ
define("QUIZ_WHATSNEW_TITLE", 14);

//1設問の最大配点
define("QUIZ_MAX_ALLOTMENT", 100000);

define("QUIZ_PERSONAL_INFORMATION_MAX_ROW", 20);

define("QUIZ_PREFIX_ID_NAME_REFERENCE", "quiz_reference");
?>