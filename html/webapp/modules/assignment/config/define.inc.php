<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * レポート定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

define("ASSIGNMENT_REFERENCE_PREFIX_NAME", "popup_assignment_reference");
define("ASSIGNMENT_REPORT_FORM_PREFIX_NAME", "popup_assignment_report");
define("ASSIGNMENT_GRADE_FORM_PREFIX_NAME", "popup_assignment_grade");
define("ASSIGNMENT_SUMMARY_PREFIX_NAME", "popup_assignment_summary");
define("ASSIGNMENT_SUBMITTERS_PREFIX_NAME", "popup_assignment_submitters");
define("ASSIGNMENT_SUBMITTER_PREFIX_NAME", "popup_assignment_submitter");

define("ASSIGNMENT_PERIOD_CLASS_NAME_SOON", "assignment_period_soon");	// 期限間近クラス名称
define("ASSIGNMENT_PERIOD_CLASS_NAME_OVER", "assignment_period_over");	// 期限切れクラス名称

define("ASSIGNMENT_SUBMIT_FLAG_YET_REREASED", 0);	// 未提出
define("ASSIGNMENT_SUBMIT_FLAG_SUBMITTED", 1);		// 提出済
define("ASSIGNMENT_SUBMIT_FLAG_GRADED", 2);			// 評価済
define("ASSIGNMENT_SUBMIT_FLAG_RESUBMITTED", 3);	// 再提出

define("ASSIGNMENT_STATUS_REREASED", 0);		// 公開中
define("ASSIGNMENT_STATUS_TEMPORARY", 1);		// 一時保存中
define("ASSIGNMENT_STATUS_BEFORE_REREASED", 2);	// 公開前

define("ASSIGNMENT_EDIT_MIN_SIZE", 700); 	//編集のポップアップの最小サイズ

//新着情報に載せるタイトルの長さ
define("ASSIGNMENT_WHATSNEW_TITLE", 14);

//会員情報に載せるスクロール件数
define("ASSIGNMENT_PERSONAL_INFORMATION_MAX_ROW", 20);

?>