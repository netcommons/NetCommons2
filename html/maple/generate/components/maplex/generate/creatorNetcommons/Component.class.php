<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Component関連ファイルを書き出す
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

class Maplex_Generate_CreatorNetcommons_Component extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    //var $container;

    function create(&$dto)
    {
    	$container =& DIContainerFactory::getContainer();
    	list ($classname, $filename) = $container->makeNames($dto->componentName);
    	
    	//$pathList = explode("_", $dto->componentName);
        //list ($classname, $filename) = $this->container->makeNames($dto->componentName);
        //$filename = $this->config->getValue('COMPONENT_DIR') .'/'. $filename;
        if(isset($dto->setType)) {
        	//modules以下
        	$filename = $this->config->getValue("WEBAPP_MODULE_DIR").'/'. $filename;
        } else
			$filename = $this->config->getValue('WEBAPP_COMPONENT_DIR') .'/'. $filename;
        
        return $this->output(
            $filename,
            array('classname' => $classname));
    }
}
?>
