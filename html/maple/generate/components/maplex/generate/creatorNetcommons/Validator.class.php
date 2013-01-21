<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * validatorファイルを書き出す
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

class Maplex_Generate_CreatorNetcommons_Validator extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
    	$classname = "Validator_" . ucfirst($dto->validatorName);
    	 
    	if(isset($dto->moduleName)) {
        	//modules以下
        	$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/validator/"."${classname}.class.php";
        	$classname = ucfirst($dto->moduleName)."_".$classname;
        } else
			$filename = $this->config->getValue('MAPLE_VALIDATOR_DIR') ."/${classname}.class.php";
		
        return $this->output(
            $filename,
            array('classname' => $classname));
    }
}
?>
