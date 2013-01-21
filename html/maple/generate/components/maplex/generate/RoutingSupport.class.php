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
 * @version     CVS: $Id: RoutingSupport.class.php,v 1.1 2006/10/13 08:50:19 Ryuji.M Exp $
 */

/**
 * Viewフィルタに対して提供するConfigurationを生成する
 * DynamicFilterConfigフィルタと合わせて用いる
 * 
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @access      public
 */
class Maplex_Generate_RoutingSupport
{
    /**
     * @var  GeneratorManager  $manager  
     */
    var $manager;

    /**
     * array(
     *   <generator_name> => template file,
     *   ...
     * )
     * 
     * @access public
     * @since 06/07/29 12:55
     * @return array
     */
    function getTemplateConfig()
    {
        $config = array();
        foreach($this->manager->getAllGeneratorInfo() as $name => $gen) {
            $config[$name] = $gen['usage_template'];
        }
        return $config;
    }

    /**
     * array(
     *   <generator_name> => "action:<action_name>",
     *   ...
     * )
     * 
     * @access public
     * @since 06/07/29 12:55
     * @return array
     */
    function getForwardConfig()
    {
        $config = array();
        foreach($this->manager->getAllGeneratorInfo() as $name => $gen) {
            $config[$name] = "action:". $gen['action'];
        }
        return $config;
    }
}
?>
