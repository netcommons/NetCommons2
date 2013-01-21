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
 * @version     CVS: $Id: Config.class.php,v 1.1 2006/10/18 08:55:27 Ryuji.M Exp $
 */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

/**
 * config craetorLogic
 *
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class Maplex_Generate_CreatorNetcommons_Config extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
    	$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/config/".$dto->configName;
		
		$dir_name = $dto->moduleName;
		
        return $this->output(
            $filename,
            array('dir_name'=> $dir_name
                  ),
            'CONFIG_CODE',
            $this->getTemplateFile('config.ini'));
    }

}
?>
