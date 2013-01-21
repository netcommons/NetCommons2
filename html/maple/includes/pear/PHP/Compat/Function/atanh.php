<?php
// $Id: atanh.php,v 1.3 2008/06/29 13:27:31 arpad Exp $

/**
 * Replace atanh()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.atanh
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5
 * @require     PHP 3.0.0
 */
function php_compat_atanh($n)
{
    return 0.5 * (log(1 + $n) - log(1 - $n));
}

if (!function_exists('atanh')) {
    function atanh($n)
    {
	return php_compat_atanh($n);
    }
}
