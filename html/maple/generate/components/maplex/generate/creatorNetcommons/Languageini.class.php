<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * language/(lang)japanese/main.iniファイルを生成する
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */
require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');


class Maplex_Generate_CreatorNetcommons_Languageini extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
    	$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/language/".$dto->langDir."/"."main.ini";
		
		$module_type = $dto->moduleType;
		
		$main_path_list = ucfirst ($dto->moduleName)."_View_Main_Init";
		
		if($dto->moduleType == "normal" || $dto->moduleType == "full") {
			$edit_path_list = ucfirst ($dto->moduleName)."_View_Edit_Init";
			$edit_style_path_list = ucfirst ($dto->moduleName)."_View_Edit_Style";
		} else {
			$edit_path_list = "";
			$edit_style_path_list = "";
		}
		
		$dir_name = $dto->moduleName;
		
		
        return $this->output(
            $filename,
            array('dir_name' => $dir_name,
                  'module_type'=> $module_type,
                  'main_path_list'=> $main_path_list,
                  'edit_path_list'=> $edit_path_list,
                  'edit_style_path_list'=> $edit_style_path_list
                  ),
            'CONFIG_CODE',
            $this->getTemplateFile('main.ini'));
    }
}
?>
