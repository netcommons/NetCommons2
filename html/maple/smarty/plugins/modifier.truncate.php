<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function smarty_modifier_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';

    if (mb_strlen($string, INTERNAL_CODE) > $length) {
        $length -= mb_strlen($etc, INTERNAL_CODE);
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1, INTERNAL_CODE));
        }
        if(!$middle) {
            return mb_substr($string, 0, $length, INTERNAL_CODE).$etc;
        } else {
            return mb_substr($string, 0, $length/2, INTERNAL_CODE) . $etc . mb_substr($string, -$length/2, $length, INTERNAL_CODE);
        }
    } else {
        return $string;
    }
}

/* vim: set expandtab: */

?>
