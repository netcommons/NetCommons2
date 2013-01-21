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

class Maplex_Generate_CreatorNetcommons_Theme extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    //var $actionChain;

    //var $viewConvention;

    function create(&$dto)
    {
    	if(!isset($dto->skeletonTemplateName) || !$dto->skeletonTemplateName)
    		$dto->skeletonTemplateName = "theme.ini";
    	
    	if(!isset($dto->inifileName) || !$dto->inifileName)
    		$dto->inifileName = "theme.ini";
    		
    	//if(!isset($dto->Type) || !$dto->Type)
    	//	$dto->Type = null;
    	
    	//$pathList = explode("_", $dto->actionName);
    	
    	$theme_name = $dto->themeName;
        $second_name = $dto->secondName;
		if($dto->Type == "config") {
			$filename = $this->config->getValue("WEBAPP_THEME_DIR");
			if($dto->inifileName != "block_custom.ini" && $dto->inifileName != "page_custom.ini") {
				$filename .= "/".$theme_name . "/config";
			} else {
				$pathList = explode("_", $dto->actionName);
				if(isset($pathList[1])) {
					$filename .= "/".$theme_name . "/config/".$pathList[1];
				} else {
					$filename .= "/".$theme_name . "/config";
				}
			}
	        $filename .= "/".$dto->inifileName;
		} else if($dto->Type == "css") {
			$filename = $this->config->getValue("WEBAPP_THEME_DIR");
			$pathList = explode("_", $dto->actionName);
			if(isset($pathList[1])) {
				$filename .= "/".$theme_name . "/css/".$pathList[1];
			} else {
				$filename .= "/".$theme_name . "/css";
			}
	        $filename .= "/".$dto->inifileName;
		} else if($dto->Type == "language") {
			$filename = $this->config->getValue("WEBAPP_THEME_DIR");
			if($dto->inifileName != "block_custom.ini") {
				$filename .= "/".$theme_name . "/language/japanese";	//japanese固定
			} else {
				$pathList = explode("_", $dto->actionName);
				if(isset($pathList[1])) {
					$filename .= "/".$theme_name . "/language/japanese/".$pathList[1];
				} else {
					$filename .= "/".$theme_name . "/language/japanese";
				}
			}
	        $filename .= "/".$dto->inifileName;
		} else if($dto->Type == "templates") {
			$filename = $this->config->getValue("WEBAPP_THEME_DIR");
			if($dto->inifileName != "block.html") {
				$filename .= "/".$theme_name . "/templates";	//japanese固定
			} else {
				$pathList = explode("_", $dto->actionName);
				if(isset($pathList[1])) {
					$filename .= "/".$theme_name . "/templates/".$pathList[1];
				} else {
					$filename .= "/".$theme_name . "/templates";
				}
			}
	        $filename .= "/".$dto->inifileName;
		}
          
        return $this->output(
            $filename,
            array('action_name'=>$dto->actionName,
					'theme_name'=> $theme_name,
            		'second_name'=> $second_name,
            		'theme_type'=> $dto->themeType),
            'CONFIG_CODE',
            $this->getTemplateFile($dto->skeletonTemplateName));
    }

}
?>
