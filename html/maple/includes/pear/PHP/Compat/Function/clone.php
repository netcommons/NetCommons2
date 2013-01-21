<?php
// $Id: clone.php,v 1.5 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace clone()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/language.oop5.cloning
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.5 $
 * @since       PHP 5.0.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_clone($object)
{
    // Sanity check
    if (!is_object($object)) {
        user_error('clone() __clone method called on non-object', E_USER_WARNING);
        return;
    }

    // Use serialize/unserialize trick to deep copy the object
    $object = unserialize(serialize($object));

    // If there is a __clone method call it on the "new" class
    if (method_exists($object, '__clone')) {
        $object->__clone();
    }
    
    return $object;    
}


// Define
if (version_compare(phpversion(), '5.0') === -1) {
    // Needs to be wrapped in eval as clone is a keyword in PHP5
    eval('
        function clone($object)
        {
            return php_compat_clone($object);
        }
    ');
}
