<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * filterファイルを書き出す
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

class Maplex_Generate_CreatorNetcommons_Filter extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    function create(&$dto)
    {
        $classname = "Filter_" . ucfirst($dto->filterName);
		$filename = $this->config->getValue('MAPLE_FILTER_DIR') ."/${classname}.class.php";
        return $this->output(
            $filename,
            array('classname' => $classname));
    }
}

?>
