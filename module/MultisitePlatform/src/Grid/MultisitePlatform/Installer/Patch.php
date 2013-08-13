<?php

namespace Grid\MultisitePlatform\Installer;

use LogicException;
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
    const DEVELOPER_GROUP = 1;

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
            $enabled = $this->selectFromTable( 'module', 'enabled', array(
                'module' => 'Grid\\MultisiteCentral',
            ) );

            if ( null === $enabled )
            {
                $this->insertIntoTable(
                    'module',
                    array(
                        'module'    => 'Grid\\MultisiteCentral',
                        'enabled'   => 't',
                    )
                );
            }
            else if ( $enabled === 'f' || ! $enabled )
            {
                $this->updateTable(
                    'module',
                    array(
                        'enabled'   => 't',
                    ),
                    array(
                        'module'    => 'Grid\\MultisiteCentral',
                    )
                );
            }

            $developer = $this->selectFromTable( 'user', 'id', array(
                'groupId' => static::DEVELOPER_GROUP,
            ) );

            if ( $developer )
            {
                $this->query( 'ALTER TABLE "_template"."user"
                                   DISABLE TRIGGER USER' );

                $this->query( 'INSERT INTO "_template"."user"
                                    SELECT *
                                      FROM "user"
                                     WHERE "id" = :developer',
                              array( 'developer' => $developer ) );

                $this->query( 'ALTER TABLE "_template"."user"
                                    ENABLE TRIGGER USER' );

                $this->query( 'SELECT setval( :sequence, :developer )',
                              array( 'sequence'  => '"_template"."user_id_seq"',
                                     'developer' => $developer ) );

                $this->query( 'INSERT INTO "_central"."user_unified" ( "siteId", "userId" )
                                    SELECT "id"         AS "siteId",
                                           :developer   AS "userId"
                                      FROM "_central"."site"',
                              array( 'developer' => $developer ) );
            }

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

            $domain = $this->selectFromTable( array( '_central', 'domain' ), 'domain', array(
                'siteId' => $site,
            ) );

            if ( ! $domain )
            {
                $domain = $this->insertDefaultDomain( $site );
            }

            $this->getInstaller()
                 ->convertToMultisite();

            $this->setupConfigs( $domain );
        }
    }

    /**
     * Insert default site
     *
     * @param   int     $owner
     * @param   string  $schema
     * @return  int
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
     * @param   int     $site
     * @return  string
     */
    protected function insertDefaultDomain( $site )
    {
        $domain = $this->getPatchData()->get(
            'gridguyz-multisite',
            'defaultDomain',
            'Type the default domain name',
            strtolower( php_uname( 'n' ) ),
            '/([a-z0-9\-]+\.)+[a-z]{2,}/i',
            3
        );

        $this->insertIntoTable(
            array( '_central', 'domain' ),
            array(
                'domain'    => $domain,
                'siteId'    => $site,
            )
        );

        return $domain;
    }

    /**
     * Setup config files
     *
     * @param   string  $defaultDomain
     * @return  void
     */
    protected function setupConfigs( $defaultDomain )
    {
        $installer = $this->getInstaller();

        $installer->mergeConfigData( 'db', array(
            'db' => array(
                'defaultDomain' => $defaultDomain,
            ),
        ) );

        $installer->mergeConfigData( 'application', array(
            'modules' => array(
                'Grid\MultisitePlatform',
            ),
            'service_manager' => array(
                'invokables' => array(
                    'SiteConfiguration' => 'Grid\MultisitePlatform\SiteConfiguration\Multisite',
                ),
            ),
        ) );

        $installer->mergeConfigData( 'multisite.local', array(
            'modules' => array(
                'Grid\MultisiteCentral' => array(
                    'schemaPrefix'  => 'site_',
                    'schemaPostfix' => '',
                    'domainPostfix' => $this->getPatchData()->get(
                        'gridguyz-multisite',
                        'domainPostfix',
                        'Type the sites\' domain postfix',
                        null,
                        function ( $domain ) use ( $defaultDomain ) {
                            $domain = trim( $domain, '.' );

                            if ( $domain == $defaultDomain )
                            {
                                throw new LogicException( sprintf(
                                    'Cannot use the default domain "%s"',
                                    $defaultDomain
                                ) );
                            }

                            $matches = array();
                            $pattern = '/([a-z0-9\-]+\.)+[a-z]{2,}/i';

                            if ( ! preg_match( $pattern, $domain, $matches ) )
                            {
                                throw new LogicException( sprintf(
                                    '"%s" does not match "%s"',
                                    $domain,
                                    $pattern
                                ) );
                            }

                            return '.' . $matches[0];
                        },
                        5
                    )
                ),
            ),
        ) );
    }

}
