<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

/**
 * files/css/{temp_name}/style.cssファイルを生成する
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */
class Maplex_Generate_CreatorNetcommons_Css extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
    	if($dto->csstype != "global")
    		$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/files/css/".$dto->templateDir."/".$dto->cssFile;
		else
			$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/files/css/".$dto->cssFile;
		
		$dir_name = $dto->moduleName;
		$csstype = $dto->csstype;
		
        return $this->output(
            $filename,
            array('csstype' => $csstype,
                  'dir_name'=> $dir_name
                  ),
            'SCRIPT_CODE',
            $this->getTemplateFile('style.css'));
    }
}
?>
