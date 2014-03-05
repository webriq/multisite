<?php

namespace Grid\MultisitePlatform\Model\Domain;

use Zork\Model\MapperAwareTrait;
use Zork\Model\MapperAwareInterface;

/**
 * Model
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Model implements MapperAwareInterface
{

    use MapperAwareTrait;

    /**
     * Construct model
     *
     * @param   Mapper  $multisitePlatformDomainMapper
     */
    public function __construct( Mapper $multisitePlatformDomainMapper )
    {
        $this->setMapper( $multisitePlatformDomainMapper );
    }

    /**
     * Get paginator for listing
     *
     * @param   int|null    $siteId
     * @return  \Zend\Paginator\Paginator
     */
    public function getPaginator( $siteId = null )
    {
        if ( null === $siteId )
        {
            $where = array();
        }
        else
        {
            $where = array(
                'siteId' => (int) $siteId,
            );
        }

        return $this->getMapper()
                    ->getPaginator( $where );
    }

    /**
     * Create a new domain from data
     *
     * @param   array|null  $data
     * @return  Structure
     */
    public function create( $data )
    {
        return $this->getMapper()
                    ->create( $data );
    }

    /**
     * Find a domain by id
     *
     * @param   int $id
     * @return  Structure
     */
    public function find( $id )
    {
        return $this->getMapper()
                    ->find( $id );
    }

    /**
     * Find all domain (for a site)
     *
     * @param   int|null    $siteId
     * @return  Structure[]
     */
    public function findAll( $siteId = null )
    {
        if ( null === $siteId )
        {
            $where = array();
        }
        else
        {
            $where = array(
                'siteId' => (int) $siteId,
            );
        }

        return $this->getMapper()
                    ->findAll( $where );
    }

}
