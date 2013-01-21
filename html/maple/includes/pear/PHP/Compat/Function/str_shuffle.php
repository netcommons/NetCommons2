<?php
// $Id: str_shuffle.php,v 1.10 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace str_shuffle()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.str_shuffle
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.10 $
 * @since       PHP 4.3.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_str_shuffle($str)
{
    // Cast
    $str = (string) $str;
    
    // Swap random character from [0..$i] to position [$i].
    for ($i = strlen($str) - 1; $i >= 0; $i--) {  
        $j = mt_rand(0, $i);
        $tmp = $str[$i];
        $str[$i] = $str[$j];
        $str[$j] = $tmp;
    }
    
    return $str;
}


// Define
if (!function_exists('str_shuffle')) {
    function str_shuffle($str)
    {
        return php_compat_str_shuffle($str);
    }
}
