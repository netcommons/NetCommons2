<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * TODO定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

define("TODO_NONE", 0);			// なし
define("TODO_PRIORITY", 1);		// レベル
define("TODO_STATE", 2);		// 済
define("TODO_PERIOD", 3);		// 期限
define("TODO_TASK_VALUE", 4);	// 内容
define("TODO_PROGRESS", 5);		// 進捗率

define("TODO_PRIORITY_LOW", 0);		// 低
define("TODO_PRIORITY_MIDDLE", 1);	// 中
define("TODO_PRIORITY_HIGH", 2);	// 高

define("TODO_PROGRESS_INTERVAL", 10);	// 進捗率の間隔

define("TODO_ASC_IMG","images/todo/default/sort_asc.gif"); //並べ画像
define("TODO_DESC_IMG","images/todo/default/sort_desc.gif"); //並べ画像

define("TODO_PERIOD_CLASS_NAME_SOON", "todo_period_soon");	// 期限間近クラス名称
define("TODO_PERIOD_CLASS_NAME_OVER", "todo_period_over");	// 期限切れクラス名称

define("TODO_REFERENCE_PREFIX_NAME", "popup_todo_reference");
define("TODO_CATEGORY_ADD_PREFIX_NAME", "popup_todo_category_add");
define("TODO_CATEGORY_MODIFY_PREFIX_NAME", "popup_todo_category_modify");
define("TODO_TASK_ADD_PREFIX_NAME", "popup_todo_task_add");
define("TODO_TASK_MODIFY_PREFIX_NAME", "popup_todo_task_modify");
?>