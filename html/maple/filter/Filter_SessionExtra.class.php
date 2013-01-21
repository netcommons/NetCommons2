<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * セッション処理を行うFilter
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

//多次元配列を使用するためSessionExtraに変更 Ryuji.M
require_once MAPLE_DIR.'/nccore/SessionExtra.class.php';
//require_once MAPLE_DIR.'/core/Session.class.php';

/**
 * セッション処理を行うFilter
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_SessionExtra extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_SessionExtra()
    {
        parent::Filter();
    }

    /**
     * セッション処理を行う
     *
     * @access  public
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->trace("Filter_Sessionの前処理が実行されました", "Filter_SessionExtra#execute");

        $container =& DIContainerFactory::getContainer();

        //$session =& new Session;
        $session =& new SessionExtra;
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
		if (version_compare(phpversion(), '5.3.0', '>=')
			&& MobileCheck::isMobile()) {
			ini_set("session.use_only_cookies", 0);
		}

        if (in_array('start', $modeArray)) {
            $session->start();
        }

        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        if (in_array('close', $modeArray)) {
            $session->close();
        }

        $log->trace("Filter_Sessionの後処理が実行されました", "Filter_SessionExtra#execute");
    }
}
?>
