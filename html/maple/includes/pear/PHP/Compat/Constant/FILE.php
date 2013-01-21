<?php
// $Id: FILE.php,v 1.9 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace filesystem constants
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/ref.filesystem
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.9 $
 * @since       PHP 5
 */
if (!defined('FILE_USE_INCLUDE_PATH')) {
    define('FILE_USE_INCLUDE_PATH', 1);
}

if (!defined('FILE_IGNORE_NEW_LINES')) {
    define('FILE_IGNORE_NEW_LINES', 2);
}

if (!defined('FILE_SKIP_EMPTY_LINES')) {
    define('FILE_SKIP_EMPTY_LINES', 4);
}

if (!defined('FILE_APPEND')) {
    define('FILE_APPEND', 8);
}

if (!defined('FILE_NO_DEFAULT_CONTEXT')) {
    define('FILE_NO_DEFAULT_CONTEXT', 16);
}

?>