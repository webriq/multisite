<?php

namespace Grid\MultisiteCentral\Model\Site;

use Zend\Db\Sql\Select;
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
     * @param \MultisiteCentral\Model\Site\Mapper $multisiteCentralSiteMapper
     */
    public function __construct( Mapper $multisiteCentralSiteMapper )
    {
        $this->setMapper( $multisiteCentralSiteMapper );
    }

    /**
     * Get paginator for listing
     *
     * @param  int|null $ownerId
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator( $ownerId = null )
    {
        if ( null === $ownerId )
        {
            $where = array();
            $joins = array(
                'owner' => array(
                    'table'     => array( 'owner' => 'user' ),
                    'where'     => 'owner.id = ownerId',
                    'columns'   => array(
                        'ownerEmail'    => 'email',
                    ),
                    'type'      => Select::JOIN_LEFT,
                ),
            );
        }
        else
        {
            $joins = array();
            $where = array(
                'ownerId' => (int) $ownerId,
            );
        }

        return $this->getMapper()
                    ->getPaginator(
                        $where,
                        array(), // order
                        array(), // columns
                        $joins
                    );
    }

    /**
     * Create a new site from data
     *
     * @param array|null $data
     * @return \MultisiteCentral\Model\Site\Structure
     */
    public function create( $data )
    {
        return $this->getMapper()
                    ->create( $data );
    }

    /**
     * Find a site by id
     *
     * @param int $id
     * @return \MultisiteCentral\Model\Site\Structure
     */
    public function find( $id )
    {
        return $this->getMapper()
                    ->find( $id );
    }

}
