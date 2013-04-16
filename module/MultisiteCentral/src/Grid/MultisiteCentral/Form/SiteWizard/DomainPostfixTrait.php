<?php

namespace Grid\MultisiteCentral\Form\SiteWizard;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DomainPostfixTrait
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 * @implements \Zend\ServiceManager\ServiceLocatorAwareInterface
 */
trait DomainPostfixTrait
{

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var string
     */
    protected $domainPostfix;

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return self
     */
    public function setServiceLocator( ServiceLocatorInterface $serviceLocator )
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomainPostfix()
    {
        if ( null === $this->domainPostfix )
        {
            $this->domainPostfix = $this->getServiceLocator()
                                        ->get( 'Config' )
                                             [ 'modules' ]
                                             [ 'Grid\MultisiteCentral' ]
                                             [ 'domainPostfix' ];
        }

        return $this->domainPostfix;
    }

}
