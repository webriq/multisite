<?php

return array(
    'router' => array(
        'routes' => array(
            'Grid\DomainManager\Domain\Create' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/app/:locale/admin/domain/create',
                    'defaults' => array(
                        'controller' => 'Grid\DomainManager\Controller\Domain',
                        'action'     => 'edit',
                    ),
                ),
            ),
            'Grid\DomainManager\Domain\Edit' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'         => '/app/:locale/admin/domain/edit/:id',
                    'constraints'   => array(
                        'id'        => '[1-9][0-9]*',
                    ),
                    'defaults'      => array(
                        'controller'    => 'Grid\DomainManager\Controller\Domain',
                        'action'        => 'edit',
                    ),
                ),
            ),
            'Grid\DomainManager\Domain\List' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/app/:locale/admin/domain/list',
                    'defaults' => array(
                        'controller' => 'Grid\DomainManager\Controller\Domain',
                        'action'     => 'list',
                    ),
                ),
            ),
            'Grid\DomainManager\Domain\Delete' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'         => '/app/:locale/admin/domain/delete/:id',
                    'constraints'   => array(
                        'id'        => '[1-9][0-9]*',
                    ),
                    'defaults'      => array(
                        'controller'    => 'Grid\DomainManager\Controller\Domain',
                        'action'        => 'delete',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Grid\DomainManager\Controller\Domain' => 'Grid\DomainManager\Controller\DomainController'
        ),
    ),
    'modules' => array(
        'Grid\Core' => array(
            'dashboardIcons' => array(
                'domain' => array(
                    'order'         => 6,
                    'label'         => 'admin.navTop.domain',
                    'textDomain'    => 'admin',
                    'route'         => 'Grid\DomainManager\Domain\List',
                    'resource'      => 'domain',
                    'privilege'     => 'view',
                ),
            ),
            'navigation'    => array(
                'settings'  => array(
                    'pages' => array(
                        'domains'  => array(
                            'order'         => 6,
                            'label'         => 'admin.navTop.domain',
                            'textDomain'    => 'admin',
                            'route'         => 'Grid\DomainManager\Domain\List',
                            'parentOnly'    => true,
                            'pages'         => array(
                                'list'      => array(
                                    'order'         => 1,
                                    'label'         => 'admin.navTop.domainList',
                                    'textDomain'    => 'admin',
                                    'route'         => 'Grid\DomainManager\Domain\List',
                                    'resource'      => 'domain',
                                    'privilege'     => 'view',
                                ),
                                'create'    => array(
                                    'order'         => 2,
                                    'label'         => 'admin.navTop.domainCreate',
                                    'textDomain'    => 'admin',
                                    'route'         => 'Grid\DomainManager\Domain\Create',
                                    'resource'      => 'domain',
                                    'privilege'     => 'create',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'form' => array(
        'Grid\DomainManager\Domain' => array(
            'elements'  => array(
                'id'    => array(
                    'spec'  => array(
                        'type'  => 'Zork\Form\Element\Hidden',
                        'name'  => 'id',
                    ),
                ),
                'domain'    => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Text',
                        'name'      => 'domain',
                        'options'   => array(
                            'label'             => 'domain.form.domain',
                            'pattern'           => '[^\s()<>\./]+(\.[^\s()<>\./]+)+',
                            'rpc_validators'    => array(
                                'Grid\MultisitePlatform\Model\Domain\Rpc::isDomainAvailable',
                            ),
                        ),
                    ),
                ),
                'save' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Submit',
                        'name'      => 'save',
                        'attributes'    => array(
                            'value'     => 'domain.form.submit',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            'domain'            => array(
                'type'          => 'phpArray',
                'base_dir'      => __DIR__ . '/../languages/domain',
                'pattern'       => '%s.php',
                'text_domain'   => 'domain',
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'grid/domain-manager/domain/edit' => __DIR__ . '/../view/grid/domain-manager/domain/edit.phtml',
            'grid/domain-manager/domain/list' => __DIR__ . '/../view/grid/domain-manager/domain/list.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
