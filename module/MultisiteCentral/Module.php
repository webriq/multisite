<?php

namespace Grid\MultisiteCentral;

use Locale;
use Zork\Stdlib\ModuleAbstract;
use Grid\User\Authentication\Event as AuthEvent;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;

/**
 * MultisitePlafrom\Module
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Module extends ModuleAbstract
          implements InitProviderInterface
{

    /**
     * Module base-dir
     *
     * @const string
     */
    const BASE_DIR = __DIR__;

    /**
     * Initialize workflow
     *
     * @param  ModuleManagerInterface $manager
     * @return void
     */
    public function init( ModuleManagerInterface $manager )
    {
        $manager->getEventManager()
                ->getSharedManager()
                ->attach(
                    'Grid\User\Authentication\Service',
                    AuthEvent::EVENT_LOGIN,
                    array( $this, 'onLogin' ),
                    100
                );
    }

    /**
     * On login handler
     *
     * @param \User\Authentication\Event $event
     * @return void
     */
    public function onLogin( AuthEvent $event )
    {
        if ( $event->getResult()->isValid() )
        {
            $event->setReturnUri(
                '/app/' . Locale::getDefault() . '/central/welcome'
            );
        }
    }

}
