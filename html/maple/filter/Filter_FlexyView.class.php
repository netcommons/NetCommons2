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
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_FlexyView.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

require_once(MAPLE_DIR .'/flexy/Flexy_Flexy4Maple.class.php');
require_once(MAPLE_DIR .'/flexy/Flexy_ViewBase.class.php');
require_once(MAPLE_DIR .'/flexy/Flexy_FormElementFilter.class.php');
require_once(MAPLE_DIR .'/flexy/Flexy_ComponentElementFilter.class.php');

/**
 * テンプレートエンジンとして HTML_Template_Flexy を用いるためのViewフィルタ
 * 
 * @package     Maple.filter
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class Filter_FlexyView extends Filter
{
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_viewClassRoot = COMPONENT_DIR;

    /**
     * Constructor
     * 
     * 
     */
    function Filter_FlexyView()
    {
        parent::Filter();
    }
    
    /**
     * HTML_Template_Flexyによるレンダリングを行う
     * 
     * @access  private
     * @param   string  View's type (e.g "success")
     * @param   string  path/to/template.html
     * @param   DIContainer
     * @return  string
     */
    function _renderByFlexy($viewType, $template, &$container)
    {
        $actionChain =& $container->getComponent("ActionChain");
        $action      =& $actionChain->getCurAction();
        $errorList   =& $actionChain->getCurErrorList();
        $token       =& $container->getComponent("Token");
        $session     =& $container->getComponent("Session");
        
        $actionName  = $actionChain->getCurActionName();
        $viewClass = $this->_getFlexyControllerClass($actionName, $viewType, 'Flexy_ViewBase');

        $flexy =& new Flexy_Flexy4Maple();
        $obj =& new $viewClass();

        $flexy->addFilter(new Flexy_FormElementFilter($action));
        $flexy->addFilter(new Flexy_ComponentElementFilter($container, $flexy, $obj));
        $ret = $flexy->compile($template);
        if (is_a($ret,'PEAR_Error')) {
            $this->_log->error("テンプレートファイルのコンパイルに失敗しました", get_class($this)."#_renderByFlexy");
            return "";
        }
        
        $obj->setAction($action);
        $obj->setErrorList($errorList);
        if(is_object($token)) {
            $obj->setToken($token);
        }
        if(is_object($session)) {
            $obj->setSession($session);
        }
        $obj->prepare();
        
        return $flexy->bufferedOutputObject($obj);
    }
    
    /**
     * Viewクラスを検索する
     * 
     * 1. View_{ucfirst($viewType)}
     * 2. View_Default
     * 
     * の順で検索が行われる
     * ともに存在しなければ $defaultClassName が返却される
     * 複雑な検索ルールは廃止され、$actionNameは用いられない
     * 
     * @param   string  $actionName
     * @param   string  $viewType
     * @param   string  $defaultClassName
     * @return  string  Viewクラスの名前
     */
    function _getFlexyControllerClass($actionName, $viewType, $defaultClassName)
    {
        $classRoot = $this->_viewClassRoot;
        $viewTypeSp = "View_". ucfirst($viewType);
        $userDefault= "View_Default";
        
        $className = "";
        if(file_exists($classPath = $classRoot."/view/{$viewTypeSp}.class.php")) {
            $className = $viewTypeSp;
        } elseif(file_exists($classPath = $classRoot."/view/{$userDefault}.class.php")) {
            $className = $userDefault;
        }
        if($className != "") {
            require_once($classPath);
            return $className;
        }
        return $defaultClassName;
    }

    function _postfilter()
    {
        $log =& $this->_log;
        $container =& $this->_container;
        
        $response =& $container->getComponent("Response");
        $view = $response->getView();

        if ($view == "") {
            $this->_sendResponse($response);
            return ;
        }

        $template = $this->getAttribute($view);
        if ($template == "") {
            $log->error("テンプレートファイルの取得に失敗しました[${view}:${template}]", "Filter_FlexyView#_postfilter");
            exit;
        }

        if(preg_match('/action:(.+)/', $template, $matches)) {
            $actionName = trim($matches[1]);
            if(preg_match('/,\s*clear$/', $actionName)) {
                /* リクエストパラメータをリセット */
                $actionName = preg_replace('/,\s*clear$/', '', $actionName);
                $req =& $container->getComponent('Request');
                $req->_params = array();
            }

            $actionChain =& $container->getComponent("ActionChain");
            $actionChain->add($actionName);
            $log->trace("Filter_FlexyViewの中でforwardが実行されました", "Filter_FlexyView#_postfilter");
            return ;
        }elseif (preg_match("/location:/", $template)) {
            $url = preg_replace("/location:/", "", $template);
            $url = trim($url);
            $response->setRedirect($url);
        } else {
            $result = $this->_renderByFlexy($view, $template, $container);
            
            if ($result != "") {
                if(OUTPUT_CODE != INTERNAL_CODE) {
                    $result = mb_convert_encoding($result, OUTPUT_CODE, INTERNAL_CODE);
                }
                $response->setResult($result);
            }
        }
        
        $this->_sendResponse($response);
    }
    
    function _sendResponse(&$response)
    {
        $contentDisposition = $response->getContentDisposition();
        $contentType        = $response->getContentType();
        $result             = $response->getResult();
        $redirect           = $response->getRedirect();

        if ($redirect) {
            header("Location: ${redirect}");
        } else {
            if ($contentDisposition != "") {
                header("Content-disposition: ${contentDisposition}");
            }
            if ($contentType != "") {
                header("Content-type: ${contentType}");
            }

            echo $result;
        }
    }

    function _prefilter()
    {
        
    }
    
    /**
     * フィルタ処理を実行
     * 
     * @access  public
     */
    function execute()
    {
        $this->_container =& DIContainerFactory::getContainer();
        $this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $className = get_class($this);

    
        $this->_log->trace("{$className}の前処理が実行されました", "{$className}#execute");
        $this->_prefilter();

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$className}の後処理が実行されました", "{$className}#execute");
    }
}
?>
