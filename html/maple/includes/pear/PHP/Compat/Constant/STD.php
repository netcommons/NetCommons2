<?php
// $Id: STD.php,v 1.10 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace commandline constants
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/features.commandline
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.10 $
 * @since       PHP 4.3.0
 */
if (!defined('STDIN')) {
    define('STDIN', fopen('php://stdin', 'r'));
}

if (!defined('STDOUT')) {
    define('STDOUT', fopen('php://stdout', 'w'));
}

if (!defined('STDERR')) {
    define('STDERR', fopen('php://stderr', 'w'));
}

?>