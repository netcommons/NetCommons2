<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Generator.class.php,v 1.1 2006/10/13 08:50:13 Ryuji.M Exp $
 */

/**
 * ファイルを生成する
 *
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @access      public
 */
class Action_Generator
{
    var $dto;
    var $logic;

    var $_fileList;

    /**
     * ファイルを生成する
     *
     * @access  public
     */
    function execute()
    {
        $this->prepareDto($this->dto);
        
        $this->_fileList = $this->logic->execute($this->dto);
        return 'success';
    }

    /**
     * DTOを初期化
     * 
     * @since 06/07/17 13:34
     * @param  Object $dto
     */
    function prepareDto(&$dto)
    {

    }

    /**
     * 生成したファイルリストを返却
     *
     * @return  array   生成したファイルリスト
     * @access  public
     */
    function getFileList()
    {
        return $this->_fileList;
    }
}

?>
