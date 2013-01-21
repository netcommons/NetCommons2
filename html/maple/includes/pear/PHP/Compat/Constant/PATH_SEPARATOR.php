<?php
// $Id: PATH_SEPARATOR.php,v 1.14 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace constant PATH_SEPARATOR
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/ref.dir
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.14 $
 * @since       PHP 4.3.0
 */
if (!defined('PATH_SEPARATOR')) {
    define('PATH_SEPARATOR',
        strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':'
     );
}

?>