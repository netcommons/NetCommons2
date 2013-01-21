<?php
// $Id: PHP_EOL.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace PHP_EOL constant
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/reserved.constants.core
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5.0.2
 */
if (!defined('PHP_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        // Windows
        case 'WIN':
            define('PHP_EOL', "\r\n");
            break;

        // Mac
        case 'DAR':
            define('PHP_EOL', "\r");
            break;

        // Unix
        default:
            define('PHP_EOL', "\n");
    }
}

?>