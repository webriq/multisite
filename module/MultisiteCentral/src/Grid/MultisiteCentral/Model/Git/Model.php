<?php

namespace Grid\MultisiteCentral\Model\Git;

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
     * @param \MultisiteCentral\Model\Git\Mapper $gitMapper
     */
    public function __construct( Mapper $gitMapper )
    {
        $this->setMapper( $gitMapper );
    }

    /**
     * Get paginator for listing
     *
     * @param  int|null $ownerId
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator()
    {
        return $this->getMapper()
                    ->getPaginator();
    }

}
