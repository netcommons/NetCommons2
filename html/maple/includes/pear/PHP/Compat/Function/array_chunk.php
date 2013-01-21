<?php
// $Id: array_chunk.php,v 1.16 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_combine()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_chunk
 * @author      Aidan Lister <aidan@php.net>
 * @author      Thiemo Mättig (http://maettig.com)
 * @version     $Revision: 1.16 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_chunk($input, $size, $preserve_keys = false)
{
    if (!is_array($input)) {
        user_error('array_chunk() expects parameter 1 to be array, ' .
            gettype($input) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_numeric($size)) {
        user_error('array_chunk() expects parameter 2 to be long, ' .
            gettype($size) . ' given', E_USER_WARNING);
        return;
    }

    $size = (int)$size;
    if ($size <= 0) {
        user_error('array_chunk() Size parameter expected to be greater than 0',
            E_USER_WARNING);
        return;
    }

    $chunks = array();
    $i = 0;

    if ($preserve_keys !== false) {
        foreach ($input as $key => $value) {
            $chunks[(int)($i++ / $size)][$key] = $value;
        }
    } else {
        foreach ($input as $value) {
            $chunks[(int)($i++ / $size)][] = $value;
        }
    }

    return $chunks;
}


// Define
if (!function_exists('array_chunk')) {
    function array_chunk($input, $size, $preserve_keys = false)
    {
        return php_compat_array_chunk($input, $size, $preserve_keys);
    }
}
