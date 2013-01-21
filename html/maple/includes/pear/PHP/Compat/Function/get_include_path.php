<?php
// $Id: get_include_path.php,v 1.6 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace get_include_path()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.get_include_path
 * @author      Stephan Schmidt <schst@php.net>
 * @version     $Revision: 1.6 $
 * @since       PHP 4.3.0
 * @require     PHP 4.0.0
 */
function php_compat_get_include_path()
{
    return ini_get('include_path');
}


// Define
if (!function_exists('get_include_path')) {
    function get_include_path()
    {
        return php_compat_get_include_path();
    }
}
