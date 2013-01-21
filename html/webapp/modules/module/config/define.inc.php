<?php
/**
 *  共通定義
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

//;---------------------設定ファイル未設定時のデフォルト値-----------------------
define('MODULE_DEFAULT_VARSION', "2.1.0.0");
//;---------------------設定ファイル未設定時のデフォルト値---------------------
define('MODULE_DEFAULT_SYSTEM_FLAG', "0");
define('MODULE_DEFAULT_DISPOSITION_FLAG', "1");
define('MODULE_DEFAULT_MODULEICON', "noimage.gif");
define('MODULE_DEFAULT_MIN_WIDTH_SIZE', "0");
define('MODULE_DEFAULT_THEME_NAME', "");
define('MODULE_DEFAULT_TEMP_NAME', "default");
define('MODULE_DEFAULT_ENABLE_FLAG', "1");
define('MODULE_UPDATE_TIME_LIMIT', 3600);

//;  共通系jsファイル一覧
define('MODULE_COMMON_DEBUG_JS', "debug.js");
define('MODULE_COMMON_COMMON_JS', "prototype.js|common.js|operation.js");
define('MODULE_COMMON_COMPCOMMON_JS', "comp_commonutil.js");
//define('MODULE_COMMON_COMP_JS', "comp");

define('MODULE_PAGE_JS', "pages.js");
define('MODULE_SYSTEM_JS', "control.js");

define('MODULE_COMMON_GENERAL_JS', "common|comp");

//; javascriptの読み込まれる順番 
//; デバッグ用js->共通js->コンポーネント共通js->コンポーネントjs->ページjs->モジュールjs
define('MODULE_READ_ORDER_DEBUG', "0");
define('MODULE_READ_ORDER_COMMON', "1");
define('MODULE_READ_ORDER_COMPCOMMON', "2");
define('MODULE_READ_ORDER_COMP', "3");
define('MODULE_READ_ORDER_PAGE', "4");
define('MODULE_READ_ORDER_MODULE', "5");
?>