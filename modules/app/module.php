<?php

use THCFrame\Module\Module as Module;

/**
 * Description of Module
 *
 * @author Tomy
 */
class App_Module extends Module{

    /**
     * @read
     */
    protected $_moduleName = "App";
    
    protected $_routes = array(
        array(
            'pattern' => '/login',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'login',
        ),
        array(
            'pattern' => '/logout',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'logout',
        ),
        
        array(
            'pattern' => '/steptwo',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'steptwo',
        ),
        array(
            'pattern' => '/stepthree',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'stepthree',
        ),
        array(
            'pattern' => '/stepfour',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'stepfour',
        ),
        array(
            'pattern' => '/potvrzeni',
            'module' => 'app',
            'controller' => 'konzultace',
            'action' => 'potvrzeni',
        )
    );
}