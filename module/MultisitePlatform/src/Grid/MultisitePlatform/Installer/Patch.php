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
        $data   = $this->getPatchData();
        $domain = $data->get(
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
        $extra = $this->getInstaller()
                      ->getExtra();

        if ( isset( $extra['application-config'] ) )
        {
            $appConfigFile = $extra['application-config'];

            if ( substr( $appConfigFile, 0, 2 ) == './' )
            {
                $appConfigFile = substr( $appConfigFile, 2 );
            }
        }
        else
        {
            $appConfigFile = 'config/application.php';
        }

        if ( isset( $extra['db-config'] ) )
        {
            $dbConfigFile = $extra['db-config'];
        }
        else
        {
            $dbConfigFile = 'config/autoload/db.local.php';

            if ( substr( $dbConfigFile, 0, 2 ) == './' )
            {
                $dbConfigFile = substr( $dbConfigFile, 2 );
            }
        }

        if ( isset( $extra['multisite-config'] ) )
        {
            $multiConfigFile = $extra['multisite-config'];
        }
        else
        {
            $multiConfigFile = dirname( $dbConfigFile ) . '/multisite.local.php';
        }

        $appConfig   = file_exists( $appConfigFile )
            ? include $appConfigFile
            : array(
                'modules'   => array(
                    'Grid\Core',
                    'Grid\Mail',
                    'Grid\Customize',
                    'Grid\Paragraph',
                    'Grid\Tag',
                    'Grid\Image',
                    'Grid\User',
                    'Grid\Menu',
                ),
                'module_listener_options'   => array(
                    'config_glob_paths'     => array(
                        'config/autoload/{,*.}{global,local}.php',
                    ),
                    'module_paths'  => array(
                        'module',
                        'vendor',
                    ),
                ),
                'service_manager'   => array(
                    'factories'     => array(
                        'DbAdapter'     => 'Zork\Db\Adapter\AdapterServiceFactory',
                        'ModuleManager' => 'Zork\Mvc\Service\ModuleManagerFactory',
                    ),
                    'invokables'    => array(
                        'SiteConfiguration' => 'Zork\Db\SiteConfiguration\Singlesite',
                    ),
                    'aliases'       => array(
                        'Zork\Db\SiteInfo'                      => 'SiteInfo',
                        'Zend\Db\Adapter\Adapter'               => 'DbAdapter',
                        'Zork\Db\SiteConfigurationInterface'    => 'SiteConfiguration',
                    ),
                ),
            );

        $dbConfig = file_exists( $dbConfigFile )
            ? include $dbConfigFile
            : array(
                'db' => $this->getPatcher()
                             ->getConfig()
            );

        $multiConfig = file_exists( $multiConfigFile )
            ? include $multiConfigFile
            : array(
                'modules' => array(
                    'Grid\MultisiteCentral' => array(
                        'schemaPrefix'  => 'site_',
                        'schemaPostfix' => '',
                        'domainPostfix' => '.cmsguyz.com',
                    ),
                ),
            );

        if ( isset( $appConfig['db'] ) )
        {
            if ( empty( $dbConfig['db'] ) )
            {
                $dbConfig['db'] = $appConfig['db'];
            }

            unset( $appConfig['db'] );
        }

        if ( ! in_array( 'Grid\MultisitePlatform', $appConfig['modules'] ) )
        {
            $appConfig['modules'][] = 'Grid\MultisitePlatform';
        }

        $appConfig['service_manager']['invokables']['SiteConfiguration'] = 'Zork\Db\SiteConfiguration\Multisite';

        $appConfigDir = dirname( $appConfigFile ) . '/';

        if ( substr( $dbConfigFile, 0, strlen( $appConfigDir ) ) == $appConfigDir )
        {
            $file    = substr( $dbConfigFile, strlen( $appConfigDir ) - 1 );
            $require = "__DIR__ . '$file'";
        }
        else
        {
            $require = "'$dbConfigFile'";
        }

        @file_put_contents(
            $appConfigFile,
            sprintf(
                '%s%s%sreturn ( require %s ) + %s;%s',
                '<',
                '?php',
                PHP_EOL,
                $require,
                var_export( $appConfig, true ),
                PHP_EOL
            )
        );

        $dbConfig['db']['defaultDomain'] = $defaultDomain;

        @file_put_contents(
            $dbConfigFile,
            sprintf(
                '%s%s%sreturn %s;%s',
                '<',
                '?php',
                PHP_EOL,
                var_export( $dbConfig, true ),
                PHP_EOL
            )
        );

        $data   = $this->getPatchData();
        $domainPostfix = $data->get(
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
        );

        $multiConfig['modules']['Grid\MultisiteCentral']['domainPostfix'] = $domainPostfix;

        @file_put_contents(
            $multiConfigFile,
            sprintf(
                '%s%s%sreturn %s;%s',
                '<',
                '?php',
                PHP_EOL,
                var_export( $multiConfig, true ),
                PHP_EOL
            )
        );
    }

}
