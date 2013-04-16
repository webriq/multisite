<?php

namespace Grid\MultisitePlatform\Model\Domain;

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
    protected $domain;

    /**
     * Field: siteId
     *
     * @var int
     */
    protected $siteId;

    /**
     * Set domain
     *
     * @param string $domain
     * @return \MultisitePlatform\Model\Domain\Structure
     */
    public function setDomain( $domain )
    {
        $domain = @ idn_to_ascii( $domain );

        if ( ! empty( $domain ) )
        {
            $this->domain = $domain;
        }

        return $this;
    }

    /**
     * Get internationalized domain name
     *
     * @return null|false|string
     */
    public function getIdn()
    {
        if ( empty( $this->domain ) )
        {
            return null;
        }

        return @ idn_to_utf8( $this->domain );
    }

}
