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
 * @version     CVS: $Id: Filter_Token.class.php,v 1.3 2006/09/29 06:16:27 Ryuji.M Exp $
 */

require_once MAPLE_DIR.'/core/Token.class.php';

/**
 * Token処理を行うFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Filter_Token extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_Token()
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
        $log->trace("Filter_Tokenの前処理が実行されました", "Filter_Token#execute");

        $container =& DIContainerFactory::getContainer();

        $session =& $container->getComponent("Session");

        $token =& new Token;
        $container->register($token, "Token");

        $token->setSession($session);

        $attributes = $this->getAttributes();

        if (isset($attributes["name"])) {
            $token->setName($attributes["name"]);
        }

        $modeArray = array();

        if (isset($attributes["mode"])) {
            $modeArray = explode(",", $attributes["mode"]);
            foreach ($modeArray as $key => $value) {
                $modeArray[$key] = trim($value);
            }
        } else {
        //var_dump($this->_container);
            $modeArray[] = "build";
        }

        foreach ($modeArray as $value) {
            switch ($value) {
            case 'check':
                $request =& $container->getComponent("Request");
                if (!$token->check($request)) {
                    $actionChain =& $container->getComponent("ActionChain");
                    $errorList =& $actionChain->getCurErrorList();
                    $errorList->setType(TOKEN_ERROR_TYPE);
                }
                break;
            case 'remove':
                $token->remove();
                break;
            case 'build':
            default:
                $token->build();
                break;
            }
        }

        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        $log->trace("Filter_Tokenの後処理が実行されました", "Filter_Token#execute");
    }
}
?>
