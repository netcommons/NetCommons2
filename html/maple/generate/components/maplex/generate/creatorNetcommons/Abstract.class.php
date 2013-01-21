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
 * @version     CVS: $Id: Abstract.class.php,v 1.1 2006/10/18 08:55:26 Ryuji.M Exp $
 */

/**
 * craetorLogicのインターフェイスを規定
 *
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @abstract
 */
class Maplex_Generate_CreatorNetcommons_Abstract
{
    /**
     * @var  GlobalConfig  $config  
     */
    var $config;

    /**
     * このメソッドの中でやることは
     * 
     * 1. outputFile名を生成し、
     * 2. templateが必要とする情報を連想配列にまとめ
     * 3. outputメソッドに与える
     * 
     * 複数のcreatorNetcommonsのcompositeの場合は
     * 
     * 1. 順に子creatorNetcommons->create($dto)を呼び出して、
     * 2. 結果を一つの配列にまとめる
     * 
     * @abstract
     * @param  Object $dto
     * @return array
     */
    function create(&$dto)
    {
        
    }

    /**
     * createのシノニム
     * 
     * @param  Object $dto
     * @return array
     */
    function execute(&$dto)
    {
        return $this->create($dto);
    }
}
?>
