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
 * @package     Maple.generate
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: CmdRequest.class.php,v 1.2 2006/10/19 02:56:55 Ryuji.M Exp $
 */

require_once(MAPLE_DIR .'/core/Request.class.php');

/**
 * コマンドライン引数を格納する
 *
 * @package     Maple.generate
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class CmdRequest extends Request
{
    /**
     * コンストラクター
     * 親のコンストラクタは呼ばない
     *
     * @access  public
     * @since   3.1.0
     */
    function CmdRequest()
    {
        $this->_params = array();
        if (php_sapi_name() != 'cli' && php_sapi_name() != 'cgi') {
            return;
        }

        if (isset($_SERVER['argv'])) {
            $this->_params['args'] = $_SERVER['argv'];
            $script    = array_shift($this->_params['args']);
        } else {
            $this->_params['args'] = array();
        }

        //常にrouterを起動
        $this->_params[ACTION_KEY] = 'maple_generate_router';
    }

    /**
     * PHPの実行インタフェースを返却
     *
     * @return  string  PHPの実行インタフェースの値
     * @access  public
     * @since   3.1.0
     */
    function getMethod()
    {
        return php_sapi_name();
    }

    /**
     * アクションを切り分ける
     *
     * このクラスでは何も処理しない
     *
     * @access    public
     * @since    3.1.0
     */
    function dispatchAction()
    {
        return;
    }
}
?>
