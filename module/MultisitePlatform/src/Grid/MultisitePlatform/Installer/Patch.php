<?php

namespace Grid\MultisitePlatform\Installer;

use Grid\Installer\AbstractPatch;

/**
 * Patch
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 *
 * @method \PDO getDb()
 */
class Patch extends AbstractPatch
{

    /**
     * @const int
     */
    const SITE_OWNER_GROUP = 2;

    /**
     * Run after patching
     *
     * @param   string  $from
     * @param   string  $to
     * @return  void
     */
    public function afterPatch( $from, $to )
    {
        if ( $this->isZeroVersion( $from ) )
        {
            $platformOwner = $this->selectFromTable( 'user', 'id', array(
                'groupId' => static::SITE_OWNER_GROUP,
            ) );

            $schema = $this->getPatchData()
                           ->get( 'db', 'schema' );

            if ( is_array( $schema ) )
            {
                $schema = reset( $schema );
            }

            $site = $this->selectFromTable( array( '_central', 'site' ), 'id', array(
                'schema' => $schema,
            ) );

            if ( ! $site )
            {
                $site = $this->insertDefaultSite( $platformOwner, $schema );
            }

            $domain = $this->selectFromTable( array( '_central', 'domain' ), 'id', array(
                'siteId' => $site,
            ) );

            if ( ! $domain )
            {
                $domain = $this->insertDefaultDomain( $site );
            }
        }
    }

    /**
     * Insert default site
     *
     * @param   int     $owner
     * @param   string  $schema
     * @retirn  int
     */
    protected function insertDefaultSite( $owner, $schema )
    {
        return $this->insertIntoTable(
            array( '_central', 'site' ),
            array(
                'schema'    => $schema,
                'ownerId'   => $owner,
            ),
            true
        );
    }

    /**
     * Insert default domain
     *
     * @param   int $site
     * @retirn  int
     */
    protected function insertDefaultDomain( $site )
    {
        $data   = $this->getPatchData();
        $domain = $data->get(
            'gridguyz-multisite',
            'domain',
            'Type the default domain name',
            $this->getDefaultDomain(),
            '/([a-z0-9\-]+\.)+[a-z]{2,}/i',
            3
        );

        return $this->insertIntoTable(
            array( '_central', 'domain' ),
            array(
                'domain'    => $domain,
                'siteId'    => $site,
            )
        );
    }

    /**
     * Get default domain
     *
     * @return  string|null
     */
    private function getDefaultDomain()
    {
        if ( ! empty( $_SERVER['HTTP_HOST'] ) )
        {
            return $_SERVER['HTTP_HOST'];
        }

        if ( ! empty( $_SERVER['SERVER_NAME'] ) )
        {
            return $_SERVER['SERVER_NAME'];
        }

        if ( ! empty( $_SERVER['SERVER_ADDR'] ) )
        {
            return @gethostbyaddr( $_SERVER['SERVER_ADDR'] ) ?: null;
        }

        return null;
    }

}
