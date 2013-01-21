<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * sql/mysql/table.sqlファイルを生成する
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

class Maplex_Generate_CreatorNetcommons_Sql extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
    	$filename = $this->config->getValue("WEBAPP_MODULE_DIR")."/".$dto->moduleName."/sql/mysql/table.sql";
		$dir_name = $dto->moduleName;
		
        return $this->output(
            $filename,
            array('dir_name'=> $dir_name
                  ),
            'SCRIPT_CODE',
            $this->getTemplateFile('table.sql'));
       
    }
}
?>
