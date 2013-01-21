<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * install.iniファイルを生成する
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

class Maplex_Generate_CreatorNetcommons_Installini extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
    	$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/install.ini";
    	
    	$action_name = $dto->moduleName."_view_main_init";
    	if($dto->moduleType == "full") {
    		$edit_action_name = $dto->moduleName."_view_edit_init";
    		$edit_style_action_name = $dto->moduleName."_view_edit_style";
    	}else if($dto->moduleType == "simple") {
    		$edit_action_name = "";
    		$edit_style_action_name = "";
    	} else {
    		$edit_action_name = $dto->moduleName."_view_edit_init";
    		//$edit_style_action_name = "";
    		$edit_style_action_name = $dto->moduleName."_view_edit_style";
    	}
    	
    	$dir_name = $dto->moduleName;
    	$theme_name = ""; 
    	$temp_name = "default";
    	//TODO:後に修正
    	if($dto->moduleType == "full" || $dto->moduleType == "normal") {
    		$whatnew_flag = 1;
    		$search_action = $dto->moduleName."_view_admin_search";
    		$delete_action = $dto->moduleName."_action_admin_delete";
    		$block_delete_action = "";
    		$move_action = $dto->moduleName."_action_admin_operation";
    		$copy_action = $dto->moduleName."_action_admin_operation";
    		$shortcut_action = $dto->moduleName."_action_admin_operation";
    		$personalinf_action = $dto->moduleName."_view_admin_personalinf";
    		$block_add_action = "";
    	} else {
    		$whatnew_flag = 0;
    		$search_action = "";
    		$delete_action = "";
    		$block_delete_action = "";
    		$move_action = "";
    		$copy_action = "";
    		$shortcut_action = "";
    		$personalinf_action = "";
    		$block_add_action = "";
    	}
    	$module_install_action = "";
    	$module_update_action = "";
    	$module_uninstall_action = "";

        return $this->output(
            $filename,
            array('moduletype' => $dto->moduleType,
				  'action_name' => $action_name,
                  'edit_action_name'=> $edit_action_name,
                  'edit_style_action_name'=> $edit_style_action_name,
                  'dir_name'=> $dir_name,
                  'theme_name'=> $theme_name,
                  'temp_name'=> $temp_name,
                  'whatnew_flag'=> $whatnew_flag,
                  'search_action'=> $search_action,
                  'delete_action'=> $delete_action,
                  'block_add_action'=> $block_add_action,
                  'block_delete_action'=> $block_delete_action,
                  'move_action'=> $move_action,
                  'copy_action'=> $copy_action,
                  'shortcut_action'=> $shortcut_action,
                  'personalinf_action'=> $personalinf_action,
                  'module_install_action'=> $module_install_action,
                  'module_update_action'=> $module_update_action,
                  'module_uninstall_action'=> $module_uninstall_action
                  ),
            'CONFIG_CODE',
            $this->getTemplateFile('install.ini'));
    }
}
?>
