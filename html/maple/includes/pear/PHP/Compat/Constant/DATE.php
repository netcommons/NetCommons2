<?php
// $Id: DATE.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replicate datetime constants
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/manual/en/ref.datetime.php
 * @author      Rhett Waldock <rwaldock@gmail.com>
 * @version     $Revision: 1.4 $
 * @since       PHP 5.1.1
 */
if (!defined('DATE_ATOM')) {
    define('DATE_ATOM', 'Y-m-d\TH:i:sO');
}

if (!defined('DATE_COOKIE')) {
    define('DATE_COOKIE', 'D, d M Y H:i:s T');
}

if (!defined('DATE_ISO8601')) {
    define('DATE_ISO8601', 'Y-m-d\TH:i:sO');
}

if (!defined('DATE_RFC822')) {
    define('DATE_RFC822', 'D, d M Y H:i:s T');
}

if (!defined('DATE_RFC850')) {
    define('DATE_RFC850', 'l, d-M-y H:i:s T');
}

if (!defined('DATE_RFC1036')) {
    define('DATE_RFC1036', 'l, d-M-y H:i:s T');
}

if (!defined('DATE_RFC1123')) {
    define('DATE_RFC1123', 'D, d M Y H:i:s T');
}

if (!defined('DATE_RFC2822')) {
    define('DATE_RFC2822', 'D, d M Y H:i:s O');
}

if (!defined('DATE_RSS')) {
    define('DATE_RSS', 'D, d M Y H:i:s T');
}

if (!defined('DATE_W3C')) {
    define('DATE_W3C', 'Y-m-d\TH:i:sO');
}

?> 