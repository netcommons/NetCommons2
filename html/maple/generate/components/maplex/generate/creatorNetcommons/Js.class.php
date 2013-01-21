<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * files/js/{temp_name}/{module_dir}.jsファイルを生成する
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

class Maplex_Generate_CreatorNetcommons_Js extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
    	$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/files/js/".$dto->templateDir."/".$dto->jsFile;
		$dir_name = $dto->moduleName;
		
        return $this->output(
            $filename,
            array('dir_name'=> $dir_name
                  ),
            'SCRIPT_CODE',
            $this->getTemplateFile('javascript.js'));
       
    }
}
?>
