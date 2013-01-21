<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_Session.class.php,v 1.4 2008/06/02 09:05:54 Ryuji.M Exp $
 */

require_once MAPLE_DIR.'/core/Session.class.php';

/**
 * セッション処理を行うFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Filter_Session extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_Session()
    {
        parent::Filter();
    }

    /**
     * セッション処理を行う
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->trace("Filter_Sessionの前処理が実行されました", "Filter_Session#execute");

        $container =& DIContainerFactory::getContainer();

        $session =& new Session;
        $container->register($session, "Session");

        $attributes = $this->getAttributes();

        $modeArray = array();

        if (isset($attributes["mode"])) {
            $modeArray = explode(",", $attributes["mode"]);
            foreach ($modeArray as $key => $value) {
                $modeArray[$key] = trim($value);
            }
        } else {
            $modeArray[] = "start";
        }

        if (isset($attributes["name"])) {
            $session->setName($attributes["name"]);
        }
        if (isset($attributes["id"])) {
            $session->setID($attributes["id"]);
        }
        if (isset($attributes["savePath"])) {
            $session->setSavePath($attributes["savePath"]);
        }
        if (isset($attributes["subsavePath"]) && !is_writable($session->getSavePath())) {
        	$session->setSavePath($attributes["subsavePath"]);
        }
        if (isset($attributes["cacheLimiter"])) {
            $session->setCacheLimiter($attributes["cacheLimiter"]);
        }
        if (isset($attributes["cacheExpire"])) {
            $session->setCacheExpire($attributes["cacheExpire"]);
        }
        if (isset($attributes["useCookies"])) {
            $session->setUseCookies($attributes["useCookies"]);
        }
        if (isset($attributes["lifetime"])) {
            $session->setCookieLifetime($attributes["lifetime"]);
        }
        if (isset($attributes["path"])) {
            $session->setCookiePath($attributes["path"]);
        }
        if (isset($attributes["domain"])) {
            $session->setCookieDomain($attributes["domain"]);
        }
        if (isset($attributes["secure"])) {
            $session->setCookieSecure($attributes["secure"]);
        }

        if (in_array('start', $modeArray)) {
            $session->start();
        }

        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        if (in_array('close', $modeArray)) {
            $session->close();
        }

        $log->trace("Filter_Sessionの後処理が実行されました", "Filter_Session#execute");
    }
}
?>
