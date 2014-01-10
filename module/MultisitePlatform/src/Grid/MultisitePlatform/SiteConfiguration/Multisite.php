<?php

namespace Grid\MultisitePlatform\SiteConfiguration;

use Zork\Db\SiteInfo;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zork\Db\SiteConfiguration\RedirectionService;
use Zork\Db\SiteConfiguration\AbstractDomainAware;
use Zend\ServiceManager\Exception;

/**
 * Multisite
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Multisite extends AbstractDomainAware
{

    /**
     * Setup services which depends on the db
     *
     * @param   \Zend\Db\Adapter\Adapter $db
     * @return  \Zend\Db\Adapter\Adapter
     */
    public function configure( DbAdapter $db )
    {
        $sm         = $this->getServiceLocator();
        $platform   = $db->getPlatform();
        $driver     = $db->getDriver();
        $domain     = strtolower( $this->getDomain() );

        $query = $db->query( '
            SELECT *
              FROM ' . $platform->quoteIdentifier( '_central' ) . '.'
                     . $platform->quoteIdentifier( 'fulldomain' ) . '
             WHERE ' . $platform->quoteIdentifier( 'fulldomain' ) . '
                 = LOWER( ' . $driver->formatParameterName( 'fulldomain' ) . ' )
        ' );

        $result = $query->execute( array(
            'fulldomain' => $domain
        ) );

        if ( $result->getAffectedRows() > 0 )
        {
            foreach ( $result as $data )
            {
                $info = new SiteInfo( $data );
                $sm->setService( 'SiteInfo', $info );

                $driver->getConnection()
                       ->setCurrentSchema( $info->getSchema() );

                return $db;
            }
        }
        else
        {
            $parts = explode( '.', $domain );
            $subParts = array();
            $mainDomain = false;

            while ( count( $parts ) > 2 )
            {
                $subParts[] = array_shift( $parts );
                $mainDomain = implode( '.', $parts );

                $result = $query->execute( array(
                    'fulldomain' => $mainDomain
                ) );

                if ( $result->getAffectedRows() > 0 )
                {
                    break;
                }

                $mainDomain = false;
            }

            if ( $mainDomain )
            {
                $sm->setService(
                    'RedirectToDomain',
                    new RedirectionService(
                        $mainDomain,
                        sprintf(
                            'sub-domain "%s" not found',
                            implode( '.', $subParts )
                        ),
                        true
                    )
                );
            }
            else
            {
                $config = $driver->getConnection()
                                 ->getConnectionParameters();

                if ( empty( $config['defaultDomain'] ) )
                {
                    throw new Exception\InvalidArgumentException(
                        'Domain not found, and default domain not set'
                    );
                }
                else
                {
                    $sm->setService(
                        'RedirectToDomain',
                        new RedirectionService(
                            $config['defaultDomain'],
                            'domain not found',
                            false
                        )
                    );
                }
            }
        }

        return $db;
    }

}
