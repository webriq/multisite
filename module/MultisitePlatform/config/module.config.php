<?php

return array(
    'router' => array(
        'routes' => array(
            'Grid\MultisitePlatform\Admin\Index' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/central',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisitePlatform\Controller\Admin',
                        'action'     => 'index',
                    ),
                ),
            ),
            'Grid\MultisitePlatform\Admin\NotAllowed' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/central/not-allowed',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisitePlatform\Controller\Admin',
                        'action'     => 'not-allowed',
                    ),
                ),
            ),
            'Grid\MultisitePlatform\AutoLogin\ByDomain' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/auto-login-to/:domain',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisitePlatform\Controller\AutoLogin',
                        'action'     => 'by-domain',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Grid\MultisitePlatform\Controller\Admin'       => 'Grid\MultisitePlatform\Controller\AdminController',
            'Grid\MultisitePlatform\Controller\AutoLogin'   => 'Grid\MultisitePlatform\Controller\AutoLoginController',
        ),
    ),
    'factory' => array(
        'Grid\User\Model\Authentication\AdapterFactory' => array(
            'adapter'    => array(
                'autoLogin' => 'Grid\MultisitePlatform\Authentication\AutoLoginAdapter',
            ),
        ),
    ),
    'modules' => array(
        'Grid\MultisitePlatform' => array(
            'navigation' => array(
                'backToPage' => array(
                    'order'         => 1,
                    'label'         => 'admin.navTop.backToPage',
                    'textDomain'    => 'admin',
                    'uri'           => '/',
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/middle/multisite-admin' => __DIR__ . '/../view/layout/middle/multisite-admin.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
