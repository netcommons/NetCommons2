<?php
// $Id: E_STRICT.php,v 1.12 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace constant E_STRICT
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/ref.errorfunc
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.12 $
 * @since       PHP 5
 */
if (!defined('E_STRICT')) {
    define('E_STRICT', 2048);
}

?>