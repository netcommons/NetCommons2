<?php
// $Id: DIRECTORY_SEPARATOR.php,v 1.2 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace constant DIRECTORY_SEPARATOR
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/reserved.constants.standard
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 4.0.6
 */
if (!defined('DIRECTORY_SEPARATOR')) {
    define('DIRECTORY_SEPARATOR',
        strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/'
    );
}

?>