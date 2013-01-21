<?php
// $Id: str_word_count.php,v 1.11 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace str_word_count()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.str_word_count
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.11 $
 * @since       PHP 4.3.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_str_word_count($string, $format = null)
{
    if ($format !== 1 && $format !== 2 && $format !== null) {
        user_error('str_word_count() The specified format parameter, "' . $format . '" is invalid',
            E_USER_WARNING);
        return false;
    }

    $word_string = preg_replace('/[0-9]+/', '', $string);
    $word_array  = preg_split('/[^A-Za-z0-9_\']+/', $word_string, -1, PREG_SPLIT_NO_EMPTY);

    switch ($format) {
        case null:
            $result = count($word_array);
            break;

        case 1:
            $result = $word_array;
            break;

        case 2:
            $lastmatch = 0;
            $word_assoc = array();
            foreach ($word_array as $word) {
                $word_assoc[$lastmatch = strpos($string, $word, $lastmatch)] = $word;
                $lastmatch += strlen($word);
            }
            $result = $word_assoc;
            break;
    }

    return $result;
}


// Define
if (!function_exists('str_word_count')) {
    function str_word_count($string, $format = null)
    {
        return php_compat_str_word_count($string, $format);
    }
}
