<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレンダーの定数
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

define("CALENDAR_PLAN_EDIT_THIS", "0");
define("CALENDAR_PLAN_EDIT_AFTER", "1");
define("CALENDAR_PLAN_EDIT_ALL", "2");

define("CALENDAR_LINK_NONE", "");
define("CALENDAR_LINK_TODO", "todo");
define("CALENDAR_LINK_RESERVATION", "reservation");

define("CALENDAR_LINK_TABLE_TODO", "todo_task");
define("CALENDAR_LINK_TABLE_RESERVATION", "reservation_reserve");
define("CALENDAR_LINK_COLUMN_TODO", "task_id");
define("CALENDAR_LINK_COLUMN_RESERVATION", "reserve_details_id");

if (!defined("CALENDAR_REPEAT_WDAY")) {
	define("CALENDAR_REPEAT_WDAY", "SU|MO|TU|WE|TH|FR|SA");
}

?>
