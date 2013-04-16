<?php

namespace Grid\MultisiteCentral\Model\Site;

use Zork\Model\Structure\MapperAwareAbstract;

/**
 * Structure
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Structure extends MapperAwareAbstract
{

    /**
     * Field: id
     *
     * @var int
     */
    protected $id;

    /**
     * Field: schema
     *
     * @var string
     */
    protected $schema;

    /**
     * Field: ownerId
     *
     * @var int
     */
    protected $ownerId;

    /**
     * Field: created
     *
     * @var string
     */
    protected $created;

    /**
     * Field: domains
     *
     * @var array
     */
    private $domains;

    /**
     * @return string
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param array|string|null $domains
     * @return \MultisiteCentral\Model\Site\Structure
     */
    public function setDomains( $domains )
    {
        if ( $domains === null )
        {
            $domains = array();
        }
        else if ( ! is_array( $domains ) )
        {
            $domains = array_map( 'trim', explode( "\n", $domains ) );
        }

        $this->domains = $domains;
        return $this;
    }

}
