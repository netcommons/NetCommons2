<?php
// $Id: php_strip_whitespace.php,v 1.13 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace T_DOC_COMMENT in PHP 4
 */
if (!defined('T_ML_COMMENT')) {
   define('T_ML_COMMENT', T_COMMENT);
} else {
   define('T_DOC_COMMENT', T_ML_COMMENT);
}


/**
 * Replace php_strip_whitespace()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.php_strip_whitespace
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error) + Tokenizer extension
 */
function php_compat_php_strip_whitespace($file)
{
    // Sanity check
    if (!is_scalar($file)) {
        user_error('php_strip_whitespace() expects parameter 1 to be string, ' .
            gettype($file) . ' given', E_USER_WARNING);
        return;
    }

    // Load file / tokens
    $source = implode('', file($file));
    $tokens = token_get_all($source);

    // Init
    $source = '';
    $was_ws = false;

    // Process
    foreach ($tokens as $token) {
        if (is_string($token)) {
            // Single character tokens
            $source .= $token;
        } else {
            list($id, $text) = $token;
            
            switch ($id) {
                // Skip all comments
                case T_COMMENT:
                case T_ML_COMMENT:
                case T_DOC_COMMENT:
                    break;

                // Remove whitespace
                case T_WHITESPACE:
                    // We don't want more than one whitespace in a row replaced
                    if ($was_ws !== true) {
                        $source .= ' ';
                    }
                    $was_ws = true;
                    break;

                default:
                    $was_ws = false;
                    $source .= $text;
                    break;
            }
        }
    }

    return $source;

}


// Define
if (!function_exists('php_strip_whitespace')) {
    function php_strip_whitespace($file)
    {
        return php_compat_php_strip_whitespace($file);
    }
}
