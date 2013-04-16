<?php

namespace Grid\MultisiteCentral\Model\Git;

use Zork\Model\MapperAwareTrait;
use Zork\Model\MapperAwareInterface;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * PaginatorAdapter
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class PaginatorAdapter implements AdapterInterface,
                                  MapperAwareInterface
{

    use MapperAwareTrait;

    /**
     * Where
     *
     * @var array
     */
    protected $where = null;

    /**
     * Is reverse
     *
     * @var bool
     */
    protected $isReversed = null;

    /**
     * Total item count
     *
     * @var integer
     */
    protected $rowCount = 100;

    /**
     * Constructor
     *
     * @param \MultisiteCentral\Model\Git\Mapper $mapper
     * @param array|null $where
     * @param bool|null $isReversed
     */
    public function __construct( Mapper $mapper, $where = null, $isReversed = null )
    {
        $this->setMapper( $mapper );

        if ( null !== $where )
        {
            $this->where = (array) $where;
        }

        if ( null !== $isReversed )
        {
            $this->isReversed = (bool) $isReversed;
        }
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset           Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems( $offset, $itemCountPerPage )
    {
        return $this->getMapper()
                    ->findAll( $this->where,
                               $this->isReversed,
                               $itemCountPerPage,
                               $offset );
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count()
    {
        return $this->rowCount;
    }

}
