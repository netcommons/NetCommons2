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
 * @package     Maple.core
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: ViewConvention.class.php,v 1.1 2006/10/13 08:50:13 Ryuji.M Exp $
 */


/**
 * ViewConventionクラス
 * 
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */
class ViewConvention
{
    /**
     * 対応するViewフィルタ名を得る
     * 
     * @param  String    $type
     * @return String
     */
    function getFilterName($type)
    {
        if(strtolower($type) == 'smarty') {
            //B.C.
            return 'View';
        }
        return ucfirst($type) .'View';
    }

    /**
     * アクション名と同フォーマットの文字列から
     * テンプレートファイル名を得る
     * 
     * @param  String    $actionName
     * @return String
     */
    function getTemplate($actionName)
    {
        return str_replace('_', '/', $actionName) . '.html';
    }
}

?>
