<?php

namespace Grid\MultisiteCentral\Model;

use Zork\Cache\AbstractCacheStorage;

/**
 * SiteWizardData
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class SiteWizardData extends AbstractCacheStorage
{

    /**
     * Save site-wizard data
     *
     * @param   string  $hash
     * @param   mixed   $data
     * @return  SiteWizardHash
     */
    public function save( $hash, $data )
    {
        $store = $this->getCacheStorage();
        $store->setItem( $hash, serialize( $data ) );
        return $this;
    }

    /**
     * Has site-wizard data available by hash
     *
     * @param   string  $hash
     * @return  bool
     */
    public function has( $hash )
    {
        return $this->getCacheStorage()
                    ->hasItem( $hash );
    }

    /**
     * Get site-wizard data by hash
     *
     * @param   string  $hash
     * @return  mixed   data
     */
    public function get( $hash )
    {
        return unserialize(
            $this->getCacheStorage()
                 ->getItem( $hash )
        );
    }

    /**
     * Delete site-wizard data by hash
     *
     * @param   string  $hash
     * @return  bool
     */
    public function delete( $hash )
    {
        return $this->getCacheStorage()
                    ->removeItem( $hash );
    }

}
