<?php

return array(
    'router' => array(
        'routes' => array(
            'Grid\MultisiteCentral\Welcome\Index' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/central/welcome',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisiteCentral\Controller\Welcome',
                        'action'     => 'index',
                    ),
                ),
            ),
            'Grid\MultisiteCentral\Welcome\DeleteSite' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/central/delete-site/:id',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisiteCentral\Controller\Welcome',
                        'action'     => 'delete-site',
                    ),
                ),
            ),
            'Grid\MultisiteCentral\SiteAdmin\List' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/admin/central/sites',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisiteCentral\Controller\SiteAdmin',
                        'action'     => 'list',
                    ),
                ),
            ),
            'Grid\MultisiteCentral\SiteWizard\Confirm' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/central/site-wizard/confirm/:hash',
                    'defaults'  => array(
                        'controller'    => 'Grid\MultisiteCentral\Controller\SiteWizard',
                        'action'        => 'confirm',
                    ),
                ),
            ),
            'Grid\MultisiteCentral\SiteWizard\Step' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/central/site-wizard[/[:step]]',
                    'defaults'  => array(
                        'controller'    => 'Grid\MultisiteCentral\Controller\SiteWizard',
                        'action'        => 'step',
                        'step'          => 'start',
                    ),
                ),
            ),
            'Grid\MultisiteCentral\Git\List' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/admin/system/git/log',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisiteCentral\Controller\Git',
                        'action'     => 'list',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Grid\MultisiteCentral\Controller\Git'           => 'Grid\MultisiteCentral\Controller\GitController',
            'Grid\MultisiteCentral\Controller\Welcome'       => 'Grid\MultisiteCentral\Controller\WelcomeController',
            'Grid\MultisiteCentral\Controller\SiteAdmin'     => 'Grid\MultisiteCentral\Controller\SiteAdminController',
            'Grid\MultisiteCentral\Controller\SiteWizard'    => 'Grid\MultisiteCentral\Controller\SiteWizardController',
        ),
    ),
    'factory' => array(
        'Grid\Paragraph\Model\Paragraph\StructureFactory' => array(
            'adapter' => array(
                'siteWizard' => 'Grid\MultisiteCentral\Model\Paragraph\Structure\SiteWizard',
            ),
        ),
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            'central' => array(
                'type'          => 'phpArray',
                'base_dir'      => __DIR__ . '/../languages/central',
                'pattern'       => '%s.php',
                'text_domain'   => 'central',
            ),
        ),
    ),
    'modules'   => array(
        'Grid\Core'  => array(
            'navigation'    => array(
                'central'   => array(
                    'label'         => 'admin.navTop.central',
                    'textDomain'    => 'admin',
                    'uri'           => '#',
                    'order'         => 900,
                    'parentOnly'    => true,
                    'pages'         => array(
                        'siteList'  => array(
                            'label'         => 'central.site.list.title',
                            'textDomain'    => 'central',
                            'route'         => 'Grid\MultisiteCentral\SiteAdmin\List',
                            'order'         => 1,
                            'resource'      => 'central.sites',
                            'privilege'     => 'view',
                        ),
                    ),
                ),
             /* 'system'    => array(
                    'pages' => array(
                        'gitLog'    => array(
                            'label'         => 'admin.navTop.system.gitLog',
                            'textDomain'    => 'admin',
                            'route'         => 'Grid\MultisiteCentral\Git\List',
                            'order'         => 2,
                            'resource'      => 'sysadmin.git',
                            'privilege'     => 'view',
                        ),
                    ),
                ), */
            ),
        ),
        'Grid\User' => array(
            'display' => array(
                'centralWelcomeLink' => true,
            ),
        ),
        'Grid\Paragraph' => array(
            'customizeSelectors' => array(
                'siteWizardForm'            => '#paragraph-%id%.paragraph.paragraph-%type% form',
                'siteWizardFormDlDt'        => '#paragraph-%id%.paragraph.paragraph-%type% form dl dt',
                'siteWizardFormDlDd'        => '#paragraph-%id%.paragraph.paragraph-%type% form dl dd',
                'siteWizardFormLabel'       => '#paragraph-%id%.paragraph.paragraph-%type% form label',
            ),
            'customizeMapForms' => array(
                'siteWizard'    => array(
                    'element'               => 'general',
                    'siteWizardForm'        => 'general',
                    'siteWizardFormDlDt'    => 'general',
                    'siteWizardFormDlDd'    => 'general',
                    'siteWizardFormLabel'   => 'basic',
                ),
            ),
        ),
        'Grid\MultisiteCentral'  => array(
            'schemaPrefix'  => 'site_',
            'schemaPostfix' => '_dev',
            'domainPostfix' => 'sites.local',
            'uploadsFiles'  => array(),
            'uploadsDirs'   => array(
                'pages',
                'settings',
                'snippets',
                'pages/images',
                'pages/documents',
            ),
        ),
    ),
    'form' => array(
        'Grid\Paragraph\CreateWizard\Start' => array(
            'elements'  => array(
                'type'  => array(
                    'spec'  => array(
                        'options'   => array(
                            'options'   => array(
                                'central'       => array(
                                    'label'     => 'paragraph.type-group.central',
                                    'order'     => 999,
                                    'options'   => array(
                                        'siteWizard' => 'paragraph.type.siteWizard',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'Grid\Paragraph\Meta\Edit' => array(
            'fieldsets' => array(
                'siteWizard' => array(
                    'spec'   => array(
                        'name'      => 'siteWizard',
                        'options'   => array(
                            'label'     => 'paragraph.type.siteWizard',
                            'required'  => false,
                        ),
                        'elements'  => array(
                            'name'  => array(
                                'spec'  => array(
                                    'type'      => 'Zork\Form\Element\Text',
                                    'name'      => 'name',
                                    'options'   => array(
                                        'label'     => 'paragraph.form.abstract.name',
                                        'required'  => false,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'Grid\MultisiteCentral\SiteWizard\Start' => array(
            'type'      => 'Grid\MultisiteCentral\Form\SiteWizard\Start',
            'elements'  => array(
                'subdomain' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Text',
                        'name'      => 'subdomain',
                        'options'   => array(
                            'required'  => true,
                            'label'     => 'central.site.create.subDomain',
                            'pattern'   => '[A-Za-z\d]+(-[A-Za-z\d]+)*',
                            'minlength' => 3,
                            'maxlength' => 16,
                            'rpc_validators'    => array(
                                'Grid\MultisitePlatform\Model\Domain\Rpc::isSubdomainAvailable',
                            ),
                        ),
                    ),
                ),
                'terms' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Checkbox',
                        'name'      => 'terms',
                        'options'   => array(
                            'required'      => true,
                            'label'         => null,
                            'label_enable'  => 'central.site.create.terms',
                        ),
                    ),
                ),
            ),
        ),
        'Grid\MultisiteCentral\SiteWizard\User' => array(
            'elements'  => array(
                'displayName' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Text',
                        'name'      => 'displayName',
                        'options'   => array(
                            'required'  => true,
                            'label'     => 'central.site.create.displayName',
                            'rpc_validators'    => array(
                                'Grid\User\Model\User\Rpc::isDisplayNameAvailable',
                            ),
                        ),
                    ),
                ),
                'email' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Email',
                        'name'      => 'email',
                        'options'   => array(
                            'required'  => true,
                            'label'     => 'central.site.create.email',
                            'rpc_validators'    => array(
                                'Grid\User\Model\User\Rpc::isEmailAvailable',
                            ),
                        ),
                    ),
                ),
                'password' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Password',
                        'name'      => 'password',
                        'options'   => array(
                            'required'  => true,
                            'label'     => 'central.site.create.password',
                        ),
                    ),
                ),
                'passwordVerify'    => array(
                    'spec'          => array(
                        'type'      => 'Zork\Form\Element\Password',
                        'name'      => 'passwordVerify',
                        'options'   => array(
                            'label'     => 'central.site.create.passwordVerify',
                            'required'  => true,
                            'identical' => 'password',
                        ),
                    ),
                ),
            ),
        ),
        'Grid\MultisiteCentral\SiteWizard\Layout' => array(
            'elements'  => array(
                'layout' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\RadioGroupModel',
                        'name'      => 'layout',
                        'options'   => array(
                            'label'     => '', // 'central.site.create.layout',
                            'required'  => true,
                            'model'     => 'Grid\Paragraph\Model\Paragraph\Model',
                            'method'    => 'findOptions',
                            'arguments' => array(
                                'layout',
                                '_central',
                            ),
                        ),
                        'attributes' => array(
                            'data-js-type'                          => 'js.layoutSelect',
                            'data-js-layoutselect-imagesrc'         => '/uploads/_central/pages/layouts/[value]/thumb.png',
                            'data-js-layoutselect-type'             => 'import',
                        ),
                    ),
                ),
            ),
        ),
        'Grid\MultisiteCentral\SiteWizard\Content' => array(
            'elements'  => array(
                'content' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\RadioGroupModel',
                        'name'      => 'content',
                        'options'   => array(
                            'label'     => '', // 'central.site.create.content',
                            'required'  => true,
                            'model'     => 'Grid\Paragraph\Model\Paragraph\Model',
                            'method'    => 'findOptions',
                            'arguments' => array(
                                'content',
                                '_central',
                                'created',
                            ),
                        ),
                        'attributes'    => array(
                            'data-js-type'                              => 'js.form.imageRadioGroup',
                            'data-js-imageradiogroup-itemsperrow'       => '3',
                            'data-js-imageradiogroup-class'             => 'default align-center',
                            'data-js-imageradiogroup-imagesrc'          => '/images/common/admin/paragraph-content-type/[value].png',
                            'data-js-imageradiogroup-descriptionkey'    => 'paragraph.form.content.model.[value].description',
                            'data-js-imageradiogroup-fieldsettabs'      => 'false',
                        ),
                    ),
                ),
                'locale' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Locale',
                        'name'      => 'locale',
                        'options'   => array(
                            'required'      => true,
                            'label'         => 'central.site.create.locale',
                            'only_enabled'  => false,
                        ),
                    ),
                ),
                'title' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Text',
                        'name'      => 'title',
                        'options'   => array(
                            'required'  => true,
                            'label'     => 'central.site.create.title',
                        ),
                    ),
                ),
            ),
        ),
        'Grid\MultisiteCentral\SiteWizard\Settings' => array(
            'elements'  => array(
                'headTitle' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Text',
                        'name'      => 'headTitle',
                        'options'   => array(
                            'required'  => false,
                            'label'     => 'central.site.create.headTitle',
                        ),
                    ),
                ),
                'keywords' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Textarea',
                        'name'      => 'keywords',
                        'options'   => array(
                            'required'  => false,
                            'label'     => 'central.site.create.keywords',
                        ),
                    ),
                ),
                'description' => array(
                    'spec'  => array(
                        'type'      => 'Zork\Form\Element\Textarea',
                        'name'      => 'description',
                        'options'   => array(
                            'required'  => false,
                            'label'     => 'central.site.create.description',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'grid/multisite-central/welcome/index'           => __DIR__ . '/../view/grid/multisite-central/welcome/index.phtml',
            'grid/multisite-central/welcome/site-wizard'     => __DIR__ . '/../view/grid/multisite-central/welcome/site-wizard.phtml',
            'grid/multisite-central/welcome/site-list'       => __DIR__ . '/../view/grid/multisite-central/welcome/site-list.phtml',
            'grid/multisite-central/site-admin/list'         => __DIR__ . '/../view/grid/multisite-central/site-admin/list.phtml',
            'grid/multisite-central/site-wizard/cancel'      => __DIR__ . '/../view/grid/multisite-central/site-wizard/cancel.phtml',
            'grid/multisite-central/site-wizard/check'       => __DIR__ . '/../view/grid/multisite-central/site-wizard/check.phtml',
            'grid/multisite-central/site-wizard/description' => __DIR__ . '/../view/grid/multisite-central/site-wizard/description.phtml',
            'grid/multisite-central/site-wizard/finish'      => __DIR__ . '/../view/grid/multisite-central/site-wizard/finish.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
