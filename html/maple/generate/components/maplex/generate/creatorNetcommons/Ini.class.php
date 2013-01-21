<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

 /**
 * maple.iniファイルを生成する
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

class Maplex_Generate_CreatorNetcommons_Ini extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    //var $actionChain;

    //var $viewConvention;

    function create(&$dto)
    {
    	if(!$dto->skeletonTemplateName)
    		$dto->skeletonTemplateName = "maple.ini";
    	if(!isset($dto->inifileName) || !$dto->inifileName)
    		$dto->inifileName = $this->config->getValue('CONFIG_FILE');
    		
    	if(!isset($dto->Type) || !$dto->Type)
    		$dto->Type = null;
    	
    	$pathList = explode("_", $dto->actionName);
    	if($pathList[1] == "view") {
    		$action_type = "view";
    	} else {
    		$action_type = "action";
    	}
    	
    	if(isset($pathList[2]) && $pathList[2] == "edit") {
    		$second_name = "edit";
    	} elseif(isset($pathList[2]) && $pathList[2] == "main") {
    		$second_name = "main";
    	} elseif(isset($pathList[2]) && $pathList[2] == "admin") {
    		$second_name = "admin";
    	} else {
    		$second_name = "";
    	}
    	
    	if(isset($pathList[3]) && $pathList[3] == "init") {
    		if($second_name == "edit") {
    			$third_name = "edit";
    		} else {
    			$third_name = "init";
    		}
    	} elseif(isset($pathList[3]) && $pathList[3] == "style") {
    		$third_name = "style";
    	} else {
    		$third_name = "";
    	}
    		
        //list ($classname, $filename) = $this->actionChain->makeNames($dto->actionName);

		$filename = $this->config->getValue("WEBAPP_MODULE_DIR");
		foreach($pathList as $path) {
			$filename .= "/".$path;
		}
        $filename .= "/".$dto->inifileName;
        //$filename = dirname(
        //    $this->replaceWithConfig('MODULE_DIR', $filename)) . '/' .
        //    $this->config->getValue('CONFIG_FILE');

        //$template = $this->viewConvention->getTemplate($dto->templateName);
        //$section  = $this->viewConvention->getFilterName($dto->templateType);
        if(isset($dto->templateName))
        	$template = $dto->templateName;
        else
        	$template = null;

        $dir_name = $dto->moduleName;
        
        if(isset($dto->view_actionName))
        	$view_actionName = $dto->view_actionName;
        else
        	$view_actionName = "";

        return $this->output(
            $filename,
            array('dir_name'=> $dir_name,
            		'view_action_name'=> $view_actionName,
            		'action_type'=> $action_type,
            		'type'=> $dto->Type,
            		'second_name'=> $second_name,
            		'third_name'=> $third_name,
            		'moduleType'=> $dto->moduleType,
					'template'=> $template),
            'CONFIG_CODE',
            $this->getTemplateFile($dto->skeletonTemplateName));
    }

}
?>
