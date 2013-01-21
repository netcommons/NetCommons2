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
 * @package     Maple.filter
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_SimpleView.class.php,v 1.6 2006/12/08 06:35:50 Ryuji.M Exp $
 */

require_once MAPLE_DIR .'/core/SimpleView4Maple.class.php';

/**
 * PHPのincludeを使用したViewの実行を行うFilter
 *
 * @package     Maple.filter
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class Filter_SimpleView extends Filter
{
    var $_classname = "Filter_SimpleView";

    var $_container;
    var $_log;
    var $_filterChain;
    var $_response;
    var $_actionChain;
    var $_token;
    var $_session;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.1.0
     */
    function Filter_SimpleView()
    {
        parent::Filter();
    }

    /**
     * Viewの処理を実行
     *
     * @access  public
     * @since   3.1.0
     */
    function execute()
    {
        $this->_container   =& DIContainerFactory::getContainer();
        $this->_log         =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_response    =& $this->_container->getComponent("Response");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_token       =& $this->_container->getComponent("Token");
        $this->_session     =& $this->_container->getComponent("Session");

        $this->_log->trace("{$this->_classname}の前処理が実行されました", "{$this->_classname}#execute");
        $this->_preFilter();
        
        $this->_filterChain->execute();

        $this->_log->trace("{$this->_classname}の後処理が実行されました", "{$this->_classname}#execute");
        $this->_postFilter();
    }
    
    /**
     * プリフィルタ
     *
     * @access  private
     * @since   3.1.0
     */
    function _preFilter()
    {
        // 何もしません。
    }

    /**
     * SimpleView4Mapleのインスタンスを返す
     * 
     * @return Object
     */
    function &_getSimpleView()
    {
        $action    =& $this->_actionChain->getCurAction();
        $errorList =& $this->_actionChain->getCurErrorList();
        $token     =& $this->_token;
        $session   =& $this->_session;
        if(isset($_SERVER['SCRIPT_NAME']))
        	$scriptName = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);
        else
        	$scriptName = "";   
        $simpleView =& SimpleView4Maple::getInstance();
        $simpleView->setAction($action);
        $simpleView->setErrorList($errorList);
        $simpleView->setToken($token);
        $simpleView->setSession($session);
        $simpleView->setScriptName($scriptName);
        return $simpleView;
    }
    
    /**
     * ポストフィルタ
     *
     * @access  private
     * @since   3.1.0
     */
    function _postFilter()
    {
        $view = $this->_response->getView();

        if ($view != "") {
            $template = $this->getAttribute($view);
            if ($template == "") {
                $this->_log->error("テンプレートファイルの取得に失敗しました", "{$this->_classname}#execute");
                exit;
            }

            if (preg_match("/location:/", $template)) {
                $url = preg_replace("/location:/", "", $template);
                $url = trim($url);
                $this->_response->setRedirect($url);
            } else if (preg_match("/action:/", $template)) {
                $action = preg_replace("/action:/", "", $template);
                $action = trim($action);
                $actionChain =& $this->_container->getComponent("ActionChain");
                $actionChain->add($action);
            } else {
                $simpleView =& $this->_getSimpleView();

                $alias = $this->getAttribute('_alias');
                $simpleView->setAliasFuncName($alias == "" ? 'h' : $alias);
                //if (!preg_match("/main:/", $template)) {
                //	$action_name = $this->_actionChain->getCurActionName();
				//	$pathList = explode("_", $action_name);
				//	$sub_main_template_dir = "/" . $pathList[0]. "/templates/";
				//	$main_template_dir = MODULE_DIR . $sub_main_template_dir;
				//	$simpleView->setTemplate($main_template_dir);
				//} 
                $result = $simpleView->fetch($template);

                if ($result != "") {
                    $this->_response->setResult($result);
                }
            }
        }

        $contentDisposition = $this->_response->getContentDisposition();
        $contentType        = $this->_response->getContentType();
        $result             = $this->_response->getResult();
        $redirect           = $this->_response->getRedirect();

        if ($redirect) {
            header("Location: ${redirect}");
        } else {
            if ($contentDisposition != "") {
                header("Content-disposition: ${contentDisposition}");
            }
            if ($contentType != "") {
                header("Content-type: ${contentType}");
            }

            print $result;
        }
    }
}

?>