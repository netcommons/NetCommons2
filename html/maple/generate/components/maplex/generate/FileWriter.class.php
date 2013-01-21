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
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: FileWriter.class.php,v 1.1 2006/10/13 08:50:18 Ryuji.M Exp $
 */

require_once(MAPLE_DIR .'/core/SimpleView.class.php');

/**
 * SimpleViewを用いてファイルを書き出す
 *
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class Maplex_Generate_FileWriter
{
    /**
     * @var  object  $fileUtil
     */
    var $fileUtil;

    /**
     * @var  GlobalConfig  $config  
     */
    var $config;
    
    /**
     * $templateを読み込んで$outputFileに書き出す
     * $varsはテンプレートから $skeleton としてアクセスできる
     * 
     * @param  String    $template
     * @param  String    $outputFile
     * @param  array     $vars
     * @param  String    $outputEncoding
     * @return boolean
     */
    function write($template, $outputFile, $vars, $outputEncoding)
    {
        $skeleton = (object)$vars;

        $view =& $this->_createSimpleView($skeleton, $outputEncoding);

        $buf = $view->fetch($template);
        $buf = str_replace("\r\n", "\n", $buf);
        
        return $this->fileUtil->write($outputFile, $buf);
    }

    /**
     * SimpleViewの新しいインスタンスを返す
     * 
     * @since 06/08/18 17:03
     * @param  object    $skeleton
     * @param  String    $outputEncoding
     * @return SimpleView
     */
    function &_createSimpleView(&$skeleton, $outputEncoding)
    {
        $view =& new SimpleView();
        $view->outputEncoding   = $this->config->getValue($outputEncoding);
        $view->templateEncoding = SKELETON_CODE;

        $view->assignByRef('skeleton', $skeleton);
        $view->assign('author', $this->config->getValue('generator.author', ''));
        $view->assign('license', $this->config->getValue('generator.license', ''));
        $view->assign('copyright', $this->config->getValue('generator.copyright', ''));

        return $view;
    }

}
?>
