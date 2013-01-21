<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Networking
 * @package    Net_UserAgent_Mobile
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2003-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Portable.php,v 1.2 2008/06/05 02:42:46 snakajima Exp $
 * @since      File available since Release 0.1.0
 */

require_once 'Net/UserAgent/Mobile/Common.php';
require_once 'Net/UserAgent/Mobile/Display.php';

// {{{ Net_UserAgent_Mobile_NonMobile

/**
 * Portable Agent implementation
 *
 * Net_UserAgent_Mobile_NonMobile is a subclass of
 * {@link Net_UserAgent_Mobile_Common}, which implements non-mobile or unimplemented
 * user agents.
 *
 * SYNOPSIS:
 * <code>
 * require_once 'Net/UserAgent/Mobile.php';
 *
 * $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0';
 * $agent = &Net_UserAgent_Mobile::factory();
 * </code>
 *
 * @category   Networking
 * @package    Net_UserAgent_Mobile
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2003-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: 1.0.0RC1
 * @since      Class available since Release 0.1.0
 */
class Net_UserAgent_Mobile_Portable extends Net_UserAgent_Mobile_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ isPortable()

    /**
     * returns true
     *
     * @return boolean
     */
    function isPortable()
    {
        return true;
    }

    // }}}
    // {{{ parse()

    /**
     * Parses HTTP_USER_AGENT string.
     *
     * @param string $userAgent User-Agent string
     */
    function parse($userAgent)
    {
        if (preg_match('!PSP!', $userAgent)) {
            $this->_parsePSP($userAgent);
        } elseif (preg_match('!Nitro!', $userAgent)) {
            $this->_parseNitro($userAgent);
        }
    }

    // }}}
    // {{{ _parsePSP()

    /**
     * parse HTTP_USER_AGENT string for the PlayStation Portable aegnt
     *
     * @param array $agent parts of the User-Agent string
     * @throws Net_UserAgent_Mobile_Error
     */
    function _parsePSP($userAgent)
    {
        if (preg_match('!^Mozilla/4\.0 \(PSP \(PlayStation Portable\); ([0-9]\.[0-9]+)\)!',
                       $userAgent, $matches)
            ) {
            $this->version = $matches[1];
        }
		$this->_rawModel = "PlayStation Portable";
        $this->name = "PlayStation Portable";
    }

    // }}}
    // {{{ _parseNitro()

    /**
     * parse HTTP_USER_AGENT string for the Nintendo DS aegnt
     *
     * @param array $agent parts of the User-Agent string
     * @throws Net_UserAgent_Mobile_Error
     */
    function _parseNitro($userAgent)
    {
        if (preg_match('!^Mozilla/4\.0 \(compatible; MSIE 6.0; Nitro\).*\)!',
                       $userAgent, $matches)
            ) {
            $this->version = $matches[1];
        }
        $this->_rawModel = "Nintendo DS";
        $this->name = "Nintendo DS";
    }

    // }}}
    // {{{ makeDisplay()

    /**
     * create a new {@link Net_UserAgent_Mobile_Display} class instance
     *
     * @return Net_UserAgent_Mobile_Display
     */
    function makeDisplay()
    {
        return new Net_UserAgent_Mobile_Display(null);
    }

    // }}}
    // {{{ getCarrierShortName()

    /**
     * returns the short name of the carrier
     *
     * @return string
     */
    function getCarrierShortName()
    {
        return substr($this->name,0,1);
    }

    // }}}
    // {{{ getCarrierLongName()

    /**
     * returns the long name of the carrier
     *
     * @return string
     */
    function getCarrierLongName()
    {
        return $this->name;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
