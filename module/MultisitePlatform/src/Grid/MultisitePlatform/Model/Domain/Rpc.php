<?php

namespace Grid\MultisitePlatform\Model\Domain;

use Zork\Rpc\CallableTrait;
use Zork\Rpc\CallableInterface;
use Zork\Model\MapperAwareTrait;
use Zork\Model\MapperAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zork\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Rpc
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Rpc implements CallableInterface,
                     MapperAwareInterface,
                     ServiceLocatorAwareInterface
{

    use CallableTrait,
        MapperAwareTrait,
        ServiceLocatorAwareTrait;

    /**
     * Construct rpc
     *
     * @param \MultisiteCentral\Model\Domain\Mapper $userMapper
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function __construct( Mapper $userMapper,
                                 ServiceLocatorInterface $serviceLocator )
    {
        $this->setMapper( $userMapper )
             ->setServiceLocator( $serviceLocator );
    }

    /**
     * Is sub-domain available
     *
     * @param string $subDomain
     * @return bool
     */
    public function isSubdomainAvailable( $subDomain )
    {
        $config = $this->getServiceLocator()
                       ->get( 'Config' )
                            [ 'modules' ]
                            [ 'Grid\MultisiteCentral' ];

        $domain = $subDomain . $config[ 'domainPostfix' ];
        $schema = ( empty( $config[ 'schemaPrefix' ] )
                    ? '' : $config[ 'schemaPrefix' ] )
                . $subDomain
                . ( empty( $config[ 'schemaPostfix' ] )
                    ? '' : $config[ 'schemaPostfix' ] );

        return $this->isDomainAvailable( $domain )
            && ! $this->getMapper()
                      ->isSchemaExists( $schema );
    }

    /**
     * Is domain available
     *
     * @param string $domain
     * @return bool
     */
    public function isDomainAvailable( $domain, $values = null )
    {
        $domain = @ idn_to_ascii( $domain );

        if ( empty( $domain ) )
        {
            return false;
        }

        $config = $this->getServiceLocator()
                       ->get( 'Config' )
                            [ 'db' ];

        if ( ! empty( $config['defaultDomain'] ) &&
             $domain == $config['defaultDomain'] )
        {
            return false;
        }

        $parts = explode( '.', $domain );

        if ( count( $parts ) < 2 )
        {
            return false;
        }

        if ( is_object( $values ) )
        {
            $values = (array) $values;
        }

        if ( ! empty( $values['id'] ) )
        {
            $excludeId = (int) $values['id'];
        }
        else
        {
            $excludeId = null;
        }

        $mapper = $this->getMapper();
        $domain = array_pop( $parts );

        do
        {
            $domain = array_pop( $parts ) . '.' . $domain;

            if ( $mapper->isDomainExists( $domain, $excludeId ) )
            {
                return false;
            }
        }
        while ( ! empty( $parts ) );

        return true;
    }

}
