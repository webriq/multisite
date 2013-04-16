<?php

namespace Grid\MultisitePlatform\Authentication;

use Zend\Authentication\Result;
use Zork\Model\ModelAwareTrait;
use Zork\Model\ModelAwareInterface;
use Zork\Model\Structure\StructureAbstract;
use Zork\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zork\Factory\AdapterInterface as FactoryAdapterInterface;
use Zend\Authentication\Adapter\AdapterInterface as AuthAdapterInterface;

/**
 * AutoLoginAdapter
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class AutoLoginAdapter extends StructureAbstract
                    implements ModelAwareInterface,
                               AuthAdapterInterface,
                               FactoryAdapterInterface,
                               ServiceLocatorAwareInterface
{

    use ModelAwareTrait,
        ServiceLocatorAwareTrait;

    /**
     * @var string
     */
    protected $autoLoginToken;

    /**
     * Return true if and only if $options accepted by this adapter
     * If returns float as likelyhood the max of these will be used as adapter
     *
     * @param  array $options;
     * @return float
     */
    public static function acceptsOptions( array $options )
    {
        return isset( $options['autoLoginToken'] );
    }

    /**
     * Return a new instance of the adapter by $options
     *
     * @param  array $options;
     * @return Grid\MultisitePlatform\Authentication\AutoLoginAdapter
     */
    public static function factory( array $options = null )
    {
        return new static( $options );
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     *         If authentication cannot be performed
     */
    public function authenticate()
    {
        $token   = $this->autoLoginToken;
        $manager = $this->getServiceLocator()
                        ->get( __NAMESPACE__ . '\AutoLoginToken' );

        if ( ! $manager->has( $token ) ||
             ! ( $email = $manager->find( $token ) ) )
        {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                null
            );
        }

        $user = $this->getModel()
                     ->findByEmail( $email );

        $manager->delete( $token );

        return new Result(
            $user ? Result::SUCCESS
                  : Result::FAILURE_IDENTITY_NOT_FOUND,
            $user,
            array(
                'loginWith' => 'autoLogin',
            )
        );
    }

}
