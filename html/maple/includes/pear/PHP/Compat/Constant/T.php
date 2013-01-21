<?php
// $Id: T.php,v 1.5 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace tokenizer constants
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/ref.tokenizer
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.5 $
 * @since       PHP 5
 */
if (!defined('T_ML_COMMENT')) {
   define('T_ML_COMMENT', T_COMMENT);
}
if (!defined('T_DOC_COMMENT')) {
    define('T_DOC_COMMENT', T_ML_COMMENT);
}

if (!defined('T_OLD_FUNCTION')) {
    define('T_OLD_FUNCTION', -1);
}
if (!defined('T_ABSTRACT')) {
    define('T_ABSTRACT', -1);
}
if (!defined('T_CATCH')) {
    define('T_CATCH', -1);
}
if (!defined('T_FINAL')) {
    define('T_FINAL', -1);
}
if (!defined('T_INSTANCEOF')) {
    define('T_INSTANCEOF', -1);
}
if (!defined('T_PRIVATE')) {
    define('T_PRIVATE', -1);
}
if (!defined('T_PROTECTED')) {
    define('T_PROTECTED', -1);
}
if (!defined('T_PUBLIC')) {
    define('T_PUBLIC', -1);
}
if (!defined('T_THROW')) {
    define('T_THROW', -1);
}
if (!defined('T_TRY')) {
    define('T_TRY', -1);
}
if (!defined('T_CLONE')) {
    define('T_CLONE', -1);
}

?>