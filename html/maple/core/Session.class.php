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
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Session.class.php,v 1.4 2008/08/13 02:58:45 Ryuji.M Exp $
 */

/**
 * Session管理を行う
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Session
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Session()
    {
    }

    /**
     * 設定されている値を返却
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function getParameter($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }

    /**
     * 設定されている値を返却(オブジェクトを返却)
     *
     * @param   string  $key    パラメータ名
     * @return  Object  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function &getParameterRef($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }

    /**
     * 値をセット
     *
     * @param   string  $key    パラメータ名
     * @param   string  $value  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function setParameter($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 値をセット(オブジェクトをセット)
     *
     * @param   string  $key    パラメータ名
     * @param   Object  $value  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function setParameterRef($key, &$value)
    {
        $_SESSION[$key] =& $value;
    }

    /**
     * 値を返却(配列で返却)
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値(配列)
     * @access  public
     * @since   3.0.0
     */
    function getParameters()
    {
        if (isset($_SESSION)) {
            return $_SESSION;
        }
    }

    /**
     * 値を削除する
     *
     * @param   string  $key    パラメータ名
     * @access  public
     * @since   3.0.0
     */
    function removeParameter($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * セッション処理を開始
     *
     * @access  public
     * @since   3.0.0
     */
    function start()
    {
        @session_start();
    }

    /**
     * セッション処理を終了
     *
     * @access  public
     * @since   3.0.0
     */
    function close()
    {
        $_SESSION = array();
        session_destroy();
    }

    /**
     * セッション名を返却
     *
     * @return  string  セッション名
     * @access  public
     * @since   3.0.0
     */
    function getName()
    {
        return session_name();
    }

    /**
     * セッション名をセット
     *
     * @param   string  $name   セッション名
     * @access  public
     * @since   3.0.0
     */
    function setName($name = '')
    {
        if ($name) {
            session_name($name);
        }
    }

    /**
     * セッションIDを返却
     *
     * @return  string  セッションID
     * @access  public
     * @since   3.0.0
     */
    function getID()
    {
        return session_id();
    }

    /**
     * セッションIDをセット
     *
     * @param   string  $id セッションID
     * @access  public
     * @since   3.0.0
     */
    function setID($id = '')
    {
        if ($id) {
            session_id($id);
        }
    }

    /**
     * save_pathをセット
     *
     * @param   string  $savePath   save_path
     * @access  public
     * @since   3.0.0
     */
    function setSavePath($savePath)
    {
        if (!isset($savePath)) {
            return;
        }
        // defineしたものを読み込めるように修正 Ryuji.M
        if (preg_match("/^define:/", $savePath)) {
			$savePath  = preg_replace("/^define:/", "", $savePath);
			$savePath = constant($savePath);
        }
        if(!is_writeable($savePath)) {
        	// 書き込みできないので、そのまま返却
        	return;	
        }
        // Edit End Ryuji.M
        session_save_path($savePath);
    }
    
     /**
     * save_pathを返却
     * 追加
     *
     * @return   string  $savePath   save_path
     * @access  public
     * 
     */
    function getSavePath()
    {
        return session_save_path();
    }

    /**
     * cache_limiterをセット
     *
     * @param   string  $cacheLimiter   cache_limiter
     * @access  public
     * @since   3.0.0
     */
    function setCacheLimiter($cacheLimiter)
    {
        if (!isset($cacheLimiter)) {
            return;
        }
        session_cache_limiter($cacheLimiter);
    }

    /**
     * cache_expireをセット
     *
     * @param   string  $cacheExpire    cache_expire
     * @access  public
     * @since   3.0.0
     */
    function setCacheExpire($cacheExpire)
    {
        if (!isset($cacheExpire)) {
            return;
        }
        session_cache_expire($cacheExpire);
    }

    /**
     * use_cookies をセット
     *
     * @param   string  $useCookies use_cookies 
     * @access  public
     * @since   3.0.1
     */
    function setUseCookies($useCookies)
    {
        if (!isset($useCookies)) {
            return;
        }
        ini_set('session.use_cookies', $useCookies ? 1 : 0);
    }

    /**
     * cookie_lifetime をセット
     *
     * @param   string  $cookieLifetime cookie_lifetime
     * @access  public
     * @since   3.0.1
     */
    function setCookieLifetime($cookieLifetime)
    {
        if (!isset($cookieLifetime)) {
            return;
        }

        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookieLifetime, $cookie_params['path'], $cookie_params['domain'], $cookie_params['secure']);
    }

    /**
     * cookie_path をセット
     *
     * @param   string  $cookiePath cookie_path
     * @access  public
     * @since   3.0.1
     */
    function setCookiePath($cookiePath)
    {
        if (!isset($cookiePath)) {
            return;
        }

        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookie_params['lifetime'], $cookiePath, $cookie_params['domain'], $cookie_params['secure']);
    }

    /**
     * cookie_domain をセット
     *
     * @param   string  $cookieDomain   cookie_domain
     * @access  public
     * @since   3.0.1
     */
    function setCookieDomain($cookieDomain)
    {
        if (!isset($cookieDomain)) {
            return;
        }

        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookie_params['lifetime'], $cookie_params['path'], $cookieDomain, $cookie_params['secure']);
    }

    /**
     * cookie_secure をセット(SSL利用時などにsecure属性を設定する)
     *
     * @param   string  $cookieSecure   cookie_secure
     * @access  public
     * @since   3.0.1
     */
    function setCookieSecure($cookieSecure)
    {
        if (!isset($cookieSecure)) {
            return;
        }

        if (preg_match('/^true$/i', $cookieSecure) ||
            preg_match('/^secure$/i', $cookieSecure) ||
            preg_match('/^on$/i', $cookieSecure) ||
            ($cookieSecure === '1') || ($cookieSecure === 1)) {
            $cookieSecure = true;
        } else {
            $cookieSecure = false;
        }

        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookie_params['lifetime'], $cookie_params['path'], $cookie_params['domain'], $cookieSecure);
    }
}
?>
