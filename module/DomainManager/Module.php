<?php

namespace Grid\DomainManager;

use Zork\Stdlib\ModuleAbstract;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

/**
 * Grid\Customize\Module
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Module extends ModuleAbstract
          implements DependencyIndicatorInterface
{

    /**
     * Module base-dir
     *
     * @const string
     */
    const BASE_DIR = __DIR__;

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array
     */
    public function getModuleDependencies()
    {
        return array(
            'Grid\MultisitePlatform',
        );
    }

}
