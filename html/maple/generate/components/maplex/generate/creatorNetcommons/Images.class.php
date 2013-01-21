<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once('maplex/generate/creatorNetcommons/Abstract.class.php');

/**
 * images下を「{module_dir}/files/images/{theme_name}/」にコピーする
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */
class Maplex_Generate_CreatorNetcommons_Images extends Maplex_Generate_CreatorNetcommons_Abstract
{
	/**
     * @var  FileWriter  $writer  
     */
    var $writer;
    
    function create(&$dto)
    {
    	if(!$dto->copyImageDir)
    		$dto->copyImageDir = IMAGES_DIR;
    	
    	if($dto->copyImageDir == THEME_IMAGES_DIR."thumbnail/") {
    		$filename = $this->config->getValue("WEBAPP_THEME_DIR");
    		$pathList = explode("_", $dto->actionName);
			if(isset($pathList[1])) {
				$filename .= "/".$pathList[0] . "/images/".$pathList[1]."/";
			} else {
				$filename .= "/".$pathList[0] . "/images/";
			}
    	} else if($dto->copyImageDir == THEME_IMAGES_DIR."images/") {
    		$filename = $this->config->getValue("WEBAPP_THEME_DIR");
    		$filename .= "/".$dto->themeName . "/images/";
    	} else if($dto->templateDir) {
    		$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/files/images/".$dto->templateDir."/";
    	} else {
    		$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/files/images/";	
    	}
		
        return $this->writer->write(
            $filename,
            $dto->copyImageDir
            );
    }
}
?>
