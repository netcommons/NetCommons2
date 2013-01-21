<?php
// $Id $


/**
 * Replace setcookie()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.setcookie
 * @author      Stefan Neufeind <neufeind@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5.2 (Added optional httponly parameter)
 * @require     PHP 3 (setcookie)
 */
function php_compat_setcookie($name, $value, $expire, $path, $domain, $secure, $httponly)
{
    // Following the idea on Matt Mecham's blog
    // http://blog.mattmecham.com/archives/2006/09/http_only_cookies_without_php.html
    $domain === ($httponly === true) ? $domain . '; HttpOnly' : $domain;
    setcookie($name, $value, $expire, $path, $domain, $secure);
}

// Define
if (!function_exists('setcookie')) {
    function setcookie($name, $value, $expire, $path, $domain, $secure, $httponly)
    {
        return php_compat_setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
}